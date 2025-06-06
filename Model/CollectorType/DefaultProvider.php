<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\CollectorType;

use Blackbird\CacheWarmer\Api\CollectorTypeProviderInterface;
use Blackbird\CacheWarmer\Api\UrlCollectorInterface;
use Blackbird\CacheWarmer\Model\Collector\ProductUrlCollector;
use Blackbird\CacheWarmer\Model\Collector\SitemapUrlCollector;
use Magento\Framework\Phrase;

/**
 * Default provider for collector types
 */
class DefaultProvider implements CollectorTypeProviderInterface
{
    /**
     * @param SitemapUrlCollector $sitemapUrlCollector
     * @param ProductUrlCollector $productUrlCollector
     */
    public function __construct(
        protected SitemapUrlCollector $sitemapUrlCollector,
        protected ProductUrlCollector $productUrlCollector
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCollectorTypes(): array
    {
        return [
            'sitemap' => [
                'sort_order' => 10,
                'class' => $this->sitemapUrlCollector,
                'label' => __('Sitemap')
            ],
            'product' => [
                'sort_order' => 20,
                'class' => $this->productUrlCollector,
                'label' => __('Product')
            ]
        ];
    }
}
