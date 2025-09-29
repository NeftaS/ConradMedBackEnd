<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeletePublicFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Ruta relativa dentro de disk('public'), por ejemplo: "ordenes/archivo.pdf"
    public string $relativePath;

    public function __construct(string $relativePath)
    {
        $this->relativePath = $relativePath;
    }

    public function handle(): void
    {
        try {
            $deleted = Storage::disk('public')->delete($this->relativePath);
            Log::info('DeletePublicFile job executed', [
                'relative' => $this->relativePath,
                'deleted'  => $deleted,
            ]);
        } catch (\Throwable $e) {
            Log::error('DeletePublicFile job failed', [
                'relative' => $this->relativePath,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
