<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\UploadMenuItemImageRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends AdminController
{
    public function uploadMenuItemImage(UploadMenuItemImageRequest $request): JsonResponse
    {
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = 'menu-items/' . $filename;

        $file->storeAs('menu-items', $filename, 'public');

        $url = Storage::disk('public')->url($path);

        return ApiResponse::success(
            [
                'path' => $path,
                'url' => $url,
            ],
            'Image uploaded.'
        );
    }
}
