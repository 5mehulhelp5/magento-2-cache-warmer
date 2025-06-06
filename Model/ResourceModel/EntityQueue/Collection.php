<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\ResourceModel\EntityQueue;

use Blackbird\CacheWarmer\Model\EntityQueue;
use Blackbird\CacheWarmer\Model\ResourceModel\EntityQueue as EntityQueueResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Entity Queue Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(EntityQueue::class, EntityQueueResource::class);
    }
}
