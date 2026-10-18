<?php

namespace App\Http\Requests\HistoryRiil;

use Illuminate\Foundation\Http\FormRequest;

class IndexHistoryRiilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before validation is applied.
     */
    protected function prepareForValidation(): void
    {
        //
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'page' => [
                'required',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'tanggal_mulai' => [
                'required',
                'date',
                'before_or_equal:tanggal_selesai',
            ],
            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'kas' => [
                'sometimes',
                'array',
            ],
            'kas.*' => [
                'sometimes',
                'string',
                'in:kas pemuda,17 an',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * Used to add additional validation after the main rules.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            //
        });
    }

    /**
     * Handle a passed validation attempt.
     *
     * This method is called after validation is successful.
     */
    protected function passedValidation(): void
    {
        //
    }

    /**
     * Handle failed authorization.
     *
     * @return void
     */
    protected function failedAuthorization()
    {
        abort(403, 'Tidak memiliki izin untuk melakukan aksi ini');
    }
}
