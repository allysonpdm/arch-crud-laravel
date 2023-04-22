<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\BusinessException;
use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\ObjectValues\CustomExceptionMapping;
use ArchCrudLaravel\App\Services\Traits\ExceptionTreatment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

class ExceptionTreatmentTest extends TestCase
{
    use ExceptionTreatment;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testValidationException()
    {
        $exception = ValidationException::withMessages(['email' => 'The email field is required.']);
        $response = $this->exceptionTreatment($exception);

        $this->assertEquals(StatusCode::UNPROCESSABLE_ENTITY->value, $response->getStatusCode());
        $this->assertArrayHasKey('email', $response->getData(true));
    }

    public function testModelNotFoundException()
    {
        $exception = new ModelNotFoundException();
        $response = $this->exceptionTreatment($exception);

        $this->assertEquals(StatusCode::NOT_FOUND->value, $response->getStatusCode());
        $this->assertArrayHasKey('Message', $response->getData(true));
    }

    public function testBusinessException()
    {
        $exception = new BusinessException('A business error occurred.', StatusCode::BAD_REQUEST->value);
        $response = $this->exceptionTreatment($exception);

        $this->assertEquals(StatusCode::BAD_REQUEST->value, $response->getStatusCode());
        $this->assertStringContainsString('A business error occurred.', $response->getContent());
    }

    public function testQueryException()
    {
        $exception = new QueryException('A query error occurred.', 23000, null);
        $response = $this->exceptionTreatment($exception);

        $this->assertEquals(StatusCode::INTERNAL_SERVER_ERROR->value, $response->getStatusCode());
        $this->assertArrayHasKey('Exception', $response->getData(true));
        $this->assertArrayHasKey('Message', $response->getData(true));
    }

    public function testCustomExceptionMappings()
    {
        $customExceptionMapping = new CustomExceptionMapping(
            BusinessException::class,
            function ($exception) {
                return response(
                    content: ['CustomMessage' => 'A custom business exception occurred.'],
                    status: StatusCode::BAD_REQUEST->value
                );
            }
        );

        $this->setCustomExceptionMappings([$customExceptionMapping]);

        $exception = new BusinessException('A business error occurred.', StatusCode::BAD_REQUEST->value);
        $response = $this->exceptionTreatment($exception);

        $this->assertEquals(StatusCode::BAD_REQUEST->value, $response->getStatusCode());
        $this->assertArrayHasKey('CustomMessage', $response->getData(true));
    }

    public function testInvalidCustomExceptionMapping()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->setCustomExceptionMappings([null]);
    }
}
