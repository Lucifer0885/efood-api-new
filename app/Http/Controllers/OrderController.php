<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sebdesign\VivaPayments\Enums\TransactionStatus;
use Sebdesign\VivaPayments\Facades\Viva;
use Sebdesign\VivaPayments\VivaException;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()
            ->with([
                'products.product',
                'store'
            ])
            ->orderByDesc('created_at')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                $product->product->append('mainImage');
            }
            $order->store->append('logo');
        }

        $response = [
            'success' => true,
            'message' => "Orders retrieved successfully for user: {$user->name}",
            'data' => [
                'orders' => $orders,
            ],
        ];

        return response()->json($response, 200);
    }


    public function store(Request $request)
    {
        $user = $request->user();
        $address = $user->addresses()->find($request->address_id);
        $store = Store::find($request->store_id);

        if (!$address) {
            $response = [
                'success' => false,
                'message' => 'Address not found'
            ];
            return response()->json($response, 404);
        }

        if (!$store) {
            $response = [
                'success' => false,
                'message' => 'Store not found'
            ];
            return response()->json($response, 404);
        }

        $distanceInKm = DB::select("SELECT distance({$store->latitude},{$store->longitude},{$address->latitude},{$address->longitude}) as distance")[0]->distance;
        if ($store->delivery_range < $distanceInKm) {
            $response = [
                'success' => false,
                'message' => 'Address out of delivery range'
            ];
            return response()->json($response, 400);

        }

        $order = new Order();
        $order->user_id = $user->id;
        $order->store_id = $store->id;
        $order->address_id = $address->id;
        $order->payment_method = $request->payment_method;
        $order->shipping_method = $request->shipping_method;
        $order->note = $request->note;
        $order->tip = $request->tip;
        $order->delivery_time = 0;
        $order->shipping_price = 0;

        if ($order->payment_method === "cod") {
            $order->status = "processing";
        }

        $order->save();

        $order->products_price = 0;
        foreach ($request->products as $p) {
            $product = $store->products()->find($p['product_id']);
            if (!$product) {
                $response = [
                    'success' => false,
                    'message' => 'Some products not found'
                ];
                return response()->json($response, 404);
            }

            $orderProduct = new OrderProduct();
            $orderProduct->product_id = $product->id;
            $orderProduct->product_name = $product->name;
            $orderProduct->note = $p['note'];
            $orderProduct->quantity = $p['quantity'];
            $orderProduct->price = $product->price;
            $orderProduct->total_price = $product->price * $p['quantity'];
            $order->products_price += $orderProduct->total_price;
            $order->products()->save($orderProduct);
        }
        $order->save();

        if ($order->shipping_method === 'delivery') {

            // Calculate delivery time and shipping price

            $minPerStoreOrder = config('app.delivery_time.minutes_per_store_order');
            $minPerItem = config('app.delivery_time.minutes_per_item');
            $minPerKm = config('app.delivery_time.minutes_per_km');
            // $minPerDriverOrder = config('app.delivery_time.minutes_per_driver_order');

            $storeOrdersCount = $store->orders()
                ->whereIn('status', ['processing'])
                // ->whereId('!=', $order->id) // Exclude current order
                ->count();
            $orderProductsCount = $order->products()->count();
            $shippingPriceFixed = config('app.shipping_price.fixed');
            $shippingPricePerKm = config('app.shipping_price.price_per_km');

            $order->delivery_time = abs($minPerStoreOrder * $storeOrdersCount + $minPerItem * $orderProductsCount + $minPerKm * $distanceInKm);
            $order->shipping_price = round($shippingPriceFixed + $shippingPricePerKm * $distanceInKm, 2);
        }

        $order->discount = 0;
        if ($request->has('coupon_code')) {
            $couponIsValid = true;
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('active', true)
                ->first();

            if (!$coupon) {
                $couponIsValid = false;
            } else {
                if ($coupon->start_date && $coupon->start_date->isFuture()) {
                    $couponIsValid = false;
                }

                if ($coupon->end_date && $coupon->end_date->isPast()) {
                    $couponIsValid = false;
                }
            }

            if ($couponIsValid) {
                $order->coupon_code = $coupon->code;
                $coupon->type === 'percentage'
                   ? $order->discount = $order->products_price * ($coupon->value / 100)
                   : $order->discount = $coupon->value;
                $order->save();
            }
        }

        $order->total_price = $order->products_price + $order->shipping_price - $order->discount + $order->tip;
        $order->save();

        $vivaRedirectUrl = null;
        if ($order->payment_method === 'card') {
            $order->createVivaCode();
            $vivaRedirectUrl = $order->getVivaUrl();
        }

        $response = [
            'success' => true,
            'message' => 'Order created',
            'data' => [
                'order' => $order->refresh(),
                'viva_redirect_url' => $vivaRedirectUrl,
            ]
        ];
        return response()->json($response, 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = $user->orders()
            ->with([
                'products.product',
                'store',
                'address'
            ])
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => "Order with ID {$id} not found for user: {$user->name}",
            ], 404);
        }

        foreach ($order->products as $product) {
            $product->product->append('mainImage');
        }
        $order->store->append('logo');

        $response = [
            'success' => true,
            'message' => "Order retrieved successfully for user: {$user->name}",
            'data' => [
                'order' => $order,
            ],
        ];

        return response()->json($response, 200);
    }

    public function vivaReturn(Request $request)
    {
        try {
            $transaction = Viva::transactions()->retrieve($request->input('t'));
        } catch (VivaException $e) {
            //
        }

        $order_id =  str_replace("order:", "", $transaction->merchantTrns);
        $order = Order::find($order_id);
        if (!$order) {
            $response = [
                'success' => false,
                'message' => 'Order not found'
            ];
            return response()->json($response, 404);
        }

        switch ($transaction->statusId) {
            case TransactionStatus::PaymentSuccessful:
                $order->payment_status = 'completed';
                $order->status = 'processing';
                // notify store
                break;
            case TransactionStatus::Error:
                $order->payment_status = 'failed';
                $order->status = 'cancelled';
                break;
        }

        $order->save();

        return redirect()->to(env("CLIENT_URL") . "/orders/{$order->id}");
    }

}
