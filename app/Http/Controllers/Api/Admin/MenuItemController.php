<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\StoreMenuItemRequest;
use App\Http\Requests\Admin\ToggleMenuItemAvailabilityRequest;
use App\Http\Requests\Admin\ToggleMenuItemFeaturedRequest;
use App\Http\Requests\Admin\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Http\Responses\ApiResponse;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MenuItem::class);

        $query = MenuItem::query()
            ->with('category')
            ->forRestaurant($this->restaurantId())
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search')->toString() . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => MenuItemResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $this->authorize('create', MenuItem::class);

        $restaurantId = $this->restaurantId();
        $slug = MenuItem::uniqueSlugForRestaurant($restaurantId, $request->validated('name'));

        $menuItem = MenuItem::create([
            'restaurant_id' => $restaurantId,
            'category_id' => $request->validated('category_id'),
            'name' => $request->validated('name'),
            'slug' => $slug,
            'description' => $request->validated('description'),
            'price' => $request->validated('price'),
            'discount_price' => $request->validated('discount_price'),
            'is_available' => $request->boolean('is_available', true),
            'is_featured' => $request->boolean('is_featured', false),
            'sort_order' => $request->validated('sort_order', 0),
        ]);

        $menuItem->load('category');

        return ApiResponse::success(
            new MenuItemResource($menuItem),
            'Menu item created.',
            201
        );
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        $this->authorize('view', $menuItem);
        $menuItem->load('category');

        return ApiResponse::success(new MenuItemResource($menuItem));
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        $this->authorize('update', $menuItem);

        $name = $request->validated('name');
        $slug = $menuItem->slug;
        if ($name !== $menuItem->name) {
            $slug = MenuItem::uniqueSlugForRestaurant($menuItem->restaurant_id, $name, $menuItem->id);
        }

        $menuItem->update([
            'category_id' => $request->validated('category_id'),
            'name' => $name,
            'slug' => $slug,
            'description' => $request->validated('description'),
            'price' => $request->validated('price'),
            'discount_price' => $request->validated('discount_price'),
            'is_available' => $request->boolean('is_available', $menuItem->is_available),
            'is_featured' => $request->boolean('is_featured', $menuItem->is_featured),
            'sort_order' => $request->validated('sort_order', $menuItem->sort_order),
        ]);

        $menuItem->load('category');

        return ApiResponse::success(new MenuItemResource($menuItem->fresh()), 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $this->authorize('delete', $menuItem);

        $menuItem->delete();

        return ApiResponse::success(null, 'Menu item deleted.');
    }

    public function availability(ToggleMenuItemAvailabilityRequest $request, MenuItem $menuItem): JsonResponse
    {
        $this->authorize('update', $menuItem);

        $menuItem->update(['is_available' => $request->boolean('is_available')]);

        return ApiResponse::success(
            new MenuItemResource($menuItem->load('category')->fresh()),
            'Menu item availability updated.'
        );
    }

    public function featured(ToggleMenuItemFeaturedRequest $request, MenuItem $menuItem): JsonResponse
    {
        $this->authorize('update', $menuItem);

        $menuItem->update(['is_featured' => $request->boolean('is_featured')]);

        return ApiResponse::success(
            new MenuItemResource($menuItem->load('category')->fresh()),
            'Menu item featured status updated.'
        );
    }
}
