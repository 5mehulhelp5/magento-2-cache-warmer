<?php

namespace Blackbird\CacheWarmer\Model\Collector;

use Blackbird\CacheWarmer\Api\UrlCollectorInterface;
use Blackbird\CacheWarmer\Model\Service\CollectorTypeService;

class AllUrlCollector implements UrlCollectorInterface
{
    /**
     * @param CollectorTypeService $collectorTypeService
     */
    public function __construct(
        protected CollectorTypeService $collectorTypeService
    ) {
    }

    /**
     * Collect URLs from all other collectors and ensure they are unique
     *
     * @param array $stores
     * @return array
     */
    public function collectUrls(array $stores): array
    {
        $allUrls = [];
        $collectorTypes = $this->collectorTypeService->getCollectorTypes();

        // Collect URLs from all collector types except 'all'
        foreach ($collectorTypes as $code => $collectorType) {
            // Skip the 'all' collector to avoid infinite recursion
            if ($code === 'all') {
                continue;
            }

            $urls = $collectorType['class']->collectUrls($stores);
            foreach ($urls as $url) {
                $allUrls[] = $url;
            }
        }

        // Ensure URLs are unique by using array_unique
        $uniqueUrls = array_unique($allUrls);

        return $uniqueUrls;
    }
}