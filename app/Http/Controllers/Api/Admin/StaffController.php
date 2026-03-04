<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\ToggleStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAnyStaff', User::class);

        $query = User::query()
            ->where('restaurant_id', $this->restaurantId())
            ->orderBy('name');

        if ($request->filled('search')) {
            $q = $request->string('search')->toString();
            $query->where(function ($builder) use ($q): void {
                $builder->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => StaffResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
            'message' => 'Staff list fetched.',
        ]);
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $this->authorize('createStaff', User::class);

        $user = User::create([
            'restaurant_id' => $this->restaurantId(),
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => 'staff',
            'is_active' => true,
        ]);

        return ApiResponse::success(
            new StaffResource($user),
            'Staff created.',
            201
        );
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('updateStaff', $user);

        return ApiResponse::success(new StaffResource($user));
    }

    public function update(UpdateStaffRequest $request, User $user): JsonResponse
    {
        $this->authorize('updateStaff', $user);

        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role', $user->role),
        ]);

        return ApiResponse::success(new StaffResource($user->fresh()), 'Staff updated.');
    }

    public function toggle(ToggleStaffRequest $request, User $user): JsonResponse
    {
        $this->authorize('toggleStaff', $user);

        if ($user->id === Auth::id()) {
            return ApiResponse::error('You cannot disable your own account.', 422);
        }

        if ($request->boolean('is_active') === false && $user->isOwner()) {
            $ownerCount = User::query()
                ->where('restaurant_id', $this->restaurantId())
                ->where('role', 'owner')
                ->where('is_active', true)
                ->count();
            if ($ownerCount <= 1) {
                return ApiResponse::error('Cannot disable the last active owner.', 422);
            }
        }

        $user->update(['is_active' => $request->boolean('is_active')]);

        return ApiResponse::success(new StaffResource($user->fresh()), 'Staff status updated.');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('deleteStaff', $user);

        $user->delete();

        return ApiResponse::success(null, 'Staff deleted.');
    }
}
