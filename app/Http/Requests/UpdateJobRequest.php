<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canEdit() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $jobId = $this->route('job')?->id ?? $this->route('job');
        
        return [
            'job_number' => 'required|string|unique:jobs,job_number,' . $jobId,
            'job_card' => 'nullable|string',
            'franchise' => 'required|in:PC,CV',
            'plate_number' => 'required|string',
            'unit' => 'nullable|string',
            'type_unit' => 'nullable|string',
            'account_no' => 'nullable|string',
            'date_first_reg' => 'nullable|date',
            'customer_name' => 'nullable|string',
            'customer_address' => 'nullable|string',
            'service_advisor' => 'nullable|string',
            'technician' => 'nullable|string',
            'foreman' => 'nullable|string',
            'job_date' => 'nullable|date',
            'labour_sales' => 'nullable|numeric',
            'part_sales' => 'nullable|numeric',
            'total_sales' => 'nullable|numeric',
            'rq' => 'nullable|string',
            'no_order_part_mbina' => 'nullable|string',
            'lain_lain' => 'nullable|string',
            'need_part' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'job_number.required' => 'Job/WIP number is required.',
            'job_number.unique' => 'This job number already exists.',
            'franchise.required' => 'Franchise (PC/CV) is required.',
            'plate_number.required' => 'Plate number is required.',
        ];
    }
}
