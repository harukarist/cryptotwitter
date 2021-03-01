<?php

namespace App\Exceptions;

use Exception;
use App\Mail\ExceptionMail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * 例外が投げられた時の処理を定義する例外ハンドラクラス
 * サーバーエラー発生時に、管理者メールアドレス宛に
 * サーバーエラー発生メールを送信する処理をreportメソッドで指定する。
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // メール通知対象外とする例外エラーを指定
        \Illuminate\Auth\AuthenticationException::class, //認証エラー
        \Illuminate\Auth\Access\AuthorizationException::class, //アクセス権限エラー
        \Illuminate\Validation\ValidationException::class, //バリデーションエラー
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * 例外のレポート処理を実行するメソッド
     * ステータスコードが500以上のサーバーエラーが発生した場合はメールで通知する
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        // 例外がHttpExceptionの場合はステータスコードを取得
        $status = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;

        if ($exception instanceof \Exception && $this->shouldReport($exception)) {
            // 商用環境の場合
            if (\App::environment(['production'])) {
                // ステータスコードが500以上の場合はエラー内容を配列に格納して
                // mail/exception.blade.php のメールテンプレートを使ってメールを作成、送信する
                $status = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;

                if ($status >= 500) {
                    $error['message'] = $exception->getMessage();
                    $error['status']  = $status;
                    $error['code']    = $exception->getCode();
                    $error['file']    = $exception->getFile();
                    $error['line']    = $exception->getLine();
                    $error['url']     = url()->current();

                    // Mailファサードでメールを送信
                    // （app/Mail のMailableクラスを使用する）
                    Mail::to(config('mail.from.address'))
                        ->send(new ExceptionMail($error));
                }
            }
        }

        // その他は親クラスの設定を継承
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
