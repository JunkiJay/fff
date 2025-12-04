<?php

namespace App\Exceptions;

final class SyntaxError extends \JsonException
{
    public function __construct(
        string $message,
        private int $lineNumber,
        private int $column,
        \Throwable|null $previous = null
    ) {
        $message = \sprintf('%s at line %d column %d of the JSON5 data', $message, $lineNumber, $column);

        parent::__construct($message, 0, $previous);
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function getColumn(): int
    {
        return $this->column;
    }
}