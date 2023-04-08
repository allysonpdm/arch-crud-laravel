<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Exceptions\{
    BusinessException,
    CreateException,
    SoftDeleteException
};
use ArchCrudLaravel\App\Exceptions\UpdateException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

trait ExceptionTreatment
{
    use TransactionControl;

    protected function exceptionTreatment($exception): Response
    {
        $this->rollBack();
        $type = get_class($exception);
        $code = (int) $exception->getCode();
        $code = empty($code) ? StatusCode::INTERNAL_SERVER_ERROR : $code;

        switch ($type) {
            case ValidationException::class:
                $response = response($exception->validator->messages(), StatusCode::UNPROCESSABLE_ENTITY); // HTTP error 422
                break;
            case ModelNotFoundException::class:
                $response = response([
                    'Message' => __('exceptions.error.no_results')
                ], 404);
                break;
            case CreateException::class:
            case BusinessException::class:
            case UpdateException::class:
                $response = response($exception->getMessage(), $code);
                break;
            case SoftDeleteException::class:
                $response = response($exception->getMessage(), StatusCode::OK);
                break;
            case QueryException::class:
                switch ($code){
                    case 23000:
                        $message = "Verifique se o relacionamento foi criado e garanta que o mesmo esteja correto. SQLSTATE[{$code}]: ";
                        break;
                    default:
                    $message = null;
                }
                $response = response([
                    'Exception' => $type,
                    'Message' => "{$message} {$exception->getMessage()}",
                    'File' => $exception->getFile(),
                    'Line' => $exception->getLine(),
                ], StatusCode::INTERNAL_SERVER_ERROR);
                break;
            default:
                $response = response([
                    'Exception' => $type,
                    'Message' => $exception->getMessage(),
                    'File' => $exception->getFile(),
                    'Line' => $exception->getLine(),
                ], $code);
        }
        return $response;
    }
}
