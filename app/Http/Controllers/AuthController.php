<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Orhanerday\OpenAi\OpenAi;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
        
        $user = Auth::user();
        $project = Project::where('user_id', Auth::user()->id)->first();
        $user->project = $project;
        $user->api_validation = self::validate_api($project->openai_api_key);

        return response()->json([
                'status' => 'success',
                'user' => $user,
                'access_token' => $token
            ]);

    }

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $project = Project::create([
            'name' => 'Default Project',
            'user_id' => $user->id,
        ]);

        $token = Auth::login($user);
        $user->project = $project;
        $user->api_validation = self::validate_api($project->openai_api_key);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'access_token' => $token
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        $project = Project::where('user_id', Auth::user()->id)->first();
        $user = Auth::user();
        $user->project = $project;
        $user->api_validation = self::validate_api($project->openai_api_key);

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'access_token' => Auth::refresh()
        ]);
    }

    public function me()
    {
        $project = Project::where('user_id', Auth::user()->id)->first();
        $user = Auth::user();
        $user->project = $project;
        $user->api_validation = self::validate_api($project->openai_api_key);

        return response()->json([
            'status' => 'success',
            'user' => Auth::user()
        ]);
    }

    private function validate_api($openai_api_key) {
        $api_validation = false;

        //validate openai api key
        if ($openai_api_key != null) {
            $open_ai = new OpenAi($openai_api_key);
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
        }

        return $api_validation;
    }

}
