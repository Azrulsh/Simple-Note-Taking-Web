<?php

namespace App\Http\Controllers;

use App\Models\NoteRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use App\Http\Requests\EditNoteRequest;
use App\Http\Requests\CreateNoteRequest;

class NoteController extends Controller
{
    //Display all notes.  
    public function index(): View
    {
        //Implement BootstrapFive paginate for 5 notes per page
        $notes = NoteRecord::latest()->paginate(5);
        return view('manage-note.IndexNote', compact('notes'))
                    ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    //Display interface for creating new notes.
    public function create()
    {
        return view('manage-note.CreateNote');
    }

    //Store newly created note in DB
    public function store(CreateNoteRequest $request) : RedirectResponse
    {
        //Validating data from CreateNoteRequest
        NoteRecord::create($request->validated());   
        return redirect()->route('manage-note.IndexNote')
                         ->with('success', 'Note created successfully.');
    }
 
    //Display note based on id
    public function show(Request $request, $id)
    {
        //Find and display specific note from DB based on id
        $note = NoteRecord::find($id);
        return view('manage-note.ListAllNote',compact('note'));
    }

    //Display interface for editing existing note.
    public function edit(Request $request, $id)
    {
        //Find and edit specific note from DB based on id
        $note = NoteRecord::find($id);
        return view('manage-note.EditNote',compact('note'));
    }
    
    //Update existing note in DB
    public function update(EditNoteRequest $request, NoteRecord $note): RedirectResponse
    {
        //Validating data from EditNoteRequest
        $note->update($request->validated());
        $note->name = $request->name;
        $note->description = $request->description; 
        $note->save();
        return redirect()->route('manage-note.IndexNote')
                         ->with('success','Note edited successfully');
    }

    //Delete specific note in DB
    public function destroy(Request $request, $id)
    {
        //Find and delete specific note from DB based on id
        $note = NoteRecord::find($id);
        $note->delete();
        return redirect()->route('manage-note.IndexNote')
                         ->with('success','Note deleted successfully');
    }
}
