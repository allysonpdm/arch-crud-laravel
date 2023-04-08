<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Exceptions\{
    BusinessException,
    CreateException,
    SoftDeleteException,
    UpdateException
};
use ArchCrudLaravel\App\ObjectValues\CustomExceptionMapping;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

trait ExceptionTreatment
{
    protected array $customExceptionMappings = [];

    use TransactionControl;

    protected function exceptionTreatment(Exception $exception): Response
    {
        $this->rollBack();

        return $this->getExceptionHandler($exception);
    }

    protected function getExceptionHandler(Exception $exception): Response
    {
        $code = (int) $exception->getCode();
        $code = empty($code)
            ? StatusCode::INTERNAL_SERVER_ERROR->value
            : $code;

        $exceptionMappings = [
            ValidationException::class => function ($exception) {
                return response(
                    content: $exception->validator->messages(),
                    status: StatusCode::UNPROCESSABLE_ENTITY->value
                );
            },
            ModelNotFoundException::class => function ($exception) {
                return response(
                    content: [
                        'Message' => __('exceptions.error.no_results')
                    ],
                    status: StatusCode::NOT_FOUND->value
                );
            },
            CreateException::class => function ($exception) use ($code) {
                return response(
                    content: $exception->getMessage(),
                    status: $code
                );
            },
            BusinessException::class => function ($exception) use ($code) {
                return response(
                    content: $exception->getMessage(),
                    status: $code
                );
            },
            UpdateException::class => function ($exception) use ($code) {
                return response(
                    content: $exception->getMessage(),
                    status: $code
                );
            },
            SoftDeleteException::class => function ($exception) {
                return response(
                    content: $exception->getMessage(),
                    status: StatusCode::OK->value
                );
            },
            QueryException::class => function ($exception) {
                switch ($exception->getCode()) {
                    case 23000:
                        $message = "Verifique se o relacionamento foi criado e garanta que o mesmo esteja correto. SQLSTATE[{$exception->getCode()}]: ";
                        break;
                    default:
                        $message = null;
                }
                return response(
                    content: [
                        'Exception' => get_class($exception),
                        'Message' => "{$message} {$exception->getMessage()}",
                        'File' => $exception->getFile(),
                        'Line' => $exception->getLine(),
                    ],
                    status: StatusCode::INTERNAL_SERVER_ERROR->value
                );
            }
        ];
        $exceptionMappings = [
            ...$this->defaultExceptionMappings($exceptionMappings),
            ...$this->customExceptionMappings,
        ];

        foreach ($exceptionMappings as $class => $handler) {
            if ($exception instanceof $class) {
                return $handler($exception);
            }
        }

        return response(
            content: [
                'Exception' => get_class($exception),
                'Message' => $exception->getMessage(),
                'File' => $exception->getFile(),
                'Line' => $exception->getLine(),
            ],
            status: $code
        );
    }

    public function setCustomExceptionMappings(array $mappings): self
    {
        foreach ($mappings as $key => $mapping) {
            if (!($mapping instanceof CustomExceptionMapping)) {
                throw new InvalidArgumentException("Cada item do array deve ser uma instância de CustomExceptionMapping.");
            }
        }
        $this->customExceptionMappings = self::reduceMappings($mappings);
        return $this;
    }

    protected function defaultExceptionMappings(array $values): array
    {
        $mappings = [];

        foreach ($values as $class => $handler) {
            $mappings[] = new CustomExceptionMapping($class, $handler);
        }

        return self::reduceMappings($mappings);
    }

    protected static function reduceMappings(array $mappings): array
    {
        return array_reduce(
            $mappings,
            function ($result, $mapping) {
                return array_merge($result, $mapping->toArray());
            },
            []
        );
    }
}
