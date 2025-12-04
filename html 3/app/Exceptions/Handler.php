<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ForbiddenHttpException::class,
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e): Response
    {
        if (is_a($e, ForbiddenHttpException::class)) {
            return response()->json(
                [
                    'error' => true,
                    'message' => 'Запрещено текущему пользователю'
                ],
                Response::HTTP_FORBIDDEN
            );
        }
        if (is_a($e, ValidationException::class)) {
            return response()->json(
                [
                    'error' => true,
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if (is_a($e, \DomainException::class)) {
            return response()->json(
                [
                    'error' => true,
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        Log::error($e->getMessage(), $e->getTrace());

        return parent::render($request, $e);
    }
}
