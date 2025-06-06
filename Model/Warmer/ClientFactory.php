<?php

namespace Blackbird\CacheWarmer\Model\Warmer;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class ClientFactory
{
    /**
     * @var array
     */
    protected array $defaultConfig = [];

    /**
     * @param array $config
     * @return Client
     */
    public function create(array $config = []): Client
    {
        return new Client($this->getConfig($config));
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setDefaultConfig(array $config): self
    {
        $this->defaultConfig = $config;
        return $this;
    }

    /**
     * @param array $config
     * @return array
     */
    public function getConfig(array $config): array
    {
        $config = array_merge($this->defaultConfig, $config);
        if (!isset($config['cookies'])) {
            $config['cookies'] = new CookieJar();
        }
        if (!isset($config['headers']['User-Agent'])) {
            $config['User-Agent'] = "Magento Cache Warmer";
        }
        return $config;
    }
}
