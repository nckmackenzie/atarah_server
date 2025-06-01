<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\RoleRight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role = Role::withCount('users');
        if ($search = $request->input('q','')) {           
            $role->where('name', 'like', "%{$search}%");
        }

        return response()->json(['data' => $role->orderBy('name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        return $this->handleCreateOrUpdate($request, new Role());
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['rights.form:id,name']);
        $transformedRights = $role->rights->map(function ($right) {
            return [
                'form' => $right->form, // Only include the form object
            ];
        });
        
        $role->setRelation('rights', $transformedRights);
        return response()->json(['data' => $role],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role)
    {
        return $this->handleCreateOrUpdate($request, $role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        Gate::authorize('delete', $role);
        try {
            DB::transaction(function () use ($role) {
                $role->rights()->delete();
                $role->delete();
            });
            return response()->json(['message' => 'Role deleted successfully.'], 204);
        } catch (\Exception $e) {
            Log::error('Error in RoleController: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while deleting the role.',
            ], 500);
        }
    }

    private function handleCreateOrUpdate(RoleRequest $request, Role $role)
    {
        try {
            
            DB::transaction(function () use($request,$role) {
                $role->fill($request->only(['name', 'is_active']));
                $role->save();

                RoleRight::where('role_id', $role->id)->delete(); 
                
                foreach ($request->rights as $form) {
                    RoleRight::create([
                        'role_id' => $role->id, 
                        'form_id' => $form, 
                    ]);
                }

            });
            return response()->json(['data' => $role], 201);

        } catch (\Exception $e) {
           Log::error('Error in RoleController: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing your request.',
            ], 500);
        }
    }
}
