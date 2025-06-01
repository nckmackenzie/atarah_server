<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clients = Client::query();
        if($search = $request->input('q')) {
            $clients->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('tax_pin', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%");
            });
        }
        $clients = $clients->orderBy('name')
                           ->get();
        return response()->json(['data' => $clients]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request)
    {
        return $this->handleCreateUpdateClient($request, new Client());
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return response()->json(['data' => $client]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, Client $client)
    {
        return $this->handleCreateUpdateClient($request, $client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // TODO: check whether the client can be deleted (e.g., no associated forms)
        try {
            $client->delete();
            return response()->json(['message' => 'Client deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Error deleting client: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting client. Try again later'], 500);
        }
    }

    private function handleCreateUpdateClient(ClientRequest $request,Client $client)
    {
        try {
            $client->fill($request->validated());
            $client->save();
            return response()->json(['message' => 'Client saved successfully'], $client->wasRecentlyCreated ? 201 : 200);
        } catch (\Exception $e) {
            Log::error('Error saving client: ' . $e->getMessage());
            return response()->json(['message' => 'Error saving client. Try again later'], 500);
        } 
    }
}
