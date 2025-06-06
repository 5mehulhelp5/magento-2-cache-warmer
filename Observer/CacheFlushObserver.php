<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Observer;

use Blackbird\CacheWarmer\Logger\Logger;
use Blackbird\CacheWarmer\Model\Flag;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\FlagManager;
use Magento\PageCache\Model\Config;

/**
 * Observer for cache flush events to trigger cache warming
 */
class CacheFlushObserver implements ObserverInterface
{
    /**
     * @param FlagManager $flagManager
     * @param Config $pageCacheConfig
     * @param Logger $logger
     */
    public function __construct(
        protected FlagManager $flagManager,
        protected Config $pageCacheConfig,
        protected Logger $logger
    ) {
    }

    /**
     * Execute observer for cache flush events
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            // Only proceed if full page cache is enabled
            if (!$this->pageCacheConfig->isEnabled()) {
                return;
            }

            $this->logger->info('Cache flush detected, scheduling cache warming');

            // Set flag to trigger cache warming on next cron run
            $this->flagManager->saveFlag(Flag::TRIGGER_WARMING_FLAG_CODE, true);
        } catch (\Exception $e) {
            $this->logger->error('Error scheduling cache warming: ' . $e->getMessage());
        }
    }
}
