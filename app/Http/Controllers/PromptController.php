<?php

namespace App\Http\Controllers;

use App\Models\FileNode;
use Illuminate\Http\Request;
use App\Models\Prompt;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Orhanerday\OpenAi\OpenAi;

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

        $file = FileNode::where('content_id', $prompt->id)->where('type', 'prompt')->first();
        $prompt->name = $file->name;
        $prompt->file_id = $file->id;

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
        if ($prompt == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prompt not found',
            ], 404);
        }

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
        if ($prompt == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prompt not found',
            ], 404);
        }

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

    public function run(Request $request, $id)
    {
        $request->validate([
            'name' => 'max:255|nullable',
            'payload' => 'nullable',
            'settings' => 'array|nullable',
        ]);

        $prompt = Prompt::find($id);
        if ($prompt == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prompt not found',
            ], 404);
        }

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

        $settings = $prompt->settings;

        // run GPT3 prompt
        $open_ai_key = getenv('OPENAI_API_KEY');
        $open_ai = new OpenAi($open_ai_key);

        $complete = $open_ai->completion([
            'model' => $settings['model'],
            'prompt' => $prompt->payload,
            'temperature' => $settings['tempurature'],
            'max_tokens' => $settings['max_tokens'],
            'frequency_penalty' => $settings['frequency_penalty'],
            'presence_penalty' => $settings['presence_penalty'],
        ]);

        if ($complete == null || json_decode(($complete)) == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Completion failed. Unknown error. Please run again.',
                'prompt' => $prompt,
            ], 500);
        }

        $c = json_decode($complete);

        if (isset($c->error)) {
            return response()->json([
                'status' => 'error',
                'message' => $c->error->message,
                'prompt' => $prompt,
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'prompt' => $prompt,
            'completion' => $c,
        ]);
    }
}
