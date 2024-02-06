<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventQuestionController extends Controller
{
    public function index($event_id){
        $event = Event::find($event_id);
        $questions = $event->questions;
        return view('admin.event_settings.questions.view', compact('event', 'questions'));
    }

    public function add($event_id){
        return view('admin.event_settings.questions.add');
    }
    public function edit($event_id, $id){
        $event = Event::find($event_id);
        $question = $event->questions()->find($id);
        return view('admin.event_settings.questions.edit', compact('question'));
    }
    public function store(Request $request, $event_id){
        $request->validate([
            'question' => 'required',
            'type' => 'required|in:text,number,email,date,time,select,radio,checkbox',
            'options' => 'required_if:type,select|required_if:type,radio|required_if:type,checkbox',
        ]);
        $event = Event::find($event_id);
        if($request->has('id')){
            $question = $event->questions()->find($request->id);
            $question->update([
                'question' => $request->question,
                'type' => $request->type,
                'options' => $request->options,
            ]);
            return redirect()->back()->with('success', 'Question updated successfully');
        }
        $event->questions()->create([
            'question' => $request->question,
            'type' => $request->type,
            'options' => $request->options,
        ]);
        return redirect()->back()->with('success', 'Question added successfully');
    }
}
