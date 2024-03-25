<?php

declare(strict_types=1);

namespace Atoolo\Search\Exception;

use RuntimeException;

class UnexpectedResultException extends RuntimeException
{
    public function __construct(
        private readonly string $result,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message . ": " . $this->result,
            $code,
            $previous
        );
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
