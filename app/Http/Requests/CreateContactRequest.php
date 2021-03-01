<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * お問い合わせフォームに入力された内容の
 * バリデーションチェックを行うフォームリクエストクラス
 */
class CreateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * ユーザーがこの要求を行うことを許可するかどうか
     * @return bool
     */
    public function authorize()
    {
        return true; //要求を許可
    }

    /**
     * Get the validation rules that apply to the request.
     * リクエストに適用するバリデーションルールを指定
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:30',
            'email' => 'required|email',
            'message'  => 'required|max:1000',
        ];
    }
}
