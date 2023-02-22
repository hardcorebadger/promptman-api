<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prompt;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $prompts = Prompt::where("user_id", Auth::user()->id)->get();
        return response()->json([
            'status' => 'success',
            'prompts' => $prompts,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project' => 'required'
        ]);

        $project = Project::find($request->project);

        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $prompt = Prompt::create([
            'name' => $request->name,
            'user_id' => Auth::user()->id,
            'project_id' => $request->project
        ]);

        return response()->json([
            'status' => 'success',
            'prompt' => $prompt,
        ]);
    }

    public function load($id)
    {
        $prompt = Prompt::find($id);

        if ($prompt->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'prompt' => $prompt,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'max:255|nullable',
            'payload' => 'nullable',
            'settings' => 'array|nullable',
        ]);

        $prompt = Prompt::find($id);

        if ($prompt->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($request->has('name'))
            $prompt->name = $request->name;
        if ($request->has('payload'))
            $prompt->payload = $request->payload;
        if ($request->has('settings'))
            $prompt->settings = $request->settings;

        $prompt->save();

        return response()->json([
            'status' => 'success',
            'prompt' => $prompt,
        ]);
    }

    public function destroy($id)
    {
        $prompt = Prompt::find($id);
        if ($prompt->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $prompt->delete();

        return response()->json([
            'status' => 'success',
            'project' => $prompt,
        ]);
    }
}
