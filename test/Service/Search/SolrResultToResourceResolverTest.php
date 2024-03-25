<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Search;

use ArrayIterator;
use Atoolo\Resource\Resource;
use Atoolo\Search\Service\Search\ResourceFactory;
use Atoolo\Search\Service\Search\SolrResultToResourceResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Result\Document;
use Solarium\QueryType\Select\Result\Result as SelectResult;

#[CoversClass(SolrResultToResourceResolver::class)]
class SolrResultToResourceResolverTest extends TestCase
{
    public function testLoadResourceList(): void
    {
        $document = $this->createStub(Document::class);
        $result = $this->createStub(SelectResult::class);
        $result->method('getIterator')->willReturn(
            new ArrayIterator([$document])
        );

        $resource = $this->createStub(Resource::class);

        $resourceFactory = $this->createStub(ResourceFactory::class);
        $resourceFactory->method('accept')->willReturn(true);
        $resourceFactory->method('create')->willReturn($resource);

        $resolver = new SolrResultToResourceResolver([$resourceFactory]);

        $resourceList = $resolver->loadResourceList($result);

        $this->assertEquals(
            [$resource],
            $resourceList,
            'unexpected resourceList'
        );
    }

    public function testLoadResourceListWithoutAcceptedFactory(): void
    {
        $document = $this->createStub(Document::class);
        $document->method('getFields')->willReturn(['url' => 'test']);
        $result = $this->createStub(SelectResult::class);
        $result->method('getIterator')->willReturn(
            new ArrayIterator([$document])
        );

        $resourceFactory = $this->createStub(ResourceFactory::class);
        $resourceFactory->method('accept')->willReturn(false);

        $resolver = new SolrResultToResourceResolver([$resourceFactory]);

        $resourceList = $resolver->loadResourceList($result);

        $this->assertEmpty(
            $resourceList,
            'resourceList should be empty'
        );
    }

    public function testLoadResourceListWithoutAcceptedFactoryNoUrl(): void
    {
        $document = $this->createStub(Document::class);
        $result = $this->createStub(SelectResult::class);
        $result->method('getIterator')->willReturn(
            new ArrayIterator([$document])
        );

        $resourceFactory = $this->createStub(ResourceFactory::class);
        $resourceFactory->method('accept')->willReturn(false);

        $resolver = new SolrResultToResourceResolver([$resourceFactory]);

        $resourceList = $resolver->loadResourceList($result);

        $this->assertEmpty(
            $resourceList,
            'resourceList should be empty'
        );
    }
}
