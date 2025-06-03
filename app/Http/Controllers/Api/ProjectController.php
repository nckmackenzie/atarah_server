<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = Project::query();
        if($search = $request->input('q')){
            $projects->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $projects = $projects->orderBy('name')->get();
        return response()->json(['data' => $projects]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {
        return $this->handleCreateOrUpdate($request, new Project());
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return response()->json(['data' => $project]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project)
    {
        return $this->handleCreateOrUpdate($request, $project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        // TODO: Check if the project is associated with any expenses or other resources before deletion
        
        try {
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete project: ' . $e->getMessage()], 500);
        } 
    }

    private function handleCreateOrUpdate(ProjectRequest $request, Project $project)
    {
        $project->fill($request->validated());
        $project->save();

        return response()->json(['data' => $project], 201);
    }
}
