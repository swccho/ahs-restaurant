<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\StoreOfferRequest;
use App\Http\Requests\Admin\ToggleOfferRequest;
use App\Http\Requests\Admin\UpdateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Http\Responses\ApiResponse;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Offer::class);

        $query = Offer::query()
            ->forRestaurant($this->restaurantId())
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->string('search')->toString() . '%');
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        if ($request->boolean('valid_now')) {
            $query->where('is_active', true)
                ->where(function ($q): void {
                    $now = now();
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($q): void {
                    $now = now();
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                });
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => OfferResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function store(StoreOfferRequest $request): JsonResponse
    {
        $this->authorize('create', Offer::class);

        $offer = Offer::create([
            'restaurant_id' => $this->restaurantId(),
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'type' => $request->validated('type'),
            'value' => $request->validated('value'),
            'min_order_amount' => $request->validated('min_order_amount'),
            'coupon_code' => $request->validated('coupon_code'),
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'is_active' => $request->boolean('is_active', true),
            'banner_path' => $request->validated('banner_path'),
        ]);

        return ApiResponse::success(
            new OfferResource($offer),
            'Offer created.',
            201
        );
    }

    public function show(Offer $offer): JsonResponse
    {
        $this->authorize('view', $offer);

        return ApiResponse::success(new OfferResource($offer));
    }

    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        $this->authorize('update', $offer);

        $offer->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'type' => $request->validated('type'),
            'value' => $request->validated('value'),
            'min_order_amount' => $request->validated('min_order_amount'),
            'coupon_code' => $request->validated('coupon_code'),
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'is_active' => $request->boolean('is_active', $offer->is_active),
            'banner_path' => $request->input('banner_path', $offer->banner_path),
        ]);

        return ApiResponse::success(new OfferResource($offer->fresh()), 'Offer updated.');
    }

    public function destroy(Offer $offer): JsonResponse
    {
        $this->authorize('delete', $offer);

        $offer->delete();

        return ApiResponse::success(null, 'Offer deleted.');
    }

    public function toggle(ToggleOfferRequest $request, Offer $offer): JsonResponse
    {
        $this->authorize('update', $offer);

        $offer->update(['is_active' => ! $offer->is_active]);

        return ApiResponse::success(
            new OfferResource($offer->fresh()),
            'Offer status updated.'
        );
    }
}
