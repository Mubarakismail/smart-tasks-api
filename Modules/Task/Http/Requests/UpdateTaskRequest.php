<?php

namespace Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status_id' => ['sometimes', 'exists:statuses,id'],
            'assignee_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'priority' => ['sometimes', 'integer', 'between:1,5'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
