<?php

namespace ArchCrudLaravel\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Contracts\ObjectValue;
use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;

class CustomExceptionMapping extends ObjectValue implements ArrayAccess, Countable, IteratorAggregate
{
    protected mixed $value;

    public function __construct(
        protected string $exceptionClass,
        protected Closure $handler
    )
    {
        parent::__construct([$exceptionClass => $handler]);
    }

    protected function validate(mixed $value): void
    {
        foreach ($value as $exceptionClass => $handler) {
            if (
                !class_exists($exceptionClass) ||
                !(
                    is_subclass_of($exceptionClass, Exception::class) ||
                    $exceptionClass === Exception::class ||
                    $exceptionClass instanceof Exception
                )
            ) {
                throw new InvalidArgumentException("A chave deve ser uma string representando o nome de uma classe de exceção válida.");
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->value[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new Exception('CustomExceptionMapping é somente leitura.');
    }

    public function offsetUnset($offset): void
    {
        throw new Exception('CustomExceptionMapping é somente leitura.');
    }

    public function count(): int
    {
        return count($this->value);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->value);
    }
    
    public function toArray(): array
    {
        $result = [];
        
        foreach ($this->value as $exceptionClass => $handler) {
            $result[$exceptionClass] = $handler;
        }
        
        return $result;
    }
}

