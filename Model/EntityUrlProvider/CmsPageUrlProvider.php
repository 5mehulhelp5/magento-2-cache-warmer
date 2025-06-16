<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model\EntityUrlProvider;

use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Blackbird\CacheWarmer\Api\EntityUrlProviderInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\UrlInterface;

/**
 * URL provider for CMS page entities
 */
class CmsPageUrlProvider implements EntityUrlProviderInterface
{
    /**
     * @param PageRepositoryInterface $pageRepository
     * @param UrlInterface $url
     * @param Emulation $storeEmulation
     */
    public function __construct(
        protected PageRepositoryInterface $pageRepository,
        protected UrlInterface            $url,
        protected Emulation               $storeEmulation
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getUrl(int $entityId, int $storeId): array
    {
        // Start store emulation
        $this->storeEmulation->startEnvironmentEmulation($storeId);

        try {
            // Get CMS page URL
            $page = $this->pageRepository->getById($entityId);
            $pageUrl = $this->url->getUrl($page->getIdentifier());

            return [$pageUrl];
        } finally {
            // Stop store emulation
            $this->storeEmulation->stopEnvironmentEmulation();
        }
    }

    /**
     * @inheritDoc
     */
    public function supports(string $entityType): bool
    {
        return $entityType === EntityQueueInterface::ENTITY_TYPE_CMS_PAGE;
    }
}
