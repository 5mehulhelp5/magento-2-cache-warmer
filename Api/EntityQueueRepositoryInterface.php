<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Api;

use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Entity Queue Repository Interface
 */
interface EntityQueueRepositoryInterface
{
    /**
     * Save entity queue
     *
     * @param EntityQueueInterface $entityQueue
     * @return EntityQueueInterface
     * @throws CouldNotSaveException
     */
    public function save(EntityQueueInterface $entityQueue): EntityQueueInterface;

    /**
     * Get entity queue by ID
     *
     * @param int $entityId
     * @return EntityQueueInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): EntityQueueInterface;

    /**
     * Delete entity queue
     *
     * @param EntityQueueInterface $entityQueue
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(EntityQueueInterface $entityQueue): bool;

    /**
     * Delete entity queue by ID
     *
     * @param int $entityId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;

    /**
     * Get entity queues matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Get pending entity queues
     *
     * @param int $limit
     * @return EntityQueueInterface[]
     */
    public function getPendingQueues(int $limit = 100): array;

    /**
     * Add entity to queue
     *
     * @param int $targetEntityId
     * @param string $entityType
     * @param int[] $storeIds
     * @return EntityQueueInterface[]
     * @throws CouldNotSaveException
     */
    public function addToQueue(int $targetEntityId, string $entityType, array $storeIds): array;
}
