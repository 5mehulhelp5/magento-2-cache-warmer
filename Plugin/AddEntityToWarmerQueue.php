<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Plugin;

use Blackbird\CacheWarmer\Api\EntityQueueRepositoryInterface;
use Blackbird\CacheWarmer\Logger\Logger;
use Blackbird\CacheWarmer\Model\Config;
use Magento\CacheInvalidate\Observer\InvalidateVarnishObserver;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;

class AddEntityToWarmerQueue
{
    /**
     * @param EntityQueueRepositoryInterface $entityQueueRepository
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        protected EntityQueueRepositoryInterface $entityQueueRepository,
        protected StoreManagerInterface $storeManager,
        protected Logger $logger,
        protected Config $config
    ) {
    }

    /**
     * @param InvalidateVarnishObserver $subject
     * @param void $result
     * @param Observer $observer
     * @return void
     */
    public function afterExecute(InvalidateVarnishObserver $subject, $result, Observer $observer): void
    {
        if (!$this->config->isAsyncCacheWarmerEnabled()) {
            return;
        }

        try {
            $info = $this->extractEntityInfoFromObject($observer->getEvent()->getData('object'));
            if (!empty($info)) {
                $this->entityQueueRepository->addToQueue(
                    (int)$info['entity_id'],
                    $info['entity_type'],
                    $info['store_ids']
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error adding entity to queue', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param mixed $object
     * @return array{entity_id: int, entity_type: String}
     */
    public function extractEntityInfoFromObject(mixed $object): array
    {
        if ($object instanceof ProductInterface) {
            return ['entity_id' => $object->getId(), 'entity_type' => \Blackbird\CacheWarmer\Api\Data\EntityQueueInterface::ENTITY_TYPE_PRODUCT, 'store_ids' => $object->getStoreIds() ?: $this->getDefaultStoreIds()];
        }
        if ($object instanceof CategoryInterface) {
            return ['entity_id' => $object->getId(), 'entity_type' => \Blackbird\CacheWarmer\Api\Data\EntityQueueInterface::ENTITY_TYPE_CATEGORY, 'store_ids' => $object->getStoreIds() ?? $this->getDefaultStoreIds()];
        }
        if ($object instanceof PageInterface) {
            $storeIds = $object->getStores();
            if ($storeIds === ["0"]) {
                $storeIds = $this->getDefaultStoreIds();
            }
            return ['entity_id' => $object->getId(), 'entity_type' => \Blackbird\CacheWarmer\Api\Data\EntityQueueInterface::ENTITY_TYPE_CMS_PAGE,  'store_ids' => $storeIds];
        }
        return [];
    }

    protected function getDefaultStoreIds(): array{
        return array_keys($this->storeManager->getStores());
    }
}
