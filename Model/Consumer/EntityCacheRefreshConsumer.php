<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\Consumer;

use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Blackbird\CacheWarmer\Api\EntityQueueRepositoryInterface;

use Blackbird\CacheWarmer\Logger\Logger;
use Blackbird\CacheWarmer\Model\Config;
use Blackbird\CacheWarmer\Model\EntityUrlProvider\CompositeEntityUrlProvider;
use Blackbird\CacheWarmer\Model\Service\WarmerFactory;
use Blackbird\CacheWarmer\Model\Warmer\WarmerOptionsFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Consumer for processing entity cache refresh queue
 */
class EntityCacheRefreshConsumer
{
    /**
     * @param EntityQueueRepositoryInterface $entityQueueRepository
     * @param CompositeEntityUrlProvider $entityUrlProvider
     * @param WarmerFactory $warmerFactory
     * @param WarmerOptionsFactory $warmerOptionsFactory
     * @param Emulation $storeEmulation
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param Logger $logger
     * @param int $batchSize
     */
    public function __construct(
        protected EntityQueueRepositoryInterface $entityQueueRepository,
        protected CompositeEntityUrlProvider $entityUrlProvider,
        protected WarmerFactory $warmerFactory,
        protected WarmerOptionsFactory $warmerOptionsFactory,
        protected Emulation $storeEmulation,
        protected StoreManagerInterface $storeManager,
        protected Config $config,
        protected Logger $logger,
        protected int $batchSize = 500
    ) {
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): void
    {
        $pendingQueues = $this->entityQueueRepository->getPendingQueues($this->batchSize);

        if (empty($pendingQueues)) {
            return;
        }

        $this->processBatch($pendingQueues);
    }

    /**
     * Process a batch of queue items
     *
     * @param EntityQueueInterface[] $queueItems
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function processBatch(array $queueItems): void
    {
        if (empty($queueItems)) {
            return;
        }

        $urlsByStore = [];
        $errorItems = [];

        // First pass: mark all items as processing and collect URLs
        foreach ($queueItems as $itemId => $queueItem) {
            try {
                // Update status to processing
                $queueItem->setStatus(EntityQueueInterface::STATUS_PROCESSING);
                $this->entityQueueRepository->save($queueItem);

                $targetEntityId = $queueItem->getTargetEntityId();
                $entityType = $queueItem->getEntityType();
                $storeId = $queueItem->getStoreId();

                try {
                    // Check if we have a provider for this entity type
                    if (!$this->entityUrlProvider->supports($entityType)) {
                        throw new NoSuchEntityException(__('No URL provider found for entity type "%1"', $entityType));
                    }

                    // Get entity URLs
                    $entityUrls = $this->entityUrlProvider->getUrlForType($targetEntityId, $entityType, $storeId);

                    // Add URLs to the batch grouped by store
                    if (!isset($urlsByStore[$storeId])) {
                        $urlsByStore[$storeId] = [];
                    }

                    foreach ($entityUrls as $url) {
                        $urlsByStore[$storeId][$itemId][] = $url;
                    }


                    $this->logger->info('Added URLs to batch for warming', [
                        'entity_id' => $targetEntityId,
                        'entity_type' => $entityType,
                        'store_id' => $storeId,
                        'urls' => $entityUrls,
                        'count' => count($entityUrls)
                    ]);

                    if(empty($entityUrls)){
                        $queueItem->setStatus(EntityQueueInterface::STATUS_COMPLETE);
                        $this->entityQueueRepository->save($queueItem);
                    }
                } catch (NoSuchEntityException $e) {
                    $this->logger->error('Entity not found', [
                        'entity_id' => $targetEntityId,
                        'entity_type' => $entityType,
                        'store_id' => $storeId,
                        'error' => $e->getMessage()
                    ]);

                    $errorItems[] = [
                        'item' => $queueItem,
                        'error' => $e->getMessage()
                    ];
                } catch (\Throwable $e) {
                    $this->logger->error('Error getting URL for entity', [
                        'entity_id' => $targetEntityId,
                        'entity_type' => $entityType,
                        'store_id' => $storeId,
                        'error' => $e->getMessage()
                    ]);

                    $errorItems[] = [
                        'item' => $queueItem,
                        'error' => $e->getMessage()
                    ];
                }
            } catch (\Throwable $e) {
                $this->logger->error('Error processing queue item', [
                    'queue_id' => $queueItem->getEntityId(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Process error items
        foreach ($errorItems as $errorData) {
            try {
                $errorData['item']->setStatus(EntityQueueInterface::STATUS_ERROR);
                $this->entityQueueRepository->save($errorData['item']);
            } catch (\Throwable $e) {
                $this->logger->error('Error updating queue item status', [
                    'queue_id' => $errorData['item']->getEntityId(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Process URLs by store
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getId();
            $urls = $urlsByStore[$storeId] ?? [];
            if (empty($urls)) {
                continue;
            }

            $this->logger->info('Warming batch of URLs for store', [
                'store_id' => $storeId,
                'count' => count($urls)
            ]);

            // Instantiate new warmer for this store
            $warmer = $this->warmerFactory->create([
                'options' => $this->warmerOptionsFactory->create([
                    'store' => $store
                ])
            ]);

            // Flatten the array of URL arrays
            $flattenedUrls = [];
            $urlMapping = []; // Map flattened index back to original item ID and URL index

            foreach ($urls as $itemId => $itemUrls) {
                foreach ($itemUrls as $urlIndex => $url) {
                    $flattenedUrls[] = $url;
                    $urlMapping[] = [
                        'item_id' => $itemId,
                        'url_index' => $urlIndex
                    ];
                }
            }

            $results = $warmer->warmUrls($flattenedUrls);
            $urlsKeys = array_keys($urls);

            $statuses = [];
            foreach ($results as $pool => $result) {
                foreach ($urlsKeys as $index => $itemId) {
                    if (!isset($statuses[$itemId]) || $result['statuses'][$index] < 500) {
                        $statuses[$itemId] = $result['statuses'][$index];
                    }
                }
            }

            // Mark items as complete
            foreach ($statuses as $itemId => $code) {
                try {
                    $status = $code < 500 ? EntityQueueInterface::STATUS_COMPLETE : EntityQueueInterface::STATUS_ERROR;
                    $queueItems[$itemId]->setStatus($status);
                    $this->entityQueueRepository->save($queueItems[$itemId]);
                    if ($status == EntityQueueInterface::STATUS_ERROR) {
                        $this->logger->error('Error during warming of url', [
                            'queue_id' => $queueItems[$itemId]->getEntityId(),
                            'url' => $urls[$itemId],
                            'code' => $code
                        ]);
                    }
                } catch (\Throwable $e) {
                    $this->logger->error('Error updating queue item status', [
                        'queue_id' => $queueItems[$itemId]->getEntityId(),
                        'url' => $urls[$itemId],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
}
