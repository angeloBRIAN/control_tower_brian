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
        $user = $this->user();
        if (!$user) return false;

        if ($user->canEdit()) return true;

        // Allow Foreman to update if assigned to the job
        if ($user->hasRole('foreman')) {
            $job = $this->route('job');
            if (is_numeric($job)) {
                $job = \App\Models\Job::find($job);
            }
            
            if ($job) {
                // Check if user is linked to the foreman assigned to this job (supports multiple assignments, case-insensitive)
                $foremanNames = \App\Models\Foreman::where('user_id', $user->id)->pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
                if (!empty($foremanNames) && in_array(strtolower(trim($job->foreman)), $foremanNames)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // If user cannot edit (e.g. Foreman), they can ONLY update technician and need_part
        if (! $this->user()->canEdit()) {
            return [
                'technician' => 'nullable|string',
                'need_part' => 'nullable|boolean',
            ];
        }

        $jobId = $this->route('job')?->id ?? $this->route('job');
        
        return [
            'job_number' => 'required|string|unique:jobs,job_number,' . $jobId,
            'job_card' => 'nullable|string',
            'franchise' => 'required|in:PC,CV',
            'department' => 'nullable|string',
            'plate_number' => 'required|string',
            'chassis_number' => 'nullable|string',
            'type_unit' => 'nullable|string',
            'account_no' => 'nullable|string',
            'date_first_reg' => 'nullable|date',
            'customer_name' => 'nullable|string',
            'customer_address' => 'nullable|string',
            'service_advisor' => 'nullable|string',
            'technician' => 'nullable|string',
            'foreman' => 'nullable|string',
            'block' => 'nullable|string',
            'job_type' => 'nullable|string',
            'job_date' => 'nullable|date',
            'date_in' => 'nullable|date',
            'date_out' => 'nullable|date',
            'check_in_time' => 'nullable|string',
            'deadline' => 'nullable|date',
            'promise_date' => 'nullable|date',
            'job_description' => 'nullable|string',
            'work_status' => 'nullable|string',
            // Sales fields removed to prevent manual editing (imported from DMS)
            // 'labour_sales' => 'nullable|numeric',
            // 'part_sales' => 'nullable|numeric',
            // 'total_sales' => 'nullable|numeric',
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
