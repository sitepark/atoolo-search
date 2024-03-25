<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command\Io;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

/**
* This class can be used to obtain type-safe return values.
 * Necessary to pass the PHPStan checks.
 */
class TypifiedInput
{
    public function __construct(private readonly InputInterface $input)
    {
    }

    public function getIntOption(string $name): int
    {
        $value = $this->input->getOption($name);
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                'option ' . $name . ' must be a integer: ' . $value
            );
        }
        return (int)$value;
    }

    public function getStringArgument(string $name): string
    {
        $value = $this->input->getArgument($name);
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                'argument ' . $name . ' must be a string'
            );
        }
        return $value;
    }

    /**
     * @return string[]
     */
    public function getArrayArgument(string $name): array
    {
        $value = $this->input->getArgument($name);
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'argument ' . $name . ' must be a array'
            );
        }
        return $value;
    }
}
