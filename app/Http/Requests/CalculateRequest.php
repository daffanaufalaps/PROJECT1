<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CalculateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'min:-8.8', 'max:-5.8'],
            'longitude' => ['required', 'numeric', 'min:105.0', 'max:114.6'],
            'site_class' => ['nullable', 'string', 'in:A,B,C,D,E'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Lintang (Latitude) wajib diisi.',
            'latitude.numeric' => 'Lintang harus berupa angka.',
            'latitude.min' => 'Lintang minimal -8.8 (wilayah Pulau Jawa).',
            'latitude.max' => 'Lintang maksimal -5.8 (wilayah Pulau Jawa).',
            'longitude.required' => 'Bujur (Longitude) wajib diisi.',
            'longitude.numeric' => 'Bujur harus berupa angka.',
            'longitude.min' => 'Bujur minimal 105.0 (wilayah Pulau Jawa).',
            'longitude.max' => 'Bujur maksimal 114.6 (wilayah Pulau Jawa).',
            'site_class.in' => 'Kelas situs harus salah satu dari: A, B, C, D, atau E.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}
