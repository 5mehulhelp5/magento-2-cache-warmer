<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\EntityUrlProvider;

use Blackbird\CacheWarmer\Api\EntityUrlProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Composite URL provider that delegates to the appropriate provider based on entity type
 */
class CompositeEntityUrlProvider implements EntityUrlProviderInterface
{
    /**
     * @var EntityUrlProviderInterface[]
     */
    protected array $providers;

    /**
     * @param array $providers
     */
    public function __construct(
        array $providers = []
    ) {
        $this->providers = $providers;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(int $entityId, int $storeId): string
    {
        throw new NoSuchEntityException(__('Entity type must be specified when using the composite provider'));
    }

    /**
     * Get the URL for an entity with a specific type
     *
     * @param int $entityId The ID of the entity
     * @param string $entityType The entity type
     * @param int $storeId The store ID
     * @return string The URL of the entity
     * @throws NoSuchEntityException If no provider supports the entity type or the entity does not exist
     */
    public function getUrlForType(int $entityId, string $entityType, int $storeId): string
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($entityType)) {
                return $provider->getUrl($entityId, $storeId);
            }
        }

        throw new NoSuchEntityException(__('No provider found for entity type "%1"', $entityType));
    }

    /**
     * @inheritDoc
     */
    public function supports(string $entityType): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($entityType)) {
                return true;
            }
        }

        return false;
    }
}
