<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Search;

use Atoolo\Resource\ResourceLanguage;
use Atoolo\Search\Service\Search\ExternalResourceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Result\Document;

#[CoversClass(ExternalResourceFactory::class)]
class ExternalResourceFactoryTest extends TestCase
{
    private ExternalResourceFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ExternalResourceFactory();
    }

    public function testAcceptHttps(): void
    {
        $document = $this->createDocument('https://www.sitepark.com');
        $this->assertTrue(
            $this->factory->accept($document, ResourceLanguage::default()),
            'should be accepted',
        );
    }

    public function testAcceptHttp(): void
    {
        $document = $this->createDocument('http://www.sitepark.com');
        $this->assertTrue(
            $this->factory->accept($document, ResourceLanguage::default()),
            'should be accepted',
        );
    }

    public function testAcceptWithoutUrl(): void
    {
        $document = $this->createStub(Document::class);
        $this->assertFalse(
            $this->factory->accept($document, ResourceLanguage::default()),
            'should be accepted',
        );
    }

    public function testCreate(): void
    {
        $document = $this->createDocument('https://www.sitepark.com');
        $resource = $this->factory->create(
            $document,
            ResourceLanguage::of('en'),
        );

        $this->assertEquals(
            'https://www.sitepark.com',
            $resource->location,
            'unexpected location',
        );
    }

    public function testCreateWithName(): void
    {
        $document = $this->createDocument('https://www.sitepark.com', 'Test');
        $resource = $this->factory->create(
            $document,
            ResourceLanguage::of('en'),
        );

        $this->assertEquals(
            'Test',
            $resource->name,
            'unexpected name',
        );
    }

    public function testCreateWithMissingUrl(): void
    {
        $document = $this->createStub(Document::class);

        $this->expectException(\LogicException::class);
        $this->factory->create($document, ResourceLanguage::of('en'));
    }

    private function createDocument(string $url, string $title = ''): Document
    {
        $document = $this->createStub(Document::class);
        $document
            ->method('getFields')
            ->willReturn([
                'url' => $url,
                'title' => $title,
                'description' => ['test'],
            ]);
        return $document;
    }
}
