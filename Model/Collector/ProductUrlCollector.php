<?php

namespace Blackbird\CacheWarmer\Model\Collector;

use Blackbird\CacheWarmer\Api\UrlCollectorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;

class ProductUrlCollector implements UrlCollectorInterface
{
    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param ProductHelper $productHelper
     * @param Emulation $storeEmulation
     */
    public function __construct(
        protected ProductCollectionFactory $productCollectionFactory,
        protected Status $productStatus,
        protected Visibility $productVisibility,
        protected ProductHelper $productHelper,
        protected Emulation $storeEmulation
    ) {
    }

    /**
     * @param array $stores
     * @return array
     */
    public function collectUrls(array $stores): array
    {
        $urls = [];
        foreach ($stores as $store) {
            foreach ($this->collectUrlForStore($store) as $url) {
                $urls[] = $url;
            }
        }
        return $urls;
    }

    /**
     * @param StoreInterface $store
     * @return array
     */
    public function collectUrlForStore(StoreInterface $store): array
    {
        $this->storeEmulation->startEnvironmentEmulation($store->getId());
        $productUrls = [];

        // Get product collection already sorted by children count
        $productCollection = $this->getProductCollection($store);

        // Get URLs directly from the sorted collection
        foreach ($productCollection as $product) {
            foreach ($this->getUrlsFromProduct($product) as $url) {
                $productUrls[] = $url;
            }
        }

        $this->storeEmulation->stopEnvironmentEmulation();
        return $productUrls;
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getUrlsFromProduct(ProductInterface $product):array
    {
        return [$this->productHelper->getProductUrl($product)];
    }

    /**
     * @param StoreInterface $store
     * @return ProductCollection
     */
    public function getProductCollection(StoreInterface $store): ProductCollection
    {
        // Create a product collection
        $productCollection = $this->productCollectionFactory->create();

        // Add visibility filter to get only visible products
        $productCollection->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        $productCollection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);

        // Add enabled filter to get only enabled products
        $productCollection->addAttributeToFilter(
            'status',
            Status::STATUS_ENABLED
        );
        $productCollection->addUrlRewrite();
        $productCollection->addStoreFilter($store);

        // Add type_id attribute to identify configurable products
        $productCollection->addAttributeToSelect('type_id');

        // Join with catalog_product_super_link table to count children for configurable products
        $linkTable = $productCollection->getTable('catalog_product_super_link');

        $idFieldName =  $productCollection->getProductEntityMetadata()->getLinkField();

        // Add a left join to count children
        $productCollection->getSelect()->joinLeft(
            ['super_link' => $linkTable],
            "e.{$idFieldName} = super_link.parent_id",
            [
                'children_count' => new \Zend_Db_Expr('COUNT(super_link.product_id)'),
            ]
        )->group("e.{$idFieldName}")
        ->order('children_count DESC');

        return $productCollection;
    }
}
