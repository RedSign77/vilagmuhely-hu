<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use App\Models\ExpeditionEnrollment;
use App\Models\UserExpeditionEffect;
use App\Services\ExpeditionRewardService;
use Illuminate\Console\Command;

class ProcessExpeditionCompletionsCommand extends Command
{
    protected $signature = 'expedition:process-completions';

    protected $description = 'Process expedition completions, grant rewards, and deactivate expired effects';

    public function handle()
    {
        $this->info('Processing expedition completions...');

        // Check enrollments for completion
        $completedCount = $this->processCompletions();

        // Deactivate expired effects
        $expiredCount = $this->deactivateExpiredEffects();

        // Complete ended expeditions
        $endedCount = $this->completeEndedExpeditions();

        $this->info("Completed: {$completedCount} enrollments, {$expiredCount} effects expired, {$endedCount} expeditions ended");

        return Command::SUCCESS;
    }

    protected function processCompletions(): int
    {
        $pendingCompletions = ExpeditionEnrollment::whereNull('completed_at')
            ->whereHas('expedition', fn ($q) => $q->active())
            ->with(['expedition', 'qualifyingPosts'])
            ->get()
            ->filter(fn ($enrollment) => $enrollment->checkCompletion());

        $count = 0;
        foreach ($pendingCompletions as $enrollment) {
            $enrollment->update(['completed_at' => now()]);
            app(ExpeditionRewardService::class)->grantRewards($enrollment);
            $count++;
        }

        return $count;
    }

    protected function deactivateExpiredEffects(): int
    {
        return UserExpeditionEffect::where('is_active', true)
            ->where('expires_at', '<=', now())
            ->update(['is_active' => false]);
    }

    protected function completeEndedExpeditions(): int
    {
        return Expedition::where('status', 'active')
            ->where('ends_at', '<', now())
            ->update(['status' => 'completed']);
    }
}
