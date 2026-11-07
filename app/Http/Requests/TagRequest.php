<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class TagRequest extends FormRequest
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
        $tag = $this->route('tag');
        $tagId = $tag instanceof \App\Models\Tag ? $tag->id : $tag;
        return [
            'name' => 'required|string|max:50|unique:tags,name,' . $tagId
        ];
    }

    #[Override]
    public function messages():array
    {
        return [
            'name.required' => 'タグ名を入力してください',
            'name.max:50' => 'タグ名は50文字以内で入力してください',
            'name.unique' => 'そのタグ名はすでに使用されています',
        ];
    }
}
