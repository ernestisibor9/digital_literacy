<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CourseStoreRequest extends FormRequest
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
            //
            'instructor_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail){
                    $user = User::find($value);
                    if($user && $user->role !== 'instructor'){
                        $fail('The selected instructor is not a valid instructor');
                    }
                }
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:published,unpublished',
        ];
    }
}
