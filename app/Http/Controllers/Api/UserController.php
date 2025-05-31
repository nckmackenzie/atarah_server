<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with('role:id,name');

        if ($request->has('q')) {
            $search = $request->input('q');
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        return response()->json(['data' => $users->get()]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        return $this->handleCreateOrUpdate($request, new User());
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('role:id,name');
        return response()->json(['data' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        return $this->handleCreateOrUpdate($request, $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        try {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['error' => 'Error deleting user'], 500);
        }
    }

    private function handleCreateOrUpdate(UserRequest $request, User $user)
    {
        try {
            
            $validatedData = $request->validated();
        
            
            if (!$user->exists) {
                 $validatedData['password'] = bcrypt('12345678');
            }

            $user->fill($validatedData);
            $user->save();

            return response()->json(['message' => 'User created successfully'], $user->wasRecentlyCreated ? 201 : 200);

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json(['error' => 'Error saving user'], 500);
        }
    }
}
