<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Form;
use App\Models\Question;
use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    public function store(string $formSlug, Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([ 'message' => 'Unauthenticated.'],401,);
        }

        $form = Form::where('slug', $formSlug)->first();

        if (!$form) {
            return response()->json(['message' => 'Form not found',],404);
        }

        if ($form->allowedDomains->count() > 0) {
            if (!$user) {
                return response()->json(['message' => 'Forbidden access',],401);
            }
        }

        // Check for limit of 1 response (optional)
        if ($form->limit_responses === 1) {
            $existingResponse = Response::where('form_id', $form->id)->where('user_id', $user->id)
                ->exists();

            if ($existingResponse) {
                return response()->json(
                    [
                        'message' => 'You can not submit form twice',
                    ],
                    422,
                );
            }
        }

        // Manual Validation (optional - consider using SubmitResponseRequest)
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id', // Validate question existence
            'answers.*.value' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }
    
        $response = new Response;
        $response->form_id = $form->id;
        $response->user_id = $user->id;
        $response->date = now();
    
        // Save the response without answers
        $response->save();
    
        // Save answers in a separate loop
        $answersData = $request->input('answers');
        foreach ($answersData as $answerData) {
            $question = Question::find($answerData['question_id']);
    
            if (!$question) {
                // Handle error: question doesn't exist
                return response()->json([
                    'message' => 'Invalid question ID.',
                ], 422);
            }
    
            $answer = new Answer;
            $answer->response_id = $response->id;
            $answer->question_id = $answerData['question_id'];
            $answer->value = $answerData['value'];
            $answer->save();
        }
    
        // Return success response with the response object
        return response()->json([
            'message' => 'Submit response success',
            'data' => $response,
        ], 200);
    }

    public function index(string $formSlug)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $form = Form::where('slug', $formSlug)->first();

        if (!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // Check if user created the form (creator access)
        if ($form->allowedDomains->count() > 0) {
            if (!$user) {
                return response()->json(
                    [
                        'message' => 'Forbidden access',
                    ],
                    401,
                );
            }
        }

        $responses = Response::where('form_id', $form->id)
            ->with('user')
            ->with('answers.question') // Eager load question data for answers
            ->get();

        $responseDataList = [];
        foreach ($responses as $response) {
            if (!$response) {
                continue;
            }
        
            $responseData = [
                'date' => $response->date->format('Y-m-d H:i:s'),
                'user' => User::find($response->user->id)->toArray(),
                'answers' => [],
            ];
        
            foreach ($response->answers as $answer) {
                $responseData['answers'][$answer->question->name] = $answer->value; // Use question title for key (optional)
        
                // OR (use question ID for key)
                // $responseData['answers'][$answer->question_id] = $answer->value;
            }
        
            $responseDataList[] = $responseData;
        }           

        return response()->json([
            'message' => 'Get responses success',
            'responses' => $responseDataList,
        ], 200);
    }
}
