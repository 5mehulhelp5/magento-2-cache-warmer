<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\ResourceModel;

use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Entity Queue Resource Model
 */
class EntityQueue extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('blackbird_cachewarmer_entity_queue', EntityQueueInterface::ENTITY_ID);
    }
}
