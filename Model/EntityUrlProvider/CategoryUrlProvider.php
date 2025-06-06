<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\EntityUrlProvider;

use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Blackbird\CacheWarmer\Api\EntityUrlProviderInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\App\Emulation;

/**
 * URL provider for category entities
 */
class CategoryUrlProvider implements EntityUrlProviderInterface
{
    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryFactory $categoryFactory
     * @param Emulation $storeEmulation
     */
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
        protected CategoryFactory             $categoryFactory,
        protected Emulation                   $storeEmulation
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getUrl(int $entityId, int $storeId): string
    {
        // Start store emulation
        $this->storeEmulation->startEnvironmentEmulation($storeId);

        try {
            // Get category URL
            $category = $this->categoryRepository->get($entityId, $storeId);
            $categoryUrl = $category->getUrl();

            return $categoryUrl;
        } finally {
            // Stop store emulation
            $this->storeEmulation->stopEnvironmentEmulation();
        }
    }

    /**
     * @inheritDoc
     */
    public function supports(string $entityType): bool
    {
        return $entityType === EntityQueueInterface::ENTITY_TYPE_CATEGORY;
    }
}
