<?php

namespace App\Http\Controllers;

use App\Enums\RoleCode;
use App\Models\UserSocket;
use Illuminate\Http\Request;
use App\Models\User;

class SocketController extends Controller
{
    public function driverLocation(Request $request)
    {
       $request->validate([
            "driver_id"=> "required|exists:users,id",
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $driver = User::find($request->driver_id);
        if (!$driver->roles()->where('role_id', RoleCode::driver)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: User is not a driver',
                'data' => []
            ], 403);
        }

        $user_ids = $driver->orders()
            ->whereStatus('out_for_delivery')
            ->pluck('user_id')
            ->toArray();

        $socket_ids = UserSocket::whereIn('user_id', $user_ids)
            ->pluck('socket_id')
            ->toArray();

        if (count($socket_ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No active clients found for this driver.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Socket IDs retrieved successfully.',
            'data' => [
                'channel' => 'driver-tracking',
                'socket_ids'=> $socket_ids,
                'data'=> [
                    'driver_id'=> $request->driver_id,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]
            ]
        ]);
    }
}
