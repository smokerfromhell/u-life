<?php

namespace App\Console\Commands;

use App\Models\DecisionLog;
use App\Models\SharedDecisionLog;
use App\Models\LifeStatsSnapshot;
use App\Models\User;
use App\Support\Privacy;
use Illuminate\Console\Command;

class CleanupDecisionLogs extends Command
{
    protected $signature = 'cleanup:decision-logs';

    protected $description = 'Delete decision logs from users who have not given share consent';

    public function handle()
    {
        $this->info('Finding users without share consent...');

        // Get users without consent (share_consent is null or false)
        $usersWithoutConsent = User::where(function ($q) {
            $q->where('share_consent', false)
              ->orWhereNull('share_consent')
              ->orWhere('share_consent', '');
        })->get(['id', 'name']);

        $this->info('Users without consent:');
        foreach ($usersWithoutConsent as $user) {
            $anonId = Privacy::anonymize('user', $user->id);
            $this->line("  - ID: {$user->id}, Name: {$user->name}, AnonID: {$anonId}");
        }

        // Generate anon_user_ids for users without consent
        $anonUserIds = $usersWithoutConsent->map(function ($user) {
            return Privacy::anonymize('user', $user->id);
        })->toArray();

        $userIds = $usersWithoutConsent->pluck('id')->toArray();

        $this->info('Anonymized user IDs without consent: ' . implode(', ', $anonUserIds));
        $this->info('User IDs without consent: ' . implode(', ', $userIds));

        // Delete DecisionLogs by user_id
        $deletedDecisionLogs = DecisionLog::whereIn('user_id', $userIds)->delete();
        $this->info("Deleted {$deletedDecisionLogs} DecisionLogs");

        // Delete SharedDecisionLogs by anon_user_id
        $deletedSharedLogs = SharedDecisionLog::whereIn('anon_user_id', $anonUserIds)->delete();
        $this->info("Deleted {$deletedSharedLogs} SharedDecisionLogs");

        // Delete LifeStatsSnapshots by anon_user_id
        $deletedSnapshots = LifeStatsSnapshot::whereIn('anon_user_id', $anonUserIds)->delete();
        $this->info("Deleted {$deletedSnapshots} LifeStatsSnapshots");

        // Show remaining counts
        $remainingDecisionLogs = DecisionLog::count();
        $remainingSharedLogs = SharedDecisionLog::count();
        
        $this->info("Remaining DecisionLogs: {$remainingDecisionLogs}");
        $this->info("Remaining SharedDecisionLogs: {$remainingSharedLogs}");

        $this->info('Cleanup complete!');
        
        return 0;
    }
}
