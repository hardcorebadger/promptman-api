<?php

namespace App\Http\Controllers;

use App\Models\FileNode;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Models\Prompt;
use Orhanerday\OpenAi\OpenAi;


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

    public function set_api_key(Request $request, $id)
    {
        $request->validate([
            'openai_api_key' => 'required|string',
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

        $api_validation = false;

        //validate openai api key
        $open_ai = new OpenAi($request->openai_api_key);
        $response = $open_ai->listModels();
        if ($response == null || json_decode(($response)) == null) {
            $api_validation = false;
        }

        $c = json_decode($response);

        if (isset($c->error)) {
            $api_validation = false;
            
        } else {
            $api_validation = true;
        }

        if ($api_validation) {

            $project->openai_api_key = $request->openai_api_key;
            $project->save();

            return response()->json([
                'status' => 'success',
                'project' => $project,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OpenAI API Key',
            ], 410);
        }
    
    }
    

}
