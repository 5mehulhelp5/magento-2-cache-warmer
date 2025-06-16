<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface for entity URL providers
 */
interface EntityUrlProviderInterface
{
    /**
     * Get the URLs for an entity
     *
     * @param int $entityId The ID of the entity
     * @param int $storeId The store ID
     * @return array The URLs of the entity
     * @throws NoSuchEntityException If the entity does not exist
     */
    public function getUrl(int $entityId, int $storeId): array;

    /**
     * Check if this provider supports the given entity type
     *
     * @param string $entityType The entity type
     * @return bool True if this provider supports the entity type, false otherwise
     */
    public function supports(string $entityType): bool;
}
