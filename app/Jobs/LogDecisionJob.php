<?php

namespace App\Jobs;

use App\Services\DecisionLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogDecisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(DecisionLogService $service): void
    {
        $service->logDecision(
            $this->data['character'],
            $this->data['eventId'],
            $this->data['eventType'],
            $this->data['choices'],
            $this->data['outcome'] ?? null,
            $this->data['mbti'] ?? null
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('LogDecisionJob failed', [
            'data' => $this->data,
            'error' => $exception->getMessage(),
        ]);
    }
}
