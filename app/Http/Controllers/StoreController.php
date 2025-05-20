<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $lat = $request->coordinates->latitude;
        $lng = $request->coordinates->longitude;

        $stores = Store::query()
            // ->select(['id', 'name', 'description', 'address_id', 'category_id'])
            // ->with(['address:id,street,number,city,postal_code,latitude,longitude', 'category:id,name'])
            ->orderBy('name')
            ->get();

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
