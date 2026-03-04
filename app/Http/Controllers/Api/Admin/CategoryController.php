<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\ReorderCategoriesRequest;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        $query = Category::query()
            ->forRestaurant($this->restaurantId())
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search')->toString() . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => CategoryResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $category = Category::create([
            'restaurant_id' => $this->restaurantId(),
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order', 0),
            'is_active' => true,
        ]);

        return ApiResponse::success(
            new CategoryResource($category),
            'Category created.',
            201
        );
    }

    public function show(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        return ApiResponse::success(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category->update([
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order', $category->sort_order),
        ]);

        return ApiResponse::success(new CategoryResource($category->fresh()), 'Category updated.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        if ($category->menuItems()->exists()) {
            return ApiResponse::error(
                'Cannot delete a category that has menu items. Move or delete the items first.',
                422
            );
        }

        $category->delete();

        return ApiResponse::success(null, 'Category deleted.');
    }

    public function toggle(Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category->update(['is_active' => ! $category->is_active]);

        return ApiResponse::success(
            new CategoryResource($category->fresh()),
            'Category status updated.'
        );
    }

    public function reorder(ReorderCategoriesRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        $restaurantId = $this->restaurantId();

        DB::transaction(function () use ($request, $restaurantId): void {
            foreach ($request->validated('items') as $item) {
                Category::query()
                    ->where('id', $item['id'])
                    ->where('restaurant_id', $restaurantId)
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        return ApiResponse::success(null, 'Categories reordered.');
    }
}
