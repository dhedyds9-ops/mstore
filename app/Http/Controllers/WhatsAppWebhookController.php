<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WhatsAppMenu;
use App\Models\WhatsAppLog;
use App\Models\WhatsAppConversation;
use App\Services\AiService;
use App\Services\WhatsAppService;
use App\Services\WhatsApp\WhatsAppAutoReplyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WhatsAppWebhookController extends Controller
{
    protected $whatsappService;
    protected $aiService;
    protected $autoReplyService;

    public function __construct(WhatsAppService $whatsappService, AiService $aiService, WhatsAppAutoReplyService $autoReplyService)
    {
        $this->whatsappService = $whatsappService;
        $this->aiService = $aiService;
        $this->autoReplyService = $autoReplyService;
    }

    /**
     * Handle incoming WhatsApp messages from webhook
     */
    public function handle(Request $request)
    {
        // 1. Handle verification request (GET)
        if ($request->isMethod('GET')) {
            $verifyToken = config('services.whatsapp.verify_token');
            $receivedToken = $request->input('hub.verify_token') ?? $request->input('verify_token');
            $challenge = $request->input('hub.challenge') ?? $request->input('challenge');

            if (is_string($verifyToken) && $verifyToken !== '') {
                if (! is_string($receivedToken) || $receivedToken === '' || ! hash_equals($verifyToken, $receivedToken)) {
                    return response('Invalid verify token', 403);
                }
            }
            return response((string) ($challenge ?? 'OK'), 200);
        }

        // 2. Validate webhook request (signature/token)
        if (! $this->isValidWebhookRequest($request)) {
            Log::warning('Unauthorized WhatsApp webhook request');
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payload = null;
        $phone = null;
        try {
            $startTime = microtime(true);
            $payload = $request->all();
            Log::info('WHATSAPP WEBHOOK RECEIVED', ['payload' => $payload]);
            Log::info('WHATSAPP RAW CONTENT', ['content' => $request->getContent()]);

            // 3. Extract message from provider payload
            $messageData = $this->extractMessage($payload);
            Log::info('WHATSAPP EXTRACTED MESSAGE DATA', ['messageData' => $messageData]);
            
            if (! $messageData) {
                Log::info('WHATSAPP NO MESSAGE EXTRACTED');
                return response()->json(['status' => 'no_message']);
            }

            $phone = $messageData['phone'];
            $message = $messageData['message'];
            $isGroup = $messageData['is_group'];
            $providerMessageId = $messageData['provider_message_id'] ?? null;
            $fromMe = $messageData['from_me'] ?? false;

            // 4. Anti-loop: Skip messages from ourselves
            if ($fromMe) {
                Log::info('WHATSAPP IGNORING OWN MESSAGE (fromMe=true)', ['phone' => $phone, 'message' => $message]);
                // Save the outgoing message (from CS/Admin)
                WhatsAppLog::logMessage('outgoing', $phone, $message, 'delivered', $payload, null, [
                    'sender_type' => 'admin',
                    'message_type' => 'text',
                    'provider_message_id' => $providerMessageId,
                ]);
                return response()->json(['status' => 'ignored_own_message']);
            }

            // 5. Anti-duplicate: Cek provider message ID
            if ($providerMessageId) {
                $existingDuplicate = WhatsAppLog::where('provider_message_id', $providerMessageId)->first();
                if ($existingDuplicate) {
                    $existingDuplicate->increment('duplicate_count');
                    $existingDuplicate->update(['duplicate_detected_at' => now()]);
                    Log::info('WHATSAPP DUPLICATE MESSAGE', [
                        'phone' => $phone, 
                        'message_id' => $providerMessageId,
                        'duplicate_count' => $existingDuplicate->duplicate_count
                    ]);
                    return response()->json(['status' => 'duplicate_ignored']);
                }
            }

            // 6. Dapatkan atau buat conversation
            $conversation = WhatsAppConversation::getOrCreate($phone);
            $conversationId = $conversation ? $conversation->conversation_id : null;
            if ($conversation) {
                $conversation->incrementUnread();
            }

            // 7. Intent detection & confidence scoring
            $intentData = $this->detectIntent($message);
            $detectedIntent = $intentData['intent'];
            $aiConfidence = $intentData['confidence'];

            // 8. Log incoming message dengan metadata lengkap
            $incomingLog = WhatsAppLog::logMessage('incoming', $phone, $message, 'delivered', $payload, null, [
                'conversation_id' => $conversationId,
                'sender_type' => 'customer',
                'message_type' => 'text',
                'provider_message_id' => $providerMessageId,
                'detected_intent' => $detectedIntent,
                'ai_confidence' => $aiConfidence,
            ]);
            Log::info('WHATSAPP MESSAGE SAVED', ['log_id' => $incomingLog->id]);

            // 9. Human takeover validation: JANGAN balas jika CS sedang menangani!
            if (! $conversation || ! $conversation->shouldBotReply()) {
                Log::info('WHATSAPP CONVERSATION HANDLED BY CS OR NOT FOUND', [
                    'phone' => $phone, 
                    'status' => $conversation ? $conversation->status : 'no_conversation'
                ]);
                return response()->json(['status' => 'cs_handling']);
            }

            // 10. Low confidence: Assign ke CS waiting list
            $minConfidence = (float) Setting::getValue('whatsapp_min_confidence', 0.70);
            if ($aiConfidence < $minConfidence) {
                $conversation->update([
                    'status' => 'waiting_cs', 
                    'takeover_reason' => 'Low AI confidence: ' . $aiConfidence
                ]);
                Log::info('WHATSAPP LOW CONFIDENCE, ASSIGNED TO CS', [
                    'phone' => $phone, 
                    'confidence' => $aiConfidence
                ]);
                return response()->json(['status' => 'waiting_cs']);
            }

            // 11. Check delay reply feature
            $delayReplyEnabled = Setting::getValue('whatsapp_delay_reply_enabled', '0') == '1';
            $delayMinutes = (int) Setting::getValue('whatsapp_delay_reply_minutes', 5);
            
            if ($delayReplyEnabled && $delayMinutes > 0) {
                // Dispatch delayed reply job
                $incomingTimestamp = $incomingLog->created_at->timestamp;
                \App\Jobs\ProcessDelayedWhatsAppReply::dispatch(
                    $phone,
                    $message,
                    $isGroup,
                    $incomingTimestamp,
                    $conversationId
                )->delay(now()->addMinutes($delayMinutes));

                Log::info('WHATSAPP DELAYED REPLY QUEUED', [
                    'phone' => $phone,
                    'delay' => "{$delayMinutes} minutes",
                    'conversation_id' => $conversationId,
                ]);
                return response()->json(['status' => 'queued']);
            }

            // 12. No delay, process & reply immediately
            Log::info('WHATSAPP PROCESSING INCOMING MESSAGE', ['phone' => $phone, 'message' => $message]);
            $response = $this->processIncomingMessage($phone, $message, $isGroup, $conversation);
            Log::info('WHATSAPP AI RESPONSE GENERATED', ['response' => $response]);
            
            if ($response) {
                Log::info('WHATSAPP SENDING TO GATEWAY', ['phone' => $phone, 'response' => $response]);
                $sendResult = $this->whatsappService->sendMessage($phone, $response);
                Log::info('WHATSAPP GATEWAY RESPONSE', ['sendResult' => $sendResult]);
                $processingTime = round((microtime(true) - $startTime) * 1000);
                
                WhatsAppLog::logMessage('outgoing', $phone, $response, $sendResult['success'] ? 'sent' : 'failed', $sendResult, null, [
                    'conversation_id' => $conversationId,
                    'sender_type' => 'bot',
                    'message_type' => 'text',
                    'processing_time_ms' => $processingTime,
                ]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            Log::error('WHATSAPP WEBHOOK ERROR', [
                'message' => $e->getMessage(),
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            if (isset($phone)) {
                try {
                    WhatsAppLog::logMessage('incoming', $phone, $request->getContent() ?? 'error', 'failed', $payload ?? [], $e->getMessage());
                } catch (\Throwable $logError) {
                    Log::error('WHATSAPP FAILED TO LOG ERROR', ['error' => $logError->getMessage()]);
                }
            }
            
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Detect intent from message (simulasi ISP-specific)
     */
    private function detectIntent(string $message): array
    {
        $message = Str::lower(trim($message));
        
        $patterns = [
            'greeting' => ['halo', 'hai', 'hi', 'hallo', 'selamat pagi', 'selamat siang', 'selamat malam', 'assalamualaikum'],
            'new_installation' => ['pasang wifi', 'daftar internet', 'internet rumah', 'pasang baru', 'pendaftaran'],
            'complaint' => ['wifi lemot', 'lemot banget', 'lambat', 'tidak bisa konek', 'putus', 'error', 'gangguan'],
            'technical_issue' => ['modem mati', 'merah', 'los merah', 'tidak ada sinyal', 'sinyal hilang'],
            'billing' => ['tagihan', 'bayar', 'invoice', 'harga', 'biaya', 'pembayaran'],
            'closing' => ['sudah bisa', 'normal', 'aman', 'beres', 'selesai', 'terima kasih', 'makasih', 'thanks'],
            'support' => ['bantuan', 'help', 'tolong', 'cs', 'petugas'],
        ];

        $highestConfidence = 0;
        $detectedIntent = 'unknown';

        foreach ($patterns as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($message, $keyword)) {
                    // Calculate confidence based on match quality
                    similar_text($message, $keyword, $percent);
                    $confidence = $percent / 100;
                    
                    // Boost confidence if exact match
                    if ($message === $keyword) {
                        $confidence = 1.0;
                    } elseif (Str::startsWith($message, $keyword)) {
                        $confidence = min(1.0, $confidence + 0.2);
                    }
                    
                    if ($confidence > $highestConfidence) {
                        $highestConfidence = $confidence;
                        $detectedIntent = $intent;
                    }
                }
            }
        }

        // Default for unknown
        if ($detectedIntent === 'unknown') {
            $highestConfidence = 0.3;
        }

        return [
            'intent' => $detectedIntent,
            'confidence' => round($highestConfidence, 2),
        ];
    }

    /**
     * Cek apakah pesan ini berasal dari kita sendiri
     */
    private function isMessageFromUs(string $phone, string $message): bool
    {
        // Cek apakah ada pesan outgoing kita yang baru saja dikirim ke nomor ini dengan content yang sama
        // Atau cek apakah phone nomor kita sendiri
        $recentOutgoing = WhatsAppLog::where('phone_number', $phone)
            ->where('type', 'outgoing')
            ->where('message', $message)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();
        
        return $recentOutgoing;
    }

    /**
     * Dapatkan atau buat conversation ID baru
     */
    private function getOrCreateConversationId(string $phone): string
    {
        // Cek apakah ada percakapan aktif dalam 24 jam terakhir
        $recentLog = WhatsAppLog::where('phone_number', $phone)
            ->whereNotNull('conversation_id')
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->first();
        
        if ($recentLog && $recentLog->conversation_id) {
            return $recentLog->conversation_id;
        }
        
        // Buat conversation ID baru
        return Str::uuid()->toString();
    }

    private function isValidWebhookRequest(Request $request): bool
    {
        $secret = config('services.whatsapp.secret');
        if (! is_string($secret) || trim($secret) === '') {
            return true;
        }

        $secret = trim($secret);

        $signature256 = $request->header('X-Hub-Signature-256');
        if (is_string($signature256) && $signature256 !== '') {
            $rawBody = (string) $request->getContent();
            $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
            return hash_equals($expected, $signature256);
        }

        $authorization = $request->header('Authorization');
        if (is_string($authorization) && $authorization !== '') {
            if (str_starts_with($authorization, 'Bearer ')) {
                $token = substr($authorization, strlen('Bearer '));
                return is_string($token) && $token !== '' && hash_equals($secret, $token);
            }
        }

        $headerSecret = $request->header('X-Webhook-Secret') ?? $request->header('X-Webhook-Token');
        if (is_string($headerSecret) && $headerSecret !== '') {
            return hash_equals($secret, $headerSecret);
        }

        $querySecret = $request->query('secret');
        if (is_string($querySecret) && $querySecret !== '') {
            return hash_equals($secret, $querySecret);
        }

        return false;
    }

    /**
     * Extract message details from provider payload
     */
    private function extractMessage(array $payload): ?array
    {
        // Helper to extract fromMe from message object
        $extractFromMe = function ($msg) {
            return isset($msg['fromMe']) ? (bool)$msg['fromMe'] :
                   (isset($msg['key']['fromMe']) ? (bool)$msg['key']['fromMe'] :
                   (isset($msg['is_outgoing']) ? (bool)$msg['is_outgoing'] :
                   (isset($msg['isOutgoing']) ? (bool)$msg['isOutgoing'] :
                   (isset($msg['is_me']) ? (bool)$msg['is_me'] :
                   false))));
        };

        // Fonnte format
        if (isset($payload['data']['messages']) && is_array($payload['data']['messages'])) {
            foreach ($payload['data']['messages'] as $msg) {
                if (isset($msg['message'])) {
                    return [
                        'phone' => $msg['phone'] ?? $msg['sender'] ?? null,
                        'message' => $msg['message'],
                        'is_group' => isset($msg['is_group']) ? (bool) $msg['is_group'] : false,
                        'provider_message_id' => $msg['id'] ?? $msg['message_id'] ?? null,
                        'from_me' => $extractFromMe($msg),
                    ];
                }
            }
        }

        // Wablas format - various possibilities
        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach ($payload['data'] as $msg) {
                if (isset($msg['message'])) {
                    // Extract phone number without @c.us suffix
                    $phone = $msg['phone'] ?? $msg['sender'] ?? null;
                    if ($phone && str_contains($phone, '@')) {
                        $phone = explode('@', $phone)[0];
                    }
                    return [
                        'phone' => $phone,
                        'message' => $msg['message'],
                        'is_group' => isset($msg['is_group']) ? (bool) $msg['is_group'] : (isset($msg['group_id']) && !empty($msg['group_id'])),
                        'provider_message_id' => $msg['id'] ?? $msg['message_id'] ?? null,
                        'from_me' => $extractFromMe($msg),
                    ];
                }
            }
        }

        // Generic format 1
        if (isset($payload['message']) && isset($payload['phone'])) {
            $phone = $payload['phone'];
            if ($phone && str_contains($phone, '@')) {
                $phone = explode('@', $phone)[0];
            }
            return [
                'phone' => $phone,
                'message' => $payload['message'],
                'is_group' => isset($payload['is_group']) ? (bool) $payload['is_group'] : (isset($payload['group_id']) && !empty($payload['group_id'])),
                'provider_message_id' => $payload['id'] ?? $payload['message_id'] ?? null,
                'from_me' => $extractFromMe($payload),
            ];
        }
        
        // Generic format 2 - maybe payload is single message object
        if (isset($payload['message']) || isset($payload['text'])) {
            $phone = $payload['phone'] ?? $payload['sender'] ?? $payload['from'] ?? null;
            if ($phone && str_contains($phone, '@')) {
                $phone = explode('@', $phone)[0];
            }
            return [
                'phone' => $phone,
                'message' => $payload['message'] ?? $payload['text'] ?? '',
                'is_group' => isset($payload['is_group']) ? (bool) $payload['is_group'] : (isset($payload['group_id']) && !empty($payload['group_id'])),
                'provider_message_id' => $payload['id'] ?? $payload['message_id'] ?? null,
                'from_me' => $extractFromMe($payload),
            ];
        }

        return null;
    }

    /**
     * Process incoming message and find appropriate reply
     */
    private function processIncomingMessage(string $phone, string $message, bool $isGroup, WhatsAppConversation $conversation = null): ?string
    {
        $message = trim(Str::lower($message));

        // Check if autoreply is enabled
        $autoreplyEnabled = Setting::getValue('whatsapp_autoreply_enabled', '1') == '1';
        if (! $autoreplyEnabled) {
            return null;
        }

        // First, try WhatsAppAutoReplyService for default menus (halo, bantuan)
        $autoReply = $this->autoReplyService->getReply($message);
        if ($autoReply) {
            return $autoReply;
        }

        // Next, check WhatsAppMenu (from builder) for matching keywords
        $menus = WhatsAppMenu::active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($menus as $menu) {
            $keyword = Str::lower($menu->keyword);
            
            // Exact match
            if ($message === $keyword) {
                $menu->incrementHitCount();
                return $this->sendMenuReplyText($menu);
            }

            // Fuzzy match (if enabled)
            if ($menu->enable_fuzzy_match) {
                similar_text($message, $keyword, $percent);
                if ($percent >= 70 || Str::contains($message, $keyword)) {
                    $menu->incrementHitCount();
                    return $this->sendMenuReplyText($menu);
                }
            }
        }

        // Try AI Assistant if no keyword matches
        try {
            // Get context from previous messages (last 20 messages)
            $context = $this->getConversationContext($phone);
            
            $aiResponse = $this->aiService->processChat($message . "\n\nContext:\n" . $context);
            if ($aiResponse && !str_contains($aiResponse, 'Saya adalah Asisten AI')) {
                return $this->renderAiResponse($aiResponse);
            }
        } catch (\Exception $e) {
            Log::warning('AI Service Error in WhatsApp: ' . $e->getMessage());
        }

        // If no menu matches and AI didn't give specific answer, use custom reply from setting
        $customReply = Setting::getValue('whatsapp_unknown_keyword_reply');
        if ($customReply) {
            return $customReply;
        }

        // Default fallback
        return 'Maaf, saya tidak memahami pesan Anda. Silakan ketik "bantuan" untuk melihat daftar menu yang tersedia.';
    }

    /**
     * Get conversation context from last 20 messages
     */
    private function getConversationContext(string $phone): string
    {
        $messages = WhatsAppLog::where('phone_number', $phone)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->map(function ($log) {
                $sender = $log->type === 'incoming' ? 'Customer' : 'Bot';
                return "{$sender}: {$log->message}";
            })
            ->implode("\n");

        return $messages ?: 'No previous context';
    }

    /**
     * Send menu reply and handle media
     */
    private function sendMenuReply(string $phone, WhatsAppMenu $menu): ?string
    {
        $replyText = $this->renderMenuReplyText($menu);
        
        if ($menu->file_path && in_array($menu->type, ['image', 'document'])) {
            $mediaUrl = asset('storage/' . $menu->file_path);
            $mediaType = $menu->type === 'document' ? 'document' : 'image';
            
            $this->whatsappService->sendMessageWithMediaUrl(
                $phone, 
                $replyText, 
                $mediaUrl, 
                $mediaType
            );
            
            // Return null because we already sent the message with media
            return null;
        }

        return $replyText;
    }

    /**
     * Render menu reply text
     */
    private function sendMenuReplyText(WhatsAppMenu $menu): string
    {
        $reply = $menu->response_text;
        
        $reply = str_replace('{nama_user}', 'Teman', $reply);
        $reply = str_replace('{jam_sekarang}', now()->format('H:i'), $reply);
        $reply = str_replace('{tanggal_sekarang}', now()->format('d M Y'), $reply);

        return $reply;
    }

    /**
     * Render AI response to WhatsApp-friendly format
     */
    private function renderAiResponse($aiResponse): string
    {
        if (is_string($aiResponse)) {
            // Strip HTML tags for WhatsApp
            return strip_tags($aiResponse, '<b><i><u><br><ol><ul><li>');
        }

        if (is_array($aiResponse)) {
            $output = '';
            
            if (isset($aiResponse['title'])) {
                $output .= "*{$aiResponse['title']}*\n";
            }

            if (isset($aiResponse['items']) && is_array($aiResponse['items'])) {
                foreach ($aiResponse['items'] as $index => $item) {
                    // Strip HTML from items
                    $cleanItem = strip_tags($item, '<b><i><u>');
                    $output .= ($index + 1) . ". {$cleanItem}\n";
                }
            }

            if (isset($aiResponse['footer'])) {
                $output .= "\n" . strip_tags($aiResponse['footer']);
            }

            return $output;
        }

        return (string) $aiResponse;
    }

    /**
     * Render menu reply based on type
     */
    private function renderMenuReply(WhatsAppMenu $menu): string
    {
        $reply = $menu->response_text;
        
        // Replace placeholders
        $reply = str_replace('{nama_user}', 'Teman', $reply);
        $reply = str_replace('{jam_sekarang}', now()->format('H:i'), $reply);
        $reply = str_replace('{tanggal_sekarang}', now()->format('d M Y'), $reply);

        return $reply;
    }
}
