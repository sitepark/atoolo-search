<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command\Io;

use Atoolo\Search\Console\Command\Io\TypifiedInput;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

#[CoversClass(TypifiedInput::class)]
class TypifiedInputTest extends TestCase
{
    public function testGetIntOpt(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn(123);

        $input = new TypifiedInput($symfonyInput);

        $this->assertEquals(
            123,
            $input->getIntOption('a'),
            'unexpected option value'
        );
    }

    public function testGetIntOptWithInvalidValue(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn('abc');

        $input = new TypifiedInput($symfonyInput);

        $this->expectException(InvalidArgumentException::class);
        $input->getIntOption('a');
    }

    public function testGetStringArgument(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getArgument')
            ->willReturn('abc');

        $input = new TypifiedInput($symfonyInput);

        $this->assertEquals(
            'abc',
            $input->getStringArgument('a'),
            'unexpected argument value'
        );
    }

    public function testGetStringArgumentWithInvalidValue(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getArgument')
            ->willReturn(123);

        $input = new TypifiedInput($symfonyInput);

        $this->expectException(InvalidArgumentException::class);
        $input->getStringArgument('a');
    }

    public function testGetArrayArgument(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getArgument')
            ->willReturn(['a', 'b', 'c']);

        $input = new TypifiedInput($symfonyInput);
        $this->assertEquals(
            ['a', 'b', 'c'],
            $input->getArrayArgument('a'),
            'unexpected argument value'
        );
    }

    public function testGetArrayArgumentWithInvalidValue(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getArgument')
            ->willReturn('abc');

        $input = new TypifiedInput($symfonyInput);

        $this->expectException(InvalidArgumentException::class);
        $input->getArrayArgument('a');
    }
}
