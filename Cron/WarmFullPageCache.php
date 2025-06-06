<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Cron;

use Blackbird\CacheWarmer\Api\UrlPoolCollectorInterface;
use Blackbird\CacheWarmer\Logger\Logger;
use Blackbird\CacheWarmer\Model\Config;
use Blackbird\CacheWarmer\Model\Flag;
use Blackbird\CacheWarmer\Model\Service\WarmerFactory;
use Blackbird\CacheWarmer\Model\Warmer\WarmerOptionsFactory;
use Magento\Framework\FlagManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cron job to warm full page cache after it has been flushed
 */
class WarmFullPageCache
{
    /**
     * Timeout for cache warming process in seconds (6 hours)
     */
    private const CACHE_WARMING_TIMEOUT = 21600; // 6 hours in seconds

    /**
     * @param FlagManager $flagManager
     * @param WarmerFactory $warmerFactory
     * @param WarmerOptionsFactory $warmerOptionsFactory
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param UrlPoolCollectorInterface $urlPoolCollector
     * @param Config $config
     */
    public function __construct(
        protected FlagManager $flagManager,
        protected WarmerFactory $warmerFactory,
        protected WarmerOptionsFactory $warmerOptionsFactory,
        protected StoreManagerInterface $storeManager,
        protected Logger $logger,
        protected UrlPoolCollectorInterface $urlPoolCollector,
        protected Config $config
    ) {
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(): void
    {
        try {
            if (!$this->config->isAsyncCacheWarmerEnabled()) {
                return;
            }
            // Check if warming should be triggered
            $shouldWarm = (bool)$this->flagManager->getFlagData(Flag::TRIGGER_WARMING_FLAG_CODE);
            if (!$shouldWarm) {
                return;
            }

            // Get current timestamp
            $currentTime = time();

            // Check if warming is already in progress
            $warmingStartTime = (int)$this->flagManager->getFlagData(Flag::WARMING_IN_PROGRESS_FLAG_CODE);

            if ($warmingStartTime > 0) {
                // Check if the previous warming process has been running for more than 6 hours
                if (($currentTime - $warmingStartTime) > self::CACHE_WARMING_TIMEOUT) {
                    $this->logger->info('Previous cache warming process exceeded timeout, restarting');
                } else {
                    $this->logger->info('Cache warming is already in progress, skipping');
                    return;
                }
            }

            // Set warming in progress flag with current timestamp
            $this->flagManager->saveFlag(Flag::WARMING_IN_PROGRESS_FLAG_CODE, $currentTime);

            // Clear trigger flag
            $this->flagManager->saveFlag(Flag::TRIGGER_WARMING_FLAG_CODE, false);

            $this->logger->info('Starting full page cache warming');
            // Set time limit for the script execution (6 hours)
            set_time_limit(self::CACHE_WARMING_TIMEOUT);


            foreach ($this->storeManager->getStores() as $store) {
                $warmer = $this->warmerFactory->create([
                    'options' => $this->warmerOptionsFactory->create([
                        'store' => $store
                    ])
                ]);

                $warmer->warmUrls(
                    $this->urlPoolCollector->collectUrls([$store], $this->config->getDefaultType($store))
                );
            }

            $this->logger->info('Full page cache warming completed');

            // Clear warming in progress flag
            $this->flagManager->saveFlag(Flag::WARMING_IN_PROGRESS_FLAG_CODE, 0);
        } catch (\Exception $e) {
            // Clear flags in case of error
            $this->flagManager->saveFlag(Flag::WARMING_IN_PROGRESS_FLAG_CODE, 0);
            $this->logger->error('Error warming full page cache: ' . $e->getMessage());
        }
    }
}
