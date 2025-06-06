<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Api;

/**
 * Interface for collector type providers
 */
interface CollectorTypeProviderInterface
{
    /**
     * Get collector types
     *
     * @return array<string, array{sort_order: int, class: UrlCollectorInterface, label: string}>
     */
    public function getCollectorTypes(): array;
}
