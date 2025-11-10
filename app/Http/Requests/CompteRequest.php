<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Tri
            'sort'  => 'nullable|in:id,solde,created_at',
            'order' => 'nullable|in:asc,desc',

            // Pagination
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function sort(): string
    {
        return $this->get('sort', 'created_at');
    }

    public function order(): string
    {
        return $this->get('order', 'desc');
    }

    public function limit(): int
    {
        return min($this->get('limit', 10), 100);
    }
}