<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RemarkRecord;
use App\Models\NoteRecord;
use Illuminate\Support\Facades\Auth;

class RemarkController extends Controller
{
    // Display remark page
    public function index(Request $request)
    {
        $note_id = $request->input('note_id');

        if (!$note_id) {
            return redirect()
                ->route('manage-note.IndexNote')
                ->withErrors('Note ID is missing. Please return to the main page and try again.');
        }

        $note = NoteRecord::findOrFail($note_id);

        return view('manage-remark.IndexRemark', compact('note_id', 'note'));
    }

    // Fetch remarks data for specific note using AJAX request
    public function getremark(Request $request)
    {
        $noteId = $request->input('note_id'); // Get the note ID from the request
        $remarks = RemarkRecord::with('user')->where('note_id', $noteId)->get(); // Fetch all remarks related to the note
        return view('manage-remark.ListRemark', compact('remarks')); // Return the remarks view with the data
    }

    // Save remark and associate it with a specific note
    public function saveremark(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'description' => 'required|string|max:255',
                'note_id' => 'required|exists:note_record,id',
            ]);

            // Create a new Remark instance
            $remark = new RemarkRecord;
            $remark->description = $request->input('description');
            $remark->note_id = $request->input('note_id'); // Link remark to the note
            $remark->user_id = Auth::id(); // Link remark to currently logged-in user
            $remark->save(); // Save remark to the database

            return response($remark); // Return saved remark as a response
        }
    }

    // Update an existing remark
    public function updateremark(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'id' => 'required|exists:remark_record,id', 
                'description' => 'required|string|max:255', 
            ]);

            $remark = RemarkRecord::find($request->id);
            // Check if the authenticated user is the remark creator
            if ($remark->user_id !== Auth::id()) {
                return response()->json(['error' => 'You are not authorized to update this remark.'], 403);
            }

            $remark->description = $request->input('description');
            $remark->save(); 
            return response($remark); 
        }
    }

    // Delete a remark by ID
    public function deleteremark(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'id' => 'required|exists:remark_record,id', // Ensure the remark ID exists
            ]);

            // Find the remark by ID
            $remark = RemarkRecord::find($request->id);
            // Check if the authenticated user is the remark creator
            if ($remark->user_id !== Auth::id()) {
                return response()->json(['error' => 'You are not authorized to delete this remark.'], 403);
            }

            RemarkRecord::destroy($request->id);
            return response()->json(['success' => 'Remark deleted successfully']); 
        }
    }

    // Check permission to edit a remark
    public function checkEditRemark(Request $request)
    {
        $remark = RemarkRecord::findOrFail($request->remark_id);

        // Check if the authenticated user is the remark creator
        if ($remark->user_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have permission to edit this remark.'], 403);
        }
        return response()->json(['success' => 'Permission granted to edit the remark.']);
    }

    // Check permission to delete a remark
    public function checkDeleteRemark(Request $request)
    {
        $remark = RemarkRecord::findOrFail($request->remark_id);

        // Check if the authenticated user is the remark creator
        if ($remark->user_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have permission to delete this remark.'], 403);
        }
        return response()->json(['success' => 'Permission granted to delete the remark.']);
    }
}
