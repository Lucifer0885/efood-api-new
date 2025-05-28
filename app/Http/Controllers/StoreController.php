<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class StoreController extends Controller
{
    public function index(Request $request)
    {
        $lat = $request->coordinates['latitude'];
        $lng = $request->coordinates['longitude'];

        $query = Store::query()
            ->with([
                'categories' => function ($subQuery) {
                    $subQuery->select('categories.id', 'categories.name');
                }
            ])
            ->select(
                'id',
                'name',
                'address',
                'latitude',
                'longitude',
                'working_hours',
                'active',
                'minimum_cart_value',
                'phone'
            )
            ->addSelect(DB::raw('distance(stores.latitude, stores.longitude, ' . $lat . ', ' . $lng . ') as distance'))
            ->where('active', true)
            ->whereRaw("JSON_EXTRACT(JSON_EXTRACT(working_hours, '$[" . date('w') . "]'), '$.start') <= TIME_FORMAT(NOW(), '%H:%i')")
            ->whereRaw("JSON_EXTRACT(JSON_EXTRACT(working_hours, '$[" . date('w') . "]'), '$.end') >= TIME_FORMAT(NOW(), '%H:%i')")
            ->whereRaw('distance(stores.latitude, stores.longitude, ' . $lat . ', ' . $lng . ') <= stores.delivery_range');

        /* Filter by categories */
        if ($request->has('categories.0')) {
            $query->whereHas('categories', function ($subQuery) use ($request) {
                $subQuery->whereIn('categories.id', $request->categories);
            });
        }

        // /* Sorting */
        switch ($request->sort) {
            case 'distance':
                $query->orderBy('distance');
                break;
            case '-distance':
                $query->orderByDesc('distance');
                break;
            case 'minimum_cart_value':
                $query->orderBy('minimum_cart_value');
                break;
            case '-minimum_cart_value':
                $query->orderByDesc('minimum_cart_value');
                break;
            default:
                $query->orderBy('distance');
                break;
        }

        $stores = $query->get();

        $stores->each->append(['logo', 'cover']);

        $shippingPriceFixed = config('app.shipping_price.fixed');
        $shippingPricePerKm = config('app.shipping_price.price_per_km');

        $stores->each(function ($store) use ($shippingPriceFixed, $shippingPricePerKm) {
            $store->shipping_price = round($shippingPriceFixed + $shippingPricePerKm * $store->distance, 1);
        });

        $response = [
            'success' => true,
            'message' => 'Stores retrieved successfully',
            'data' => [
                'queryParams'=> $request->all(),
                'stores' => $stores
            ]
        ];

        return response()->json($response);
    }

    public function show($id, Request $request){
        $query = Store::query();

        $lat = $request->coordinates['latitude'];
        $lng = $request->coordinates['longitude'];

        $query->select(
                'id',
                'name',
                'address',
                'latitude',
                'longitude',
                'working_hours',
                'active',
                'minimum_cart_value',
                'phone'
            )
            ->addSelect(DB::raw('distance(stores.latitude, stores.longitude, ' . $lat . ', ' . $lng . ') as distance'))
            ->whereId( $id )
            ->where('active', true)
            ->whereRaw("JSON_EXTRACT(JSON_EXTRACT(working_hours, '$[" . date('w') . "]'), '$.start') <= TIME_FORMAT(NOW(), '%H:%i')")
            ->whereRaw("JSON_EXTRACT(JSON_EXTRACT(working_hours, '$[" . date('w') . "]'), '$.end') >= TIME_FORMAT(NOW(), '%H:%i')")
            ->whereRaw('distance(stores.latitude, stores.longitude, ' . $lat . ', ' . $lng . ') <= stores.delivery_range');

        $query->with([
            'productCategories' => function ($subQuery) {
                $subQuery->select(
                    'product_categories.id',
                    'product_categories.name',
                    'product_categories.store_id',
                    'product_categories.sort');
                $subQuery->orderBy('product_categories.sort');
            },
            'productCategories.products'=> function ($subQuery) {
                $subQuery->select(
                    'products.id',
                    'products.name',
                    'products.description',
                    'products.price',
                    'products.product_category_id',
                    'products.store_id',
                    'products.active',
                    'products.sort'
                );
                $subQuery->orderBy('products.sort');
            }
        ]);

        $store = $query->first();

        $shippingPriceFixed = config('app.shipping_price.fixed');
        $shippingPricePerKm = config('app.shipping_price.price_per_km');
        $store->shipping_price = round($shippingPriceFixed + $shippingPricePerKm * $store->distance, 1);

        $store->append(["logo", "cover"]);

        foreach ($store->productCategories as $category) {
            $category->products->each->append(["mainImage"]);
        }

        $response = [
            'success' => true,
            'message' => 'Store retrieved successfully',
            'data' => [
                'store' => $store
            ]
        ];
        return response()->json($response);
    }
}
