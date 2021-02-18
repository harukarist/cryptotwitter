<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {

        $status = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;

        if ($exception instanceof \Exception && $this->shouldReport($exception)) {
            if (\App::environment(['staging', 'production'])) {
                $status = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;

                if ($status >= 500) {
                    $error['message'] = $exception->getMessage();
                    $error['status']  = $status;
                    $error['code']    = $exception->getCode();
                    $error['file']    = $exception->getFile();
                    $error['line']    = $exception->getLine();
                    $error['url']     = url()->current();

                    Mail::send(['text' => 'emails.exception'], ["e" => $error], function (Message $message) {
                        $message
                            ->to(config('mail.to.address'))
                            ->from(config('mail.from.address'))
                            ->subject('【' . config('app.name') . '】[' . ENV('APP_ENV') . '] サーバーエラー発生の連絡');
                    });
                }
            }
        }


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
