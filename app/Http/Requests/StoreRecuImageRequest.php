<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecuImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Une image est requise.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être en format JPG, JPEG ou PNG.',
            'image.max' => 'L\'image ne doit pas dépasser 5 MB.',
        ];
    }
}
