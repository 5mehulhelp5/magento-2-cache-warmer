<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model;

use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Blackbird\CacheWarmer\Api\Data\EntityQueueInterfaceFactory;
use Blackbird\CacheWarmer\Api\EntityQueueRepositoryInterface;
use Blackbird\CacheWarmer\Model\ResourceModel\EntityQueue as EntityQueueResource;
use Blackbird\CacheWarmer\Model\ResourceModel\EntityQueue\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Entity Queue Repository
 */
class EntityQueueRepository implements EntityQueueRepositoryInterface
{
    /**
     * @param EntityQueueResource $resource
     * @param EntityQueueInterfaceFactory $entityQueueFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        protected EntityQueueResource $resource,
        protected EntityQueueInterfaceFactory $entityQueueFactory,
        protected CollectionFactory $collectionFactory,
        protected SearchResultsInterfaceFactory $searchResultsFactory,
        protected CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(EntityQueueInterface $entityQueue): EntityQueueInterface
    {
        try {
            $this->resource->save($entityQueue);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the entity queue: %1',
                $exception->getMessage()
            ));
        }
        return $entityQueue;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $entityId): EntityQueueInterface
    {
        $entityQueue = $this->entityQueueFactory->create();
        $this->resource->load($entityQueue, $entityId);
        if (!$entityQueue->getEntityId()) {
            throw new NoSuchEntityException(__('Entity queue with id "%1" does not exist.', $entityId));
        }
        return $entityQueue;
    }

    /**
     * @inheritDoc
     */
    public function delete(EntityQueueInterface $entityQueue): bool
    {
        try {
            $this->resource->delete($entityQueue);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the entity queue: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function getPendingQueues(int $limit = 100): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', EntityQueueInterface::STATUS_PENDING);
        $collection->setPageSize($limit);
        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function addToQueue(int $targetEntityId, string $entityType, array $storeIds): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('target_entity_id', $targetEntityId);
        $collection->addFieldToFilter('entity_type', $entityType);
        $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
        $collection->addFieldToFilter('status', ['in' => EntityQueueInterface::STATUS_PENDING]);

        // If there's already a pending queue item for this entity and store, return it
        $items = $collection->getItems();
        if ($collection->getSize() === count($storeIds)) {
            return $items;
        }

        $missingStoreIds = array_diff($storeIds, $collection->getColumnValues('store_id'));

        foreach ($missingStoreIds as $storeId) {
            if ($storeId == 0) {
                continue;
            }
            $entityQueue = $this->entityQueueFactory->create();
            $entityQueue->setTargetEntityId($targetEntityId);
            $entityQueue->setEntityType($entityType);
            $entityQueue->setStoreId((int)$storeId);
            $entityQueue->setStatus(EntityQueueInterface::STATUS_PENDING);
            $item = $this->save($entityQueue);
            $items[$item->getEntityId()] = $item;
        }
        return $items;
    }
}
