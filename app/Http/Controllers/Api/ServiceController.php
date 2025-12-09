<?php

// app/Http/Controllers/Api/ServiceController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::where('is_active', true);
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $services = $query->get()->map(function($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'type' => $service->type,
                'sub_type' => $service->sub_type,
                'base_price' => $service->base_price,
                'price_per_km' => $service->price_per_km,
                'capacity' => $service->capacity,
                'estimated_time_minutes' => $service->estimated_time_minutes,
                'icon' => $service->icon
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }
}