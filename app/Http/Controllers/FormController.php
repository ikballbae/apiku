<?php

namespace App\Http\Controllers;

use App\Models\AllowedDomain;
use App\Models\Form;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $forms = Form::where('creator_id', $user->id)->get();

        return response()->json([
            'message' => 'Get all forms success',
            'forms' => $forms,
        ], 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $rules = [
            'name' => 'required',
            'slug' => 'required|unique:forms,slug|alpha_dash', // Custom validation rule
            'description' => 'required',
            'limit_one_response' => 'required|boolean',
            'allowed_domains' => 'required|array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()->getMessages(),
            ], 422);
        }

        $form = new Form;
        $form->name = $request->name;
        $form->slug = Str::slug($request->slug);
        $form->description = $request->description;
        $form->limit_one_response = $request->limit_one_response;
        $form->creator_id = $user->id;

        $form->save();

        // Save allowed domains
        $allowedDomains = [];
        foreach ($request->allowed_domains as $domain) {
            $allowedDomains[] = [
                'form_id' => $form->id,
                'domain' => $domain,
            ];
        }

        // Insert allowed domains using bulk insert
        AllowedDomain::insert($allowedDomains);

        return response()->json([
            'message' => 'Create form success',
            'form' => $form,
        ], 200);
    }

    public function show(string $formSlug)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Form slug validation
        $validator = Validator::make(['slug' => $formSlug], [
            'slug' => 'required|alpha_dash', // Ensure valid slug format
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid form slug',
            ], 400); // Bad request
        }

        $form = Form::where('slug', $formSlug)->first();

        if (!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // Check if user domain is allowed
        $allowed = AllowedDomain::where('form_id', $form->id)
            ->where('domain', $user->email ? explode('@', $user->email)[1] : null)
            ->exists();

        if (!$allowed) {
            return response()->json([
                'message' => 'Forbidden access',
            ], 403);
        }

        $questions = Question::where('form_id', $form->id)->get();

        $form->questions = $questions; // Attach questions to form object

        return response()->json([
            'message' => 'Get form success',
            'form' => $form,
        ], 200);
    }
}