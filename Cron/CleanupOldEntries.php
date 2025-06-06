<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Cron;

use Blackbird\CacheWarmer\Logger\Logger;
use Blackbird\CacheWarmer\Model\Config;
use Blackbird\CacheWarmer\Model\ResourceModel\EntityQueue\CollectionFactory;
use Blackbird\CacheWarmer\Model\EntityQueueRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Cron job for cleaning up completed entity queue entries older than a week
 */
class CleanupOldEntries
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param EntityQueueRepository $entityQueueRepository
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        protected CollectionFactory $collectionFactory,
        protected EntityQueueRepository $entityQueueRepository,
        protected DateTime $dateTime,
        protected Logger $logger,
        protected Config $config
    ) {
    }

    /**
     * Execute the cron job to delete entries older than a week
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isAsyncCacheWarmerEnabled()) {
            return;
        }
        try {
            $this->logger->info('Starting cleanup of old entity queue entries');

            // Calculate the date one week ago
            $oneWeekAgo = $this->dateTime->gmtDate('Y-m-d H:i:s', strtotime('-1 week'));

            // Get collection of entries older than a week
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('updated_at', ['lt' => $oneWeekAgo]);

            $count = 0;
            foreach ($collection as $item) {
                try {
                    $this->entityQueueRepository->delete($item);
                    $count++;
                } catch (\Exception $e) {
                    $this->logger->error(
                        'Error deleting completed entity queue entry: ' . $e->getMessage(),
                        ['entity_id' => $item->getEntityId()]
                    );
                }
            }

            $this->logger->info('Finished cleanup of old entity queue entries. Deleted ' . $count . ' entries.');
        } catch (\Exception $e) {
            $this->logger->error('Error during cleanup of old entity queue entries: ' . $e->getMessage());
        }
    }
}
