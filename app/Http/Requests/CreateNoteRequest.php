<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateNoteRequest extends FormRequest
{
    //Determine if the user is authorized to create note or not.
    public function authorize(): bool
    {
        return true;
    }

    // Get the validation rules that apply for creating a note.
    public function rules(): array
    {
        return [ 'name' => 'required', 'description' => 'required' ];
    }
}
