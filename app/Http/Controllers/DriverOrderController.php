<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverOrderController extends Controller
{
    public function nearbyOrders(Request $request)
    {
        $driver_commission = config('app.driver_commission.percentage');
        $minPerStoreOrder = config('app.delivery_time.minutes_per_store_order');
        $minPerItem = config('app.delivery_time.minutes_per_item');

        $lat = $request->coordinates['latitude'];
        $lng = $request->coordinates['longitude'];

        $query = Order::query();
        $query->select([
            'orders.id',
            'orders.user_id',
            'orders.store_id',
            'orders.address_id',
            'orders.payment_method',
            'orders.payment_method',
            'orders.created_at',
        ]);
        $query->addSelect(DB::raw("(orders.shipping_price * {$driver_commission}) as driver_commission"));
        $query->addSelect(DB::raw("distance(stores.latitude, stores.longitude, " . $lat . ", " . $lng . ") as store_distance"));
        $query->addSelect(DB::raw("distance(stores.latitude, stores.longitude, addresses.latitude, addresses.longitude) as address_distance"));
        $query->where('orders.status', 'processing');
        $query->where('orders.shipping_method', 'delivery');
        $query->where('orders.driver_id', null);

        $query->join('stores', 'stores.id', '=', 'orders.store_id');
        $query->join('addresses', 'addresses.id', '=', 'orders.address_id');

        $query->with([
            'address' => function ($subQuery) {
                $subQuery->select([
                    'id',
                    'street',
                    'number',
                    'postal_code',
                    'latitude',
                    'longitude',
                ]);
            },
            'user' => function ($subQuery) {
                $subQuery->select([
                    'id',
                    'name',
                ]);
            },
            'store' => function ($subQuery) {
                $subQuery->select([
                    'id',
                    'name',
                    'address',
                    'latitude',
                    'longitude',
                ]);
                 $subQuery->withCount([
                    'orders' => function ($sub2) {
                        $sub2->where('status', 'processing');
                    }
                ]);
            },
        ]);

        $orders = $query->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No nearby orders found',
                'data' => []
            ], 404);
        }

        $orders->each(function ($order) use ($minPerStoreOrder, $minPerItem) {
            $order->store->append('logo', 'cover');
            $preparation_time = ($order->store->orders_count * $minPerStoreOrder) + ($order->products->sum('quantity') * $minPerItem);Add commentMore actions
            $order->preparation_at = $order->created_at->addMinutes($preparation_time);
        });

        return response()->json([
            'success' => true,
            'message' => 'Nearby orders retrieved successfully',
            'data' => [
                'orders' => $orders,
            ]
        ], 200);
    }

    public function takeOrder(Request $request)
    {
        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => []
            ], 404);
        }

        if ($order->driver_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order is already taken by another driver',
            ], 400);
        }

        $order->driver_id = $request->user()->id;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order taken successfully',
            'data' => [
                'order' => $order,
            ]
        ], 200);
    }

    public function orderDetails($id, Request $request)
    {
        $driver = $request->user();
        $driver_commission = config('app.driver_commission.percentage');

        $lat = $request->coordinates['latitude'];
        $lng = $request->coordinates['longitude'];

        $query = Order::query();
        $query->select([
            'orders.id',
            'orders.store_id',
            'orders.address_id',
            'orders.user_id',
            'orders.payment_method',
            'orders.payment_status',
            'orders.status',
            'orders.shipping_status',
            'orders.total_price',
        ]);
        $query->addSelect(DB::raw('(orders.shipping_price * ' . $driver_commission . ') as driver_commission'));
        $query->addSelect(DB::raw('distance(stores.latitude, stores.longitude, ' . $lat . ', ' . $lng . ') as store_distance'));
        $query->addSelect(DB::raw('distance(stores.latitude, stores.longitude, addresses.latitude, addresses.longitude) as address_distance'));

        $query->join('stores', 'stores.id', '=', 'orders.store_id');
        $query->join('addresses', 'addresses.id', '=', 'orders.address_id');

        $query->where('orders.id', $id);
        $query->where('orders.driver_id', $driver->id);

        $query->with([
            'address' => function ($query) {
                $query->select([
                    'id',
                    'street',
                    'number',
                    'postal_code',
                    'latitude',
                    'longitude'
                ]);
            },
            'user' => function ($query) {
                $query->select([
                    'id',
                    'name'
                ]);
            },
            'store' => function ($query) {
                $query->select([
                    'id',
                    'name',
                    'address',
                    'latitude',
                    'longitude'
                ]);
            }
        ]);

        $order = $query->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order details retrieved successfully',
            'data' => [
                'order' => $order
            ]
        ]);
    }

    public function startDelivery(Request $request)
    {
        $order = Order::find($request->order_id);
        $driver = $request->user();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => []
            ], 404);
        }

        if ($order->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this order',
            ], 403);
        }

        if ($order->status !== 'processing') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not in a valid state for delivery',
            ], 400);
        }

        $order->status = 'out_for_delivery';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order taken successfully',
            'data' => []
        ], 200);
    }

    public function completePayment(Request $request)
    {
        $driver = $request->user();
        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => []
            ], 404);
        }

        if ($order->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order belongs to another driver',
                'data'=> []
            ], 403);
        }

        if ($order->status !== 'out_for_delivery') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not in out for delivery status',
                'data'=> []
            ], 400);
        }

        if ($order->payment_method !== 'cod') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not cash on delivery',
                'data'=> []
            ], 400);
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order payment is not pending',
                'data'=> []
            ], 400);
        }

        $order->payment_status = "completed";
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order payment completed successfully',
            'data' => []
        ],200);
    }

    public function completeDelivery(Request $request)
    {
        $order = Order::find($request->order_id);
        $driver = $request->user();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => []
            ], 404);
        }

        if ($order->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this order',
            ], 403);
        }

        if ($order->status !== 'out_for_delivery') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not in a valid state for delivery completion',
            ], 400);
        }

        if (!($order->payment_method === "card" || ($order->payment_status === 'completed' && $order->payment_method === 'cod'))) {
            return response()->json([
                'success' => false,
                'message' => 'Payment must be completed before completing the delivery',
                'data' => []
            ], 400);
        }

        $order->shipping_status = 'completed';
        $order->status = 'completed';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order taken successfully',
            'data' => []
        ], 200);
    }
}
