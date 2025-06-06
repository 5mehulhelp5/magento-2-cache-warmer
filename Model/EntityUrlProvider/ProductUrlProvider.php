<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\EntityUrlProvider;

use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Blackbird\CacheWarmer\Api\EntityUrlProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\App\Emulation;

/**
 * URL provider for product entities
 */
class ProductUrlProvider implements EntityUrlProviderInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductHelper $productHelper
     * @param Emulation $storeEmulation
     */
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected ProductHelper $productHelper,
        protected Emulation $storeEmulation
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
            // Get product URL
            $product = $this->productRepository->getById($entityId, false, $storeId);
            $productUrl = $this->productHelper->getProductUrl($product);

            return $productUrl;
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
        return $entityType === EntityQueueInterface::ENTITY_TYPE_PRODUCT;
    }
}
