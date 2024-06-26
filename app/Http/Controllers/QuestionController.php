<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function store(string $formSlug, Request $request)
    {
        $user = Auth::user();

        if(!$user){
            return response()->json([
                'message' => 'Unauthenticated',
            ], 422);
        }

        // cari form berdasar slug
        $form = Form::where('slug', $formSlug)->first();

        if (!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // Cek jika creator_id !== user->id
        if ($form->creator_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden access',
            ], 403);
        }

        // validator make reqquest all
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'choice_type' => 'required|in:short answer,paragraph,date,multiple choice,dropdown,checkboxes',
            'choices' => 'required_if:choice_type,multiple choice,dropdown,checkboxes|array',
            'is_required' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $question = new Question;
        $question->name = $request->name;
        $question->choice_type = $request->choice_type;
        // ? implode(',')
        $question->choices = $request->choices ? implode(',', $request->choices) : null;
        $question->is_required = $request->is_required;
        $question->form_id = $form->id;

        $question->save();

        return response()->json([
            'message' => 'Add question success',
            'question' => $question,
        ], 200);
    }

    // string formslug, int questionid
    
    public function destroy(string $formSlug, int $questionId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // form where slug first
        $form = Form::where('slug', $formSlug)->first();

        if (!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // creator_id !== user_id
        if ($form->creator_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden access',
            ], 403);
        }
        
        //qurestion wheree id question where form id first 
        $question = Question::where('id', $questionId)->where('form_id', $form->id)->first();

        if (!$question) {
            return response()->json([
                'message' => 'Question not found',
            ], 404);
        }

        $question->delete();

        return response()->json([
            'message' => 'Remove question success',
        ], 200);
    }
}
