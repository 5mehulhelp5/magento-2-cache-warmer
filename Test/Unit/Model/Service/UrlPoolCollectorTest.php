<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Test\Unit\Model\Service;

use Blackbird\CacheWarmer\Api\UrlCollectorInterface;
use Blackbird\CacheWarmer\Api\UrlPoolCollectorInterface;
use Blackbird\CacheWarmer\Model\Service\CollectorTypeService;
use Blackbird\CacheWarmer\Model\Service\UrlPoolCollector;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for UrlPoolCollector
 */
class UrlPoolCollectorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected ObjectManager $objectManager;

    /**
     * @var UrlPoolCollector|UrlPoolCollectorInterface
     */
    protected UrlPoolCollector $urlPoolCollector;

    /**
     * @var CollectorTypeService|MockObject
     */
    protected CollectorTypeService $collectorTypeService;

    /**
     * @var UrlCollectorInterface|MockObject
     */
    protected UrlCollectorInterface $urlCollector1;

    /**
     * @var UrlCollectorInterface|MockObject
     */
    protected UrlCollectorInterface $urlCollector2;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->collectorTypeService = $this->createMock(CollectorTypeService::class);
        $this->urlCollector1 = $this->createMock(UrlCollectorInterface::class);
        $this->urlCollector2 = $this->createMock(UrlCollectorInterface::class);

        $this->urlPoolCollector = $this->objectManager->getObject(
            UrlPoolCollector::class,
            [
                'collectorTypeService' => $this->collectorTypeService
            ]
        );
    }

    /**
     * Test collectUrls method with specific type
     */
    public function testCollectUrlsWithType(): void
    {
        // Create mock stores
        $store1 = $this->createMock(StoreInterface::class);
        $store2 = $this->createMock(StoreInterface::class);
        $stores = [$store1, $store2];

        // Configure mocks
        $this->collectorTypeService->expects($this->once())
            ->method('getCollectorClass')
            ->with('sitemap')
            ->willReturn($this->urlCollector1);

        $this->urlCollector1->expects($this->once())
            ->method('collectUrls')
            ->with($stores)
            ->willReturn(['https://example.com/page1', 'https://example.com/page2']);

        // Execute the method
        $result = $this->urlPoolCollector->collectUrls($stores, 'sitemap');

        // Verify the result
        $this->assertCount(2, $result);
        $this->assertEquals('https://example.com/page1', $result[0]);
        $this->assertEquals('https://example.com/page2', $result[1]);
    }

    /**
     * Test collectUrls method with invalid type
     */
    public function testCollectUrlsWithInvalidType(): void
    {
        // Create mock stores
        $store1 = $this->createMock(StoreInterface::class);
        $stores = [$store1];

        // Configure mocks
        $this->collectorTypeService->expects($this->once())
            ->method('getCollectorClass')
            ->with('invalid_type')
            ->willReturn(null);

        // Expect exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "invalid_type" is not supported.');

        // Execute the method
        $this->urlPoolCollector->collectUrls($stores, 'invalid_type');
    }

    /**
     * Test collectUrls method without type
     */
    public function testCollectUrlsWithoutType(): void
    {
        // Create mock stores
        $store1 = $this->createMock(StoreInterface::class);
        $stores = [$store1];

        // Configure mocks
        $collectorTypes = [
            'sitemap' => [
                'sort_order' => 10,
                'class' => $this->urlCollector1,
                'label' => 'Sitemap'
            ],
            'product' => [
                'sort_order' => 20,
                'class' => $this->urlCollector2,
                'label' => 'Product'
            ]
        ];

        $this->collectorTypeService->expects($this->once())
            ->method('getCollectorTypes')
            ->willReturn($collectorTypes);

        $this->urlCollector1->expects($this->once())
            ->method('collectUrls')
            ->with($stores)
            ->willReturn(['https://example.com/page1', 'https://example.com/page2']);

        $this->urlCollector2->expects($this->once())
            ->method('collectUrls')
            ->with($stores)
            ->willReturn(['https://example.com/product1', 'https://example.com/product2']);

        // Execute the method
        $result = $this->urlPoolCollector->collectUrls($stores);

        // Verify the result
        $this->assertCount(4, $result);
        $this->assertEquals('https://example.com/page1', $result[0]);
        $this->assertEquals('https://example.com/page2', $result[1]);
        $this->assertEquals('https://example.com/product1', $result[2]);
        $this->assertEquals('https://example.com/product2', $result[3]);
    }
}
