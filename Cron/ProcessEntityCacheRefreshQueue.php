<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Cron;

use Blackbird\CacheWarmer\Logger\Logger;
use Blackbird\CacheWarmer\Model\Config;
use Blackbird\CacheWarmer\Model\Consumer\EntityCacheRefreshConsumer;

/**
 * Cron job for processing entity cache refresh queue
 */
class ProcessEntityCacheRefreshQueue
{
    /**
     * @param EntityCacheRefreshConsumer $consumer
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        protected EntityCacheRefreshConsumer $consumer,
        protected Logger $logger,
        protected Config $config
    ) {
    }

    /**
     * Execute the cron job
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isAsyncCacheWarmerEnabled()) {
            return;
        }
        try {
            $this->logger->info('Starting entity cache refresh queue processing');
            $this->consumer->process();
            $this->logger->info('Finished entity cache refresh queue processing');
        } catch (\Exception $e) {
            $this->logger->error('Error processing entity cache refresh queue: ' . $e->getMessage());
        }
    }
}
