<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RestoreSalaryAdjustmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kasbon:restore {--force : Force overwrite existing records with same ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore missing salary adjustments (kasbon & bonus) from public/backup_mstore_oktober2026.sql';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupFile = public_path('backup_mstore_oktober2026.sql');
        if (! file_exists($backupFile)) {
            $this->error("Backup file not found at: {$backupFile}");
            return Command::FAILURE;
        }

        $this->info("Reading backup file: {$backupFile}...");
        $sql = file_get_contents($backupFile);

        if (! preg_match('/INSERT INTO `salary_adjustments` VALUES (.*?);/s', $sql, $matches)) {
            $this->error('No salary_adjustments insert statements found in backup file.');
            return Command::FAILURE;
        }

        $raw = $matches[1];
        preg_match_all('/\((.*?)\)(?:,|$)/s', $raw, $tuples);

        $totalTuples = count($tuples[1]);
        $this->info("Found {$totalTuples} salary_adjustments records in backup. Restoring...");

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $inserted = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($tuples[1] as $tuple) {
                $fields = str_getcsv($tuple, ',', "'");
                if (count($fields) < 9) {
                    continue;
                }

                $id = (int) trim($fields[0]);
                $userId = (int) trim($fields[1]);
                $type = trim($fields[2]);
                $amount = (float) trim($fields[3]);
                $date = trim($fields[4]);
                $desc = trim($fields[5]) === 'NULL' ? null : trim($fields[5]);
                $status = trim($fields[6]);
                $createdAt = trim($fields[7]) === 'NULL' ? now() : trim($fields[7]);
                $updatedAt = trim($fields[8]) === 'NULL' ? now() : trim($fields[8]);

                $exists = DB::table('salary_adjustments')->where('id', $id)->exists();
                if ($exists && ! $this->option('force')) {
                    $skipped++;
                    continue;
                }

                if ($exists && $this->option('force')) {
                    DB::table('salary_adjustments')->where('id', $id)->update([
                        'user_id' => $userId,
                        'type' => $type,
                        'amount' => $amount,
                        'date' => $date,
                        'description' => $desc,
                        'status' => $status,
                        'updated_at' => $updatedAt,
                    ]);
                    $inserted++;
                    continue;
                }

                DB::table('salary_adjustments')->insert([
                    'id' => $id,
                    'user_id' => $userId,
                    'type' => $type,
                    'amount' => $amount,
                    'date' => $date,
                    'description' => $desc,
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
                $inserted++;
            }

            DB::commit();
            $this->info("Restoration completed successfully!");
            $this->line("   - Inserted / Restored: <info>{$inserted}</info>");
            $this->line("   - Existing / Skipped: <comment>{$skipped}</comment>");
            $this->line("   - Total in Database: <info>" . DB::table('salary_adjustments')->count() . "</info>");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Failed to restore: " . $e->getMessage());
            Log::error('RestoreSalaryAdjustmentsCommand error: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }
    }
}
