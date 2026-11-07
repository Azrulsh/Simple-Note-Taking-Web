<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    //Determine if the user is authorized to store not or not.
    public function authorize(): bool
    {
        return true;
    }

    //Get the validation rules that apply for storing a note.
    public function rules(): array
    {
        return [ 'name' => 'required|string|max:255', 'description' => 'required|string|max:255',];
    }
}
