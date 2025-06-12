<?php

namespace Blackbird\CacheWarmer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    // phpcs:disable

    public const BLACKBIRD_CACHEWARMER_GENERAL_ENABLED = "blackbird_cachewarmer/general/enabled";


    public const BLACKBIRD_CACHEWARMER_GENERAL_BASIC_AUTH_ENABLE = 'blackbird_cachewarmer/general/basic_auth_enabled';

    public const BLACKBIRD_CACHEWARMER_GENERAL_BASIC_AUTH_USERNAME = 'blackbird_cachewarmer/general/basic_auth_username';

    public const BLACKBIRD_CACHEWARMER_GENERAL_BASIC_AUTH_PASSWORD = 'blackbird_cachewarmer/general/basic_auth_password';

    public const BLACKBIRD_CACHEWARMER_GENERAL_CONCURRENCY = 'blackbird_cachewarmer/general/concurrency';

    public const BLACKBIRD_CACHEWARMER_GENERAL_SWITCH_STORE = 'blackbird_cachewarmer/general/switch_store';

    public const BLACKBIRD_CACHEWARMER_GENERAL_INSTANCES = 'blackbird_cachewarmer/general/instances';

    public const BLACKBIRD_CACHEWARMER_GENERAL_CUSTOMER_CREDENTIALS = "blackbird_cachewarmer/general/customer_credentials";
    public const BLACKBIRD_CACHEWARMER_GENERAL_NOT_LOGGED_IN_CRAWL_DISABLED = "blackbird_cachewarmer/general/not_logged_in_crawl_disabled";
    public const BLACKBIRD_CACHEWARMER_GENERAL_DEFAULT_COLLECTOR_TYPE = "blackbird_cachewarmer/general/default_collector_type";
    public const BLACKBIRD_CACHEWARMER_GENERAL_SLACK_WEBHOOK_ENABLED = "blackbird_cachewarmer/general/slack_webhook_enabled";
    public const BLACKBIRD_CACHEWARMER_GENERAL_SLACK_WEBHOOK_URL = "blackbird_cachewarmer/general/slack_webhook_url";

    public const BLACKBIRD_CACHEWARMER_GENERAL_ASYNC_CACHEWARMER_ENABLED = "blackbird_cachewarmer/general/async_cachewarmer_enabled";

    public const BLACKBIRD_CACHEWARMER_LOGGING_LOG_LEVEL = "blackbird_cachewarmer/logging/log_level";

    // phpcs : enable

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected EncryptorInterface $encryptor
    ) {
    }

    /**
     * @param StoreInterface|null $store
     * @return bool
     */
    public function isEnabled(?StoreInterface $store): bool
    {
        return $this->scopeConfig->isSetFlag(self::BLACKBIRD_CACHEWARMER_GENERAL_ENABLED, ScopeInterface::SCOPE_STORE, $store?->getCode());
    }

    /**
     * @param StoreInterface $store
     * @return array<int, array{username: string, password: string}>
     */
    public function getCustomerCredentials(StoreInterface $store): array
    {
        $value = $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_CUSTOMER_CREDENTIALS,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : [];
    }

    /**
     * @param StoreInterface $store
     * @return bool
     */
    public function isNotLoggedInCrawlDisabled(StoreInterface $store): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_NOT_LOGGED_IN_CRAWL_DISABLED,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * @param StoreInterface $store
     * @return array<int, string>
     */
    public function getInstances(StoreInterface $store): array
    {
        $value = $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_INSTANCES,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        if (empty($value)) {
            return [];
        }

        // Split by comma and trim whitespace
        $instances = array_map('trim', explode(',', $value));

        // Filter out empty values
        return array_filter($instances, function ($ip) {
            return !empty($ip);
        });
    }

    /**
     * @param StoreInterface $store
     * @return bool
     */
    public function isSwitchStoreEnabled(StoreInterface $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BLACKBIRD_CACHEWARMER_GENERAL_SWITCH_STORE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * @param StoreInterface|null $getStore
     * @return bool
     */
    public function isBasicAuthEnabled(?StoreInterface $getStore): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BLACKBIRD_CACHEWARMER_GENERAL_BASIC_AUTH_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $getStore->getCode()
        );
    }

    /**
     * @param StoreInterface|null $getStore
     * @return string|null
     */
    public function getBasicAuthUsername(?StoreInterface $getStore): ?string
    {
        return $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_BASIC_AUTH_USERNAME,
            ScopeInterface::SCOPE_STORE,
            $getStore->getCode()
        );
    }

    /**
     * @param StoreInterface|null $getStore
     * @return string|null
     */
    public function getBasicAuthPassword(?StoreInterface $getStore): ?string
    {
        $password = $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_BASIC_AUTH_PASSWORD,
            ScopeInterface::SCOPE_STORE,
            $getStore->getCode()
        );
        if(empty($password)) {
            return null;
        }
        return $this->encryptor->decrypt($password);
    }

    /**
     * @param StoreInterface|null $getStore
     * @return int
     */
    public function getConcurrency(?StoreInterface $getStore): int
    {
        return (int)$this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_CONCURRENCY,
            ScopeInterface::SCOPE_STORE,
            $getStore->getCode()
        ) ?: 1;
    }

    /**
     * @param StoreInterface|null $store
     * @return string|null
     */
    public function getDefaultType(?StoreInterface $store): ?string
    {
        return $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_DEFAULT_COLLECTOR_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Check if Slack webhook notifications are enabled
     *
     * @param StoreInterface|null $store
     * @return bool
     */
    public function isSlackWebhookEnabled(?StoreInterface $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BLACKBIRD_CACHEWARMER_GENERAL_SLACK_WEBHOOK_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Get Slack webhook URL
     *
     * @param StoreInterface|null $store
     * @return string|null
     */
    public function getSlackWebhookUrl(?StoreInterface $store): ?string
    {
        return $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_GENERAL_SLACK_WEBHOOK_URL,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * @return bool
     */
    public function isAsyncCacheWarmerEnabled(): bool
    {
        return $this->isEnabled(null) && $this->scopeConfig->isSetFlag(
            self::BLACKBIRD_CACHEWARMER_GENERAL_ASYNC_CACHEWARMER_ENABLED
        );
    }

    /**
     * Get log level for warmer logger
     *
     * @param StoreInterface|null $store
     * @return string
     */
    public function getLogLevel(?StoreInterface $store): string
    {
        return (string) $this->scopeConfig->getValue(
            self::BLACKBIRD_CACHEWARMER_LOGGING_LOG_LEVEL,
            ScopeInterface::SCOPE_STORE,
            $store?->getCode()
        );
    }
}
