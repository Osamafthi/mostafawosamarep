<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class OptimizeProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 10;

    public function __construct(
        public string $disk,
        public string $path,
        public int $maxWidth = 1600,
        public int $maxHeight = 1600,
    ) {
    }

    public function handle(): void
    {
        $storage = Storage::disk($this->disk);

        if (! $storage->exists($this->path)) {
            Log::warning("OptimizeProductImage: missing file {$this->disk}:{$this->path}");
            return;
        }

        try {
            $contents = $storage->get($this->path);

            $image = Image::read($contents)
                ->scaleDown(width: $this->maxWidth, height: $this->maxHeight);

            // Keep the original extension / encoder so URLs don't change.
            $encoded = (string) $image->encodeByPath($this->path, quality: 82);

            $storage->put($this->path, $encoded);
        } catch (\Throwable $e) {
            Log::warning('OptimizeProductImage failed: '.$e->getMessage(), [
                'disk' => $this->disk,
                'path' => $this->path,
            ]);
        }
    }
}
