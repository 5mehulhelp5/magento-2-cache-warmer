<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\Service;

use Blackbird\CacheWarmer\Api\CollectorTypeProviderInterface;
use Blackbird\CacheWarmer\Api\UrlCollectorInterface;

/**
 * Service for managing collector types
 */
class CollectorTypeService
{
    /**
     * @var array<string, array{sort_order: int, class: UrlCollectorInterface, label: string}>|null
     */
    protected ?array $collectorTypes = null;

    /**
     * @param CollectorTypeProviderInterface[] $collectorTypeProviders
     */
    public function __construct(
        protected array $collectorTypeProviders = []
    ) {
    }

    /**
     * Get all collector types
     *
     * @return array<string, array{sort_order: int, class: UrlCollectorInterface, label: string}>
     */
    public function getCollectorTypes(): array
    {
        if ($this->collectorTypes === null) {
            $this->collectorTypes = [];

            foreach ($this->collectorTypeProviders as $provider) {
                $this->collectorTypes = array_merge($this->collectorTypes, $provider->getCollectorTypes());
            }

            // Sort by sort_order
            uasort($this->collectorTypes, function ($a, $b) {
                return $a['sort_order'] <=> $b['sort_order'];
            });
        }

        return $this->collectorTypes;
    }

    /**
     * Get collector type by code
     *
     * @param string $code
     * @return array{sort_order: int, class: UrlCollectorInterface, label: string}|null
     */
    public function getCollectorType(string $code): ?array
    {
        $types = $this->getCollectorTypes();
        return $types[$code] ?? null;
    }

    /**
     * Get collector class by code
     *
     * @param string $code
     * @return UrlCollectorInterface|null
     */
    public function getCollectorClass(string $code): ?UrlCollectorInterface
    {
        $type = $this->getCollectorType($code);
        return $type ? $type['class'] : null;
    }

    /**
     * Get option array for admin configuration
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => '', 'label' => __('-- Please Select --')]
        ];

        foreach ($this->getCollectorTypes() as $code => $type) {
            $options[] = [
                'value' => $code,
                'label' => $type['label']
            ];
        }

        return $options;
    }
}
