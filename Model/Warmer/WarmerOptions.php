<?php

namespace Blackbird\CacheWarmer\Model\Warmer;

use Blackbird\CacheWarmer\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WarmerOptions
{
    /**
     * @param Config $config
     * @param StoreInterface|null $store
     * @param OutputInterface|null $output
     * @param int|null $concurrency
     * @param bool|null $switchStore
     * @param string|null $basicAuthUsername
     * @param string|null $basicAuthPassword
     * @param bool|null $isNotLoggedInCrawDisable
     * @param array|null $customerCredentials
     * @param array $instances
     * @param bool|null $slackWebhookEnabled
     * @param string|null $slackWebhookUrl
     * @param string|null $logLevel
     */
    public function __construct(
        protected Config $config,
        protected ?StoreInterface $store = null,
        protected ?OutputInterface $output = null,
        protected ?int $concurrency = null,
        protected ?bool $switchStore = null,
        protected ?string $basicAuthUsername = null,
        protected ?string $basicAuthPassword = null,
        protected ?bool $isNotLoggedInCrawDisable = null,
        protected ?array $customerCredentials = null,
        protected array $instances = [],
        protected ?bool $slackWebhookEnabled = null,
        protected ?string $slackWebhookUrl = null,
        protected ?string $logLevel = null
    ) {
    }

    /**
     * @return OutputInterface|null
     */
    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * @return array{username: string, password:string}
     */
    public function getCustomerCredentials(): array
    {
        return $this->customerCredentials ?? $this->config->getCustomerCredentials($this->getStore());
    }

    /**
     * @return int
     */
    public function getConcurrency(): int
    {
        return $this->concurrency ?? $this->config->getConcurrency($this->getStore());
    }

    /**
     * @return bool
     */
    public function isSwitchStoreEnabled(): bool
    {
        return $this->switchStore ?? $this->config->isSwitchStoreEnabled($this->getStore());
    }

    /**
     * @return StoreInterface|null
     */
    public function getStore(): ?StoreInterface {
        return $this->store;
    }

    /**
     * @return bool
     */
    public function isBasicAuthEnabled(): bool
    {
        return $this->getBasicAuthUsername() && $this->getBasicAuthPassword();
    }

    /**
     * @return string|null
     */
    public function getBasicAuthUsername(): ?string
    {
        return $this->basicAuthUsername ?? $this->config->getBasicAuthUsername($this->getStore());
    }

    /**
     * @return string|null
     */
    public function getBasicAuthPassword(): ?string{
        return $this->basicAuthPassword ?? $this->config->getBasicAuthPassword($this->getStore());
    }

    /**
     * @return array
     */
    public function getInstances(): array{
        return $this->instances ?? $this->config->getInstances($this->getStore());
    }

    /**
     * @return bool
     */
    public function isNotLoggedInCrawlDisabled(): bool
    {
        return $this->isNotLoggedInCrawDisable ??  $this->config->isNotLoggedInCrawlDisabled($this->getStore());
    }

    /**
     * Check if Slack webhook notifications are enabled
     *
     * @return bool
     */
    public function isSlackWebhookEnabled(): bool
    {
        return $this->slackWebhookEnabled ?? $this->config->isSlackWebhookEnabled($this->getStore());
    }

    /**
     * Get Slack webhook URL
     *
     * @return string|null
     */
    public function getSlackWebhookUrl(): ?string
    {
        return $this->slackWebhookUrl ?? $this->config->getSlackWebhookUrl($this->getStore());
    }

    /**
     * Get log level for warmer logger
     *
     * @return string
     */
    public function getLogLevel(): string
    {
        return $this->logLevel ?? $this->config->getLogLevel($this->getStore());
    }
}
