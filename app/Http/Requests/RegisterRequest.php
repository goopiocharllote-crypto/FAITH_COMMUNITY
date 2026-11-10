<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize input before validation so common, harmless variations
     * (extra spaces, capital letters in the email, 09/+63 mobile prefixes)
     * are accepted instead of rejected.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name)
                ? preg_replace('/\s+/u', ' ', trim($this->name))
                : $this->name,
            'email' => is_string($this->email)
                ? mb_strtolower(trim($this->email))
                : $this->email,
            'mobile' => is_string($this->mobile)
                ? self::normalizeMobile($this->mobile)
                : $this->mobile,
        ]);
    }

    /**
     * Convert a Philippine mobile number to the stored 9XXXXXXXXX format.
     * Accepts 9171234567, 09171234567, 639171234567 and +63 917 123 4567.
     */
    public static function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/[\s\-().]/', '', trim($mobile));

        if (str_starts_with($digits, '+63')) {
            $digits = substr($digits, 3);
        } elseif (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\pL\pM\s.'-]+$/u"],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'mobile' => ['required', 'string', 'regex:/^9[0-9]{9}$/', Rule::unique('users', 'mobile')],
            'password' => [
                'required',
                'string',
                'max:72',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => __('Your name may only contain letters, spaces, periods, apostrophes and hyphens.'),
            'email.email' => __('Please enter a valid email address, like you@example.com.'),
            'email.unique' => __('An account with this email address already exists. Try signing in instead.'),
            'mobile.regex' => __('Please enter a valid Philippine mobile number, like 9171234567 or 09171234567.'),
            'mobile.unique' => __('This mobile number is already registered to another account.'),
            'password.confirmed' => __('The passwords do not match.'),
            'password.max' => __('Your password must not be longer than 72 characters.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('name'),
            'email' => __('email address'),
            'mobile' => __('mobile number'),
            'password' => __('password'),
        ];
    }
}
