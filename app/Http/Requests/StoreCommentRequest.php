<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    /** Komentar terbuka untuk tamu (tanpa akun) maupun pengguna terdaftar. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guest = $this->user() === null;

        return [
            'body' => ['required', 'string', 'min:3', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'name' => [Rule::requiredIf($guest), 'nullable', 'string', 'max:100'],
            'email' => [Rule::requiredIf($guest), 'nullable', 'email', 'max:255'],
        ];
    }
}
