<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\CollectorType;

use Blackbird\CacheWarmer\Api\CollectorTypeProviderInterface;
use Blackbird\CacheWarmer\Model\Collector\AllUrlCollectorFactory;

/**
 * Provider for the 'all' collector type
 */
class AllProvider implements CollectorTypeProviderInterface
{
    /**
     * @param AllUrlCollectorFactory $allUrlCollector
     */
    public function __construct(
        protected AllUrlCollectorFactory $allUrlCollector
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCollectorTypes(): array
    {
        return [
            'all' => [
                'sort_order' => 10, // Higher than existing types
                'class' => $this->allUrlCollector->create(),
                'label' => __('All')
            ]
        ];
    }
}
