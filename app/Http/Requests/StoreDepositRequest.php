<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepositRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'proof'  => ['required', 'image', 'max:2048'], // screenshot
            'note'   => ['nullable', 'string'],
        ];
    }
}