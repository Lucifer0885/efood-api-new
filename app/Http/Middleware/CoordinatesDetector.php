<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoordinatesDetector
{
    public function handle(Request $request, Closure $next): Response
    {
        $coordinates = [
            'latitude' => 40.6449329,
            'longitude' => 22.9416259,
        ];

        if ($request->header('X-Location')){
            $location = explode(',', $request->header('X-Location'));
            $coordinates['latitude'] = $location[0];
            $coordinates['longitude'] = $location[1];
        }

        $request->merge(['coordinates'=> $coordinates]);

        return $next($request);
    }
}
