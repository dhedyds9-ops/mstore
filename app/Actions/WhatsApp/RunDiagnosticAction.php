<?php

namespace App\Actions\WhatsApp;

use App\Models\Customer;
use App\Models\Ticket;
use App\Services\TechnicianAssignmentService;
use Illuminate\Support\Facades\Log;

class RunDiagnosticAction
{
    protected $assignmentService;

    public function __construct(TechnicianAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function createTicketFromDiagnostic($diagnostic, Customer $customer, string $description = ''): ?Ticket
    {
        if (!$diagnostic->ticket_needed) {
            return null;
        }

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'ticket_number' => 'TCK-' . date('Ymd') . '-' . strtoupper(str()->random(4)),
            'title' => 'Laporan Gangguan - ' . $diagnostic->summary,
            'description' => $description ?: $diagnostic->summary,
            'status' => 'open',
            'priority' => $diagnostic->priority,
            'category' => $this->mapCategory($diagnostic),
            'diagnostic_id' => $diagnostic->id,
        ]);

        $diagnostic->update(['ticket_id' => $ticket->id]);

        // Auto assign technician
        try {
            $this->assignmentService->autoAssign($ticket);
        } catch (\Exception $e) {
            Log::error('Failed to auto assign technician for ticket #'.$ticket->id.': '.$e->getMessage(), [
                'ticket_id'   => $ticket->id,
                'customer_id' => $customer->id,
                'exception'   => $e->getMessage(),
            ]);

            // Notifikasi administrator bahwa ticket tidak ter-assign
            $this->notifyAdminsAboutUnassignedTicket($ticket, $e->getMessage());
        }

        return $ticket;
    }

    /**
     * Kirim notifikasi ke administrator bahwa ticket gagal di-assign ke technician.
     */
    protected function notifyAdminsAboutUnassignedTicket(Ticket $ticket, string $reason): void
    {
        try {
            // Ambil semua user dengan role admin atau direktur
            $admins = \App\Models\User::whereHas('role', function ($q) {
                $q->whereIn('name', ['admin', 'direktur']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\TicketAssignedNotification(
                    $ticket,
                    'Ticket #'.$ticket->id.' gagal di-auto-assign: '.$reason.'. Harap assign manual.'
                ));
            }
        } catch (\Exception $notifyEx) {
            Log::error('Gagal mengirim notifikasi unassigned ticket ke admin: '.$notifyEx->getMessage());
        }
    }

    protected function mapCategory($diagnostic): string
    {
        $map = [
            'onu_los'    => 'ONU',
            'onu_offline' => 'ONU',
            'pppoe_issue' => 'PPPoE',
            'wifi'       => 'Hotspot',
        ];

        $checks = $diagnostic->checks;
        if (isset($checks['onu']['issue_type'])) {
            return $map[$checks['onu']['issue_type']] ?? 'Other';
        }

        return 'Other';
    }
}
