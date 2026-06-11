<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'texte_brut' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'texte_brut.required' => 'Le texte du reçu est obligatoire.',
            'texte_brut.min'      => 'Le texte doit contenir au moins 20 caractères.',
            'texte_brut.max'      => 'Le texte ne peut pas dépasser 5000 caractères.'
        ];
    }
}
