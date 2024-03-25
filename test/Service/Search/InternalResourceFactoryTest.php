<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Search;

use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Search\Service\Search\InternalResourceFactory;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Result\Document;

#[CoversClass(InternalResourceFactory::class)]
class InternalResourceFactoryTest extends TestCase
{
    private ResourceLoader|Stub $resourceLoader;
    private InternalResourceFactory $factory;

    protected function setUp(): void
    {
        $this->resourceLoader = $this->createStub(
            ResourceLoader::class
        );
        $this->factory = new InternalResourceFactory(
            $this->resourceLoader
        );
    }

    public function testAccept(): void
    {
        $document = $this->createDocument('/test.php');
        $this->assertTrue(
            $this->factory->accept($document),
            'should be accepted'
        );
    }

    public function testAcceptWithoutUrl(): void
    {
        $document = $this->createStub(Document::class);
        $this->assertFalse(
            $this->factory->accept($document),
            'should not be accepted'
        );
    }

    public function testAcceptWithWrongUrl(): void
    {
        $document = $this->createDocument('/test.txt');
        $this->assertFalse(
            $this->factory->accept($document),
            'should not be accepted'
        );
    }

    public function testCreate(): void
    {
        $document = $this->createDocument('/test.php');
        $resource = $this->createStub(Resource::class);
        $this->resourceLoader
            ->method('load')
            ->willReturn($resource);

        $this->assertEquals(
            $resource,
            $this->factory->create($document),
            'unexpected resource'
        );
    }

    public function testCreateWithoutUrl(): void
    {
        $document = $this->createStub(Document::class);
        $this->expectException(LogicException::class);
        $this->factory->create($document);
    }

    private function createDocument(string $url): Document
    {
        $document = $this->createStub(Document::class);
        $document
            ->method('getFields')
            ->willReturn([
                'url' => $url
            ]);
        return $document;
    }
}
