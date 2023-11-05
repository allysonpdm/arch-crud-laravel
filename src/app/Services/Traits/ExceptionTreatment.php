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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

trait ExceptionTreatment
{
    /**
     * @var array<class-string, Closure>
     */
    protected array $customExceptionMappings = [];
    protected bool $shouldLog = false;

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

        if($this->shouldLog || app('config')->get('app.debug')){
            Log::error(
                message: $exception->getMessage(),
                context: [$exception]
            );
        }

        $exceptionMappings = [
            InvalidArgumentException::class => function ($exception) {
                return response(
                    content: [
                        'message' => $exception->getMessage()
                    ],
                    status: StatusCode::UNPROCESSABLE_ENTITY->value
                );
            },
            ValidationException::class => function ($exception) {
                return response(
                    content: [
                        'message' => $exception->validator->messages()
                    ],
                    status: StatusCode::UNPROCESSABLE_ENTITY->value
                );
            },
            ModelNotFoundException::class => function () {
                return response(
                    content: [
                        'message' => __('exceptions.error.no_results')
                    ],
                    status: StatusCode::NOT_FOUND->value
                );
            },
            CreateException::class => function ($exception) use ($code) {
                return response(
                    content: [
                        'message' => $exception->getMessage()
                    ],
                    status: $code
                );
            },
            BusinessException::class => function ($exception) use ($code) {
                return response(
                    content: [
                        'message' => $exception->getMessage()
                    ],
                    status: $code
                );
            },
            UpdateException::class => function ($exception) use ($code) {
                return response(
                    content: [
                        'message' => $exception->getMessage()
                    ],
                    status: $code
                );
            },
            SoftDeleteException::class => function ($exception) {
                return response(
                    content: [
                        'message' => $exception->getMessage()
                    ],
                    status: StatusCode::OK->value
                );
            },
            QueryException::class => function ($exception) {
                $message = match ($exception->getCode()) {
                    23000 => "Verifique se o relacionamento foi criado e garanta que o tipo esteja correto. SQLSTATE[{$exception->getCode()}]: ",
                    default => null
                };

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

        /**
         * @var array<class-string, Closure> $exceptionMappings
         */
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

    /**
     * @param array<class-string, Closure> $values
     * @return array<class-string, Closure>
     */
    protected function defaultExceptionMappings(array $values): array
    {
        $mappings = [];

        foreach ($values as $class => $handler) {
            $mappings[] = new CustomExceptionMapping($class, $handler);
        }

        return self::reduceMappings($mappings);
    }

    /**
     * @param array<int, CustomExceptionMapping> $mappings
     * @return array<class-string, Closure>
     */
    protected static function reduceMappings(array $mappings): array
    {
        return array_reduce(
            $mappings,
            fn ($result, $mapping) => array_merge($result, $mapping->toArray()),
            []
        );
    }

    /**
     * Mapea novas exceptions e o tratamento a ser aplicado respectivamente.
     * @param array<int, CustomExceptionMapping> $mappings
     * @return self
     */
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
}
