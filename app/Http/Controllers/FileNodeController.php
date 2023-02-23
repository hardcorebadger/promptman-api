<?php

namespace App\Http\Controllers;

use App\Models\FileNode;
use App\Models\Project;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileNodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function create(Request $request)
    {
        $request->validate([
            'project' => 'required',
            'parent' => 'nullable',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        // confirm the user has access to the project
        $project = Project::find($request->project);
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        // check parent exists if it's not null
        $parent = $request->has('parent') ? $request->parent : null;
        if ($parent != null) {
            $pNode = FileNode::find($parent);
            if ($pNode == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parent does not exist',
                ], 410);
            }
        }

        // if it's not a group, create the corresponding file type
        $content_id = null;
        if ($request->type != "group") {

            if ($request->type == "prompt") {
                // create a prompt
                $prompt = Prompt::create([
                    'name' => $request->name,
                    'user_id' => Auth::user()->id,
                    'project_id' => $request->project
                ]);
                $content_id = $prompt->id;
            } else {
                // invalide type
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid file type',
                ], 411);
            }

        }

        $file = FileNode::create([
            'project_id' => $request->project,
            'parent_id' => $parent,
            'name' => $request->name,
            'type' => $request->type,
            'content_id' => $content_id
        ]);

        return response()->json([
            'status' => 'success',
            'file' => $file,
        ]);
       

    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'parent' => 'nullable',
            'name' => 'nullable|string|max:255',
        ]);

        $file = FileNode::find($id);
        if ($file == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found',
            ], 404);
        }

        $project = Project::find($file->project_id);
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($request->has('name'))
            $file->name = $request->name;
        if ($request->has('parent')) {
            // can send a null parent that's okay
            if ($request->parent != null) {
                // if its not null, check parent exists
                $pNode = FileNode::find($request->parent);
                if ($pNode == null) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Parent does not exist',
                    ], 410);
                }
            }
            $file->parent_id = $request->parent;
        }

        $file->save();

        return response()->json([
            'status' => 'success',
            'file' => $file,
        ]);
    }

    public function destroy($id)
    {
        $file = FileNode::find($id);
        if ($file == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found',
            ], 404);
        }

        $project = Project::find($file->project_id);
        if ($project->user_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $file->delete();

        return response()->json([
            'status' => 'success',
            'file' => $file,
        ]);
    }
}
