<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->get();

        return response()->json([
            'success' => true,
            'message' => 'Addresses retrieved successfully',
            'data' => [
                'addresses' => $addresses
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'street' => 'required',
            'number' => '',
            'city' => '',
            'postal_code' => '',
            'latitude' => 'required',
            'longitude' => 'required',
            'phone' => '',
            'floor' => '',
            'door' => '',
        ]);

        $address = new Address($request->all());
        $request->user()->addresses()->save($address);

        return response()->json([
            'success' => true,
            'message' => 'Address created',
            'data'=> [
                'address' => $address->refresh()
            ]
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        $response  = [
            'success'=> true,
            'message'=> 'Address retrieved',
            'data'=> [
                'address'=> $address
            ]
        ];

        return response()->json($response);
    }

     public function update(Request $request, $id)
    {
        $address = $request->user()->addresses()->find($id);
        if (!$address) {
            $response = [
                'success' => false,
                'message' => 'Address not found',
            ];
            return response()->json($response, 404);
        }

        $fields = $request->validate([
            'street' => 'required',
            'number' => '',
            'city' => '',
            'postal_code' => '',
            'latitude' => 'required',
            'longitude' => 'required',
            'phone' => '',
            'floor' => '',
            'door' => '',
        ]);
        $address->update($fields);

        $response = [
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => [
                'address' => $address
            ]
        ];

        return response()->json($response);
    }

    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted'
        ], 200);
    }
}
