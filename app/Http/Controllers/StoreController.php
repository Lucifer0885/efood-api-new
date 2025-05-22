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
            // ->whereRaw("JSON_EXTRACT(JSON_EXTRACT(working_hours, '$[" . date('w') . "]'), '$.start') <= TIME_FORMAT(NOW(), '%H:%i')")
            // ->whereRaw("JSON_EXTRACT(JSON_EXTRACT(working_hours, '$[" . date('w') . "]'), '$.end') >= TIME_FORMAT(NOW(), '%H:%i')")
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

        $response = [
            'success' => true,
            'message' => 'Stores retrieved successfully',
            'data' => [
                'stores' => $stores
            ]
        ];

        return response()->json($response);
    }
}
