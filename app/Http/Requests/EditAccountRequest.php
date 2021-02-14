<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class EditAccountRequest extends FormRequest
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
        $user_id = Auth::id();
        $myEmail = User::find($user_id)->email;
        return [
            'name' => ['required', 'string', 'max:20'],
            'email' => [
                'required', 'string', 'email', 'max:50',
                Rule::unique('users', 'email')
                    ->whereNull('deleted_at') // 論理削除されていないレコードのみチェック
                    ->whereNot('email', $myEmail)
            ], //ユーザー自身のメールアドレスは重複チェックから除く
        ];
    }
}
