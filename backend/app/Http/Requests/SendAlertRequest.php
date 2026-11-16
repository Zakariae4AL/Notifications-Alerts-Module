<?php

namespace App\Http\Requests;

use App\Enums\NotificationType;
use App\Rules\ScalarValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('send-alerts') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'string', Rule::enum(NotificationType::class)],
            'user_ids' => ['required', 'array', 'min:1', 'max:500'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:500'],
            'data' => ['nullable', 'array'],
            'data.*' => ['nullable', new ScalarValue],
        ];

        // Each type declares the payload keys it needs to build its message.
        $type = NotificationType::tryFrom((string) $this->input('type'));

        foreach ($type?->requiredData() ?? [] as $key) {
            $rules["data.{$key}"] = ['required_without:message', 'nullable', new ScalarValue, 'max:255'];
        }

        return $rules;
    }

    public function notificationType(): NotificationType
    {
        return NotificationType::from($this->validated('type'));
    }
}
