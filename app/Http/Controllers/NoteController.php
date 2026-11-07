<?php

namespace App\Http\Controllers;

use App\Models\NoteRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\EditNoteRequest;
use App\Http\Requests\CreateNoteRequest;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    // Display notes with creator's name
    public function index(): View
    {
        // Eager load 'user' relationship and pagination style
        $notes = NoteRecord::with('user')->latest()->paginate(5);   
        return view('manage-note.IndexNote', compact('notes'))
                    ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    // Display interface for creating new notes.
    public function create()
    {
        return view('manage-note.CreateNote');
    }

    // Store created note in DB
    public function store(CreateNoteRequest $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to create a note.');
        }

        // Create the note with the logged-in user's ID
        NoteRecord::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(),  
        ]);
        return redirect()->route('manage-note.IndexNote')
                         ->with('success', 'Note created successfully.');
    }

    // Display note based on id
    public function show(Request $request, $id)
    {
        $note = NoteRecord::with('user')->findOrFail($id);
        return view('manage-note.ListAllNote', compact('note'));
    }

    // Display interface for editing existing note.
    public function edit(Request $request, $id)
    {
        $note = NoteRecord::findOrFail($id);
        if ($note->user_id !== Auth::id()) {
            return redirect()->route('manage-note.IndexNote')->with('error', 'You are not authorized to edit this note.');
        }
        return view('manage-note.EditNote', compact('note'));
    }
    
    // Update existing note in DB
    public function update(EditNoteRequest $request, NoteRecord $note): RedirectResponse
    {
        // Check if the logged-in user is the owner of the note
        if ($note->user_id !== Auth::id()) {
            return redirect()
                ->route('manage-note.IndexNote')
                ->with('error', 'You are not authorized to update this note.');
        }

        // Update the note using validated data
        $note->update($request->validated());

        return redirect()
            ->route('manage-note.IndexNote')
            ->with('success', 'Note edited successfully.');
    }

    // Delete specific note in DB
    public function destroy($id)
    {
        $note = NoteRecord::findOrFail($id);
        if ($note->user_id !== Auth::id()) {
            return redirect()->route('manage-note.IndexNote')->with('error', 'You are not authorized to delete this note.');
        }
        
        // Deleting note 
        $note->delete();        
        return redirect()->route('manage-note.IndexNote')
                         ->with('success', 'Note deleted successfully.');
    }
}
