<?php

namespace App\Http\Controllers;

use App\Models\EventEmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index($event_id)
    {
        return view('admin.event_settings.email_templates.email_template');
    }

    public function edit($event_id, $type)
    {
        $template = EventEmailTemplate::where('event_id', $event_id)->where('type', $type)->first();
        return view('admin.event_settings.email_templates.email_template', compact('template'));
    }

    public function view($event_id)
    {
        $templates = EventEmailTemplate::where('event_id', $event_id)->get();
        return view('admin.event_settings.email_templates.view', compact('templates'));
    }

    public function store(Request $request, $event_id)
    {
        $request->validate([
            'subject' => 'required',
            'body' => 'required',
            'type' => 'required',
        ]);

        $event_email_template = EventEmailTemplate::where('event_id', $event_id)->where('type', $request->type)->first();
        if($event_email_template){
            $event_email_template->update([
                'subject' => $request->subject,
                'body' => $request->body,
            ]);
        }else{
            $event_email_template = EventEmailTemplate::create([
                'subject' => $request->subject,
                'body' => $request->body,
                'type' => $request->type,
                'event_id' => $event_id,
            ]);
        }
        return redirect()->back()->with('success', 'Email template updated successfully');
    }
}
