<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Api;

use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Interface for URL pool collectors
 *
 * This interface defines methods for collecting URLs from multiple collectors.
 */
interface UrlPoolCollectorInterface
{
    /**
     * Collect URLs from the specified stores using the specified collector type
     *
     * If no type is specified, URLs from all available collector types will be aggregated.
     *
     * @param StoreInterface[] $stores List of stores to collect URLs from
     * @param string|null $type Collector type to use (e.g., 'sitemap', 'product')
     * @return string[] List of collected URLs
     * @throws InvalidArgumentException If the specified collector type is not supported
     */
    public function collectUrls(array $stores, ?string $type = null): array;
}
