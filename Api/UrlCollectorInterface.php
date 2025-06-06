<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Api;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Interface for URL collectors
 *
 * This interface defines methods for collecting URLs from different sources.
 */
interface UrlCollectorInterface
{
    /**
     * Collect URLs from the specified stores
     *
     * @param StoreInterface[] $stores List of stores to collect URLs from
     * @return string[] List of collected URLs
     */
    public function collectUrls(array $stores): array;
}
