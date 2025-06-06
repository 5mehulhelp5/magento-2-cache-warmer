<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Api\Data;

/**
 * Entity Queue Interface
 */
interface EntityQueueInterface
{
    /**
     * Constants for keys of data array
     */
    public const ENTITY_ID = 'entity_id';
    public const TARGET_ENTITY_ID = 'target_entity_id';
    public const ENTITY_TYPE = 'entity_type';
    public const STORE_ID = 'store_id';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_ERROR = 'error';

    /**
     * Entity type constants
     */
    public const ENTITY_TYPE_PRODUCT = 'catalog_product';
    public const ENTITY_TYPE_CATEGORY = 'catalog_category';
    public const ENTITY_TYPE_CMS_PAGE = 'cms_page';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set ID
     *
     * @param int|string $entityId
     * @return $this
     */
    public function setEntityId($entityId): self;

    /**
     * Get target entity ID
     *
     * @return int
     */
    public function getTargetEntityId(): int;

    /**
     * Set target entity ID
     *
     * @param int $targetEntityId
     * @return $this
     */
    public function setTargetEntityId(int $targetEntityId): self;

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType(): string;

    /**
     * Set entity type
     *
     * @param string $entityType
     * @return $this
     */
    public function setEntityType(string $entityType): self;

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): self;

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self;
}
