<?php

namespace App\Http\Controllers;

use App\Models\FileNode;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Models\Prompt;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $todos = Project::where("user_id", Auth::user()->id)->get();
        return response()->json([
            'status' => 'success',
            'projects' => $todos,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'user_id' => Auth::user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'project' => $project,
        ]);
    }

    public function load($id)
    {
        $project = Project::find($id);
        if ($project == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found',
            ], 404);
        }
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'project' => $project,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::find($id);
        if ($project == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found',
            ], 404);
        }
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $project->name = $request->name;
        $project->save();

        return response()->json([
            'status' => 'success',
            'todo' => $project,
        ]);
    }

    public function destroy($id)
    {
        $project = Project::find($id);
        if ($project == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found',
            ], 404);
        }
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $project->delete();

        return response()->json([
            'status' => 'success',
            'project' => $project,
        ]);
    }

    public function get_prompts($id)
    {
        $project = Project::find($id);
        if ($project == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found',
            ], 404);
        }
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $prompts = Prompt::where("user_id", Auth::user()->id)->where("project_id",$id)->get();
        return response()->json([
            'status' => 'success',
            'projects' => $prompts,
        ]);
    }

    public function get_files($id)
    {
        $project = Project::find($id);
        if ($project == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found',
            ], 404);
        }
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $files = FileNode::where("project_id",$id)->get();
        return response()->json([
            'status' => 'success',
            'files' => $files,
        ]);
    }
    
}
