<?php

namespace Blackbird\CacheWarmer\Model\Service;

use Blackbird\CacheWarmer\Api\UrlCollectorInterface;
use Blackbird\CacheWarmer\Api\UrlPoolCollectorInterface;
use Magento\Framework\Exception\InvalidArgumentException;

class UrlPoolCollector implements UrlPoolCollectorInterface
{
    /**
     * @param CollectorTypeService $collectorTypeService
     */
    public function __construct(
        protected CollectorTypeService $collectorTypeService
    ) {
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function collectUrls(array $stores, ?string $type = null): array
    {
        if (!empty($type)) {
            return $this->getUrlCollector($type)->collectUrls($stores);
        }

        $collectorTypes = $this->collectorTypeService->getCollectorTypes();

        $urls = [];
        foreach ($collectorTypes as $collectorType) {
            foreach ($collectorType['class']->collectUrls($stores) as $url) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    /**
     * @param string $type
     * @return UrlCollectorInterface
     * @throws InvalidArgumentException
     */
    protected function getUrlCollector(string $type): UrlCollectorInterface
    {
        $collector = $this->collectorTypeService->getCollectorClass($type);
        if (!$collector) {
            throw new InvalidArgumentException(__('The type "%1" is not supported.', $type));
        }
        return $collector;
    }
}
