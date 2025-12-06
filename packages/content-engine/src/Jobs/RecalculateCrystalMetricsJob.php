<?php

namespace Webtechsolutions\ContentEngine\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webtechsolutions\ContentEngine\Models\CrystalActivityQueue;
use Webtechsolutions\ContentEngine\Services\CrystalCalculatorService;

class RecalculateCrystalMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CrystalCalculatorService $calculator): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        // Recalculate all metrics and generate geometry
        $calculator->recalculateMetrics($user);

        // Mark all queued activities as processed
        CrystalActivityQueue::where('user_id', $this->userId)
            ->whereNull('processed_at')
            ->update(['processed_at' => now()]);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure
        \Log::error('Crystal metrics recalculation failed', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
