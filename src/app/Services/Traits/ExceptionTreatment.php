<?php

namespace ArchCrudLaravel\App\Services\Traits;

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
        $code = empty($code) ? 500 : $code;

        switch ($type) {
            case ValidationException::class:
                $response = response($exception->validator->messages(), 422); // HTTP error 422
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
                $response = response($exception->getMessage(), 200);
                break;
            case QueryException::class:
                switch ($code){
                    case 23000:
                        $message = "Verifique se o relacionamento foi criado e garanta que o mesmo esteja correto. ";
                        break;
                    default:
                    $message = null;
                }
                $response = response([
                    'Exception' => $type,
                    'Message' => "{$message}SQL: {$exception->getMessage()}",
                    'File' => $exception->getFile(),
                    'Line' => $exception->getLine(),
                ], 500);
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
