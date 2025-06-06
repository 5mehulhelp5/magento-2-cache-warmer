<?php

namespace Blackbird\CacheWarmer\Model\Collector;

use Blackbird\CacheWarmer\Api\UrlCollectorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory as SitemapCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class SitemapUrlCollector implements UrlCollectorInterface
{
    protected SitemapCollectionFactory $sitemapCollectionFactory;
    protected StoreManagerInterface $storeManager;
    protected DirectoryList $directoryList;
    protected File $file;

    /**
     * @param SitemapCollectionFactory $sitemapCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        SitemapCollectionFactory $sitemapCollectionFactory,
        StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    public function collectUrls(array $stores): array
    {
        $urlsWithFrequency = [];

        $sitemapCollection = $this->sitemapCollectionFactory->create();
        $storeIds = array_map(function ($store) {
            return $store->getId();
        }, $stores);
        $sitemapCollection->addFieldToFilter('store_id', ['in' => $storeIds]);

        // Define priority mapping for changefreq values
        $frequencyPriority = [
            'always' => 7,
            'hourly' => 6,
            'daily' => 5,
            'weekly' => 4,
            'monthly' => 3,
            'yearly' => 2,
            'never' => 1
        ];

        foreach ($sitemapCollection as $sitemap) {
            $sitemapPath = rtrim($sitemap->getData("sitemap_path") ?? "", '/');
            $sitemapFilename = $sitemap->getData("sitemap_filename") ?? "";
            $fullPath = $this->directoryList->getPath(DirectoryList::PUB) . $sitemapPath . '/' . $sitemapFilename;

            if ($this->file->fileExists($fullPath)) {
                $content = $this->file->read($fullPath);
                $xml = simplexml_load_string($content);

                if ($xml && isset($xml->url)) {
                    foreach ($xml->url as $urlEntry) {
                        if (isset($urlEntry->loc)) {
                            $url = (string) $urlEntry->loc;
                            $changefreq = isset($urlEntry->changefreq) ? (string) $urlEntry->changefreq : 'monthly'; // Default to monthly if not set
                            $priority = $frequencyPriority[$changefreq] ?? 3; // Default to 3 (monthly) if not in mapping

                            $urlsWithFrequency[] = [
                                'url' => $url,
                                'priority' => $priority
                            ];
                        }
                    }
                }
            }
        }

        // Sort URLs by priority in descending order
        usort($urlsWithFrequency, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        // Extract just the URLs in the sorted order
        $urls = array_map(function ($item) {
            return $item['url'];
        }, $urlsWithFrequency);

        return $urls;
    }
}
