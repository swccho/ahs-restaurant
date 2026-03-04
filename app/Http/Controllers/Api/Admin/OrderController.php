<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderListResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::query()
            ->forRestaurant($this->restaurantId())
            ->withSum('orderItems as item_count', 'qty')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($builder) use ($q): void {
                $builder->where('id', $q)
                    ->orWhere('customer_phone', 'like', '%' . $q . '%')
                    ->orWhere('customer_name', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->string('delivery_type')->toString());
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => OrderListResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
            'message' => 'Orders fetched',
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['orderItems', 'statusUpdatedBy']);

        return ApiResponse::success(new OrderDetailResource($order), 'Order fetched.');
    }

    public function status(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $currentStatus = OrderStatus::tryFrom($order->status);
        $requestedStatus = OrderStatus::tryFrom($request->validated('status'));

        if ($currentStatus === null || $requestedStatus === null) {
            return ApiResponse::error('Invalid status.', 422);
        }

        if (! $currentStatus->canTransitionTo($requestedStatus)) {
            return ApiResponse::error('Invalid status transition.', 422, [
                'current_status' => $order->status,
                'requested_status' => $request->validated('status'),
            ]);
        }

        DB::transaction(function () use ($request, $order): void {
            $order->update([
                'status' => $request->validated('status'),
                'status_updated_at' => now(),
                'status_updated_by' => Auth::id(),
                'cancel_reason' => $request->validated('status') === OrderStatus::Cancelled->value
                    ? $request->validated('cancel_reason')
                    : null,
            ]);
        });

        $order->load(['orderItems', 'statusUpdatedBy']);

        return ApiResponse::success(
            new OrderDetailResource($order->fresh()),
            'Order status updated.'
        );
    }
}
