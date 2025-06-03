<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Service::with('account:id,name');
        
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('rate', 'like', "%{$search}%")
                  ->orWhereHas('account', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $services = $query->orderBy('name')->get();
        return response()->json(['data' => $services]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        return $this->handleCreateUpdateService($request, new Service());
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        $service->load('account:id,name'); 
        return response()->json(['data' => $service]);
    }
  

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, Service $service)
    {
        return $this->handleCreateUpdateService($request, $service);
    }
  

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        try {
            $service->delete();
            return response()->json(['message' => 'Service deleted successfully.'], 204);
        } catch (\Exception $e) {
            Log::error('Error deleting service: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete service'], 500);
        }
    }

    private function handleCreateUpdateService(ServiceRequest $request, Service $service)
    {
     
        try {
   
            $service->fill($request->validated());
            $service->save();

            return response()->json(['message' => 'Service saved successfully.'], $service->wasRecentlyCreated ? 201 : 200);
        } catch (\Exception $e) {
            Log::error('Error creating/updating service: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create/update service'], 500);
        }
    }

}
