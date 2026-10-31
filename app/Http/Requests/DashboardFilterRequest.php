<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'bulan_dari' => ['nullable', 'integer', 'min:1', 'max:12'],
            'bulan_sampai' => ['nullable', 'integer', 'min:1', 'max:12'],
            'skema_id' => ['nullable', 'uuid'],
            'tuk_id' => ['nullable', 'uuid'],
        ];
    }
}
