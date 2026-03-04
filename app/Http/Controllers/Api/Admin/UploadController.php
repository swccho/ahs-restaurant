<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\UploadMenuItemImageRequest;
use App\Http\Requests\Admin\UploadOfferBannerRequest;
use App\Http\Requests\Admin\UploadRestaurantCoverRequest;
use App\Http\Requests\Admin\UploadRestaurantLogoRequest;
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

    public function uploadOfferBanner(UploadOfferBannerRequest $request): JsonResponse
    {
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = 'offers/' . $filename;

        $file->storeAs('offers', $filename, 'public');

        $url = Storage::disk('public')->url($path);

        return ApiResponse::success(
            [
                'path' => $path,
                'url' => $url,
            ],
            'Banner uploaded.'
        );
    }

    public function uploadRestaurantLogo(UploadRestaurantLogoRequest $request): JsonResponse
    {
        $restaurantId = $this->restaurantId();
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $dir = "restaurants/{$restaurantId}/logo";
        $path = "{$dir}/{$filename}";

        $file->storeAs($dir, $filename, 'public');

        $url = Storage::disk('public')->url($path);

        return ApiResponse::success(
            [
                'path' => $path,
                'url' => $url,
            ],
            'Logo uploaded.'
        );
    }

    public function uploadRestaurantCover(UploadRestaurantCoverRequest $request): JsonResponse
    {
        $restaurantId = $this->restaurantId();
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $dir = "restaurants/{$restaurantId}/cover";
        $path = "{$dir}/{$filename}";

        $file->storeAs($dir, $filename, 'public');

        $url = Storage::disk('public')->url($path);

        return ApiResponse::success(
            [
                'path' => $path,
                'url' => $url,
            ],
            'Cover uploaded.'
        );
    }
}
