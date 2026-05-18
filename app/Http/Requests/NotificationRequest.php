<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'request_id' => 'required|uuid',
            'channel' => ['required', Rule::in(['sms', 'email'])],
            'message' => 'required|string',
            'priority' => ['required', Rule::in(['high', 'low'])],
            'recipient_ids'   => 'required|array',
            'recipient_ids.*'      => 'required|int',
        ];
    }
}
