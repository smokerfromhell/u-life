<?php

namespace App\Console\Commands;

use App\Models\SharedDecisionLog;
use Illuminate\Console\Command;

class FixSharedDecisionLogsMbti extends Command
{
    protected $signature = 'shared-decision-logs:fix-mbti';
    protected $description = 'Populate mbti field for existing shared decision logs from encrypted data';

    public function handle()
    {
        // Get logs that don't have mbti set but have data with mbti
        $logs = SharedDecisionLog::whereNull('mbti')
            ->whereNotNull('data')
            ->get();
        
        $updated = 0;
        foreach ($logs as $log) {
            $data = $log->data ?? [];
            $mbti = $data['mbti'] ?? null;
            
            if ($mbti) {
                $log->update(['mbti' => $mbti]);
                $updated++;
            }
        }

        $this->info("Updated {$updated} shared decision logs with mbti.");
        
        // Also ensure characters have anon_character_id
        $this->call('character:fix-anon-id');
        
        return 0;
    }
}
