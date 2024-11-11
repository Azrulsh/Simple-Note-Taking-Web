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

    /**
     * Get the validation rules that apply for storing a note.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [ 'name' => 'required', 'description' => 'required'  ];
    }
}
