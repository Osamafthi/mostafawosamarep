<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Upload\UploadImageRequest;
use App\Jobs\OptimizeProductImage;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(UploadImageRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $disk = 'public';
        $path = $file->store('products', $disk);

        // Queue an image-optimization job (resize + re-encode) so the
        // request returns immediately and the file is tidied up in the background.
        OptimizeProductImage::dispatch($disk, $path);

        return ApiResponse::created([
            'url' => Storage::disk($disk)->url($path),
            'path' => $path,
            'disk' => $disk,
            'filename' => basename($path),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);
    }
}
