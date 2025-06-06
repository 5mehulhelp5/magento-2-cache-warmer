<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\Config\Source;

use Blackbird\CacheWarmer\Model\Service\CollectorTypeService;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for collector types
 */
class CollectorType implements OptionSourceInterface
{
    /**
     * @param CollectorTypeService $collectorTypeService
     */
    public function __construct(
        protected CollectorTypeService $collectorTypeService
    ) {
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return $this->collectorTypeService->toOptionArray();
    }
}
