# Blackbird Cache Warmer

[![Latest Stable Version](https://img.shields.io/packagist/v/blackbird/module-cache-warmer.svg?style=flat-square)](https://packagist.org/packages/blackbird/module-cache-warmer)
[![License: MIT](https://img.shields.io/github/license/blackbird-agency/magento-2-cache-warmer.svg?style=flat-square)](./LICENSE.txt)

This module provides functionality to warm up the cache by crawling pages of your Magento store. It helps improve the performance of your store by ensuring that cache is pre-populated, resulting in faster page load times for your customers.

Key functionality includes:
- Crawling pages of your Magento store to warm up the cache
- Configuring basic authentication for crawling
- Setting concurrency for crawling
- Enabling/disabling store switching
- Configuring customer credentials for authenticated crawling
- Automatic async cache refresh for entities (products, categories, CMS pages, etc.)
- Automatic cache warming when full_page cache is flushed from the admin panel
- Slack webhook notifications for cache warming errors

The source code is available at the [GitHub repository](https://github.com/blackbird-agency/magento-2-cache-warmer).

---

## Setup

### Get the Package

#### **Zip Package:**

Unzip the package into `app/code/Blackbird/CacheWarmer`, from the root of your Magento instance.

#### **Composer Package:**

```shell
composer require blackbird/module-cache-warmer
```

### Install the Module

Go to your Magento root directory, then run the following Magento commands:

**If you are in production mode, do not forget to recompile and redeploy the static resources, or to use the `--keep-generated` option.**

```shell
bin/magento module:enable Blackbird_CacheWarmer
bin/magento setup:upgrade
bin/magento cache:flush
```

## Features

### Cache Warming

The Cache Warmer module crawls pages of your Magento store to warm up the cache. This helps improve the performance of your store by ensuring that cache is pre-populated, resulting in faster page load times for your customers.

### Configurable Crawling

The module provides various configuration options for crawling:
- Basic authentication for crawling
- Concurrency for crawling (number of concurrent requests)
- Store switching (enable/disable)
- Customer credentials for authenticated crawling
- Crawling for not logged in users (enable/disable)
- Default collector type (Sitemap or Product)

### Automatic Async Cache Refresh for Entities

The module includes a feature that automatically triggers an asynchronous cache refresh for entities (products, categories, CMS pages) when their Varnish cache is cleaned. This ensures that entity pages are quickly re-cached after cache invalidation, maintaining optimal performance for your store.

#### How it works:

1. When an entity's cache is invalidated in Varnish (through the \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver), the module detects this and adds the entity to a queue.
2. A cron job runs every minute to process the queue and warm the cache for these entities.
3. The queue is stored in a dedicated database table (`blackbird_cachewarmer_entity_queue`), ensuring reliability and persistence.

#### Supported Entity Types:

- Products
- Categories
- CMS Pages

### Automatic Cache Warming on Full Page Cache Flush

The module includes a feature that automatically triggers cache warming when the full_page cache is flushed from the admin panel. This ensures that your store's pages are quickly re-cached after a cache flush, maintaining optimal performance.

#### How it works:

1. When the full_page cache is flushed from the admin panel (either through "Flush Magento Cache" or "Flush Cache Storage"), the module detects this event.
2. The module sets a flag in the database to indicate that cache warming should be triggered.
3. A cron job runs every minute to check if cache warming should be triggered.
4. If cache warming is triggered, the cron job warms the cache for all urls as the command does.
5. The module uses a timestamp-based flag system to:
   - Ensure that only one cache warming process runs at a time
   - Automatically kill any process that has been running for more than 6 hours

### Custom Logging

The module provides custom logging to `warmer.log` with configurable log levels. You can select the log level for `warmer.log` (default: error). Messages with this level and higher will be logged to `var/log/warmer.log`.

### Slack Webhook Notifications

The module can send notifications to Slack when cache warming encounters errors. This helps administrators monitor the cache warming process and quickly identify and resolve issues.

#### How it works:

1. When cache warming encounters errors, the module sends a notification to the configured Slack webhook URL.
2. The notification includes a message indicating that errors occurred and advises checking the logs for more details.
3. This feature can be enabled/disabled in the module configuration.

---

## Usage

### Configuration

The module can be configured in the Magento admin panel under **Stores > Configuration > Blackbird Extensions > Cache Warmer**.

#### General Configuration
- **Basic Auth Username**: Username for basic authentication
- **Basic Auth Password**: Password for basic authentication
- **Concurrency**: Number of concurrent requests (default: 1)
- **Switch Store**: Enable/disable store switching (default: disabled)
- **Instances**: Instances configuration
- **Customer Credentials**: Customer credentials for authentication (format: username:password)
- **Disable Not Logged In Crawl**: Enable/disable crawling for not logged in users (default: disabled)
- **Default Collector Type**: Select the default collector type (Sitemap or Product) or leave empty for no default
- **Enable Slack Webhook Notifications**: Enable/disable notifications to Slack when cache warming encounters errors (default: disabled)
- **Slack Webhook URL**: URL of the Slack webhook to send notifications to

#### Logging Configuration
- **Log Level**: Select the log level for warmer.log (default: error). Messages with this level and higher will be logged to var/log/warmer.log

### Console Commands

The module provides console commands to run the cache warmer:

```bash
bin/magento blackbird:cachewarmer:run
```

---

## Extending with Custom Collector Types

The module provides an extensible architecture that allows third-party modules to add their own collector types. To add a custom collector type, follow these steps:

1. Create a class that implements `Blackbird\CacheWarmer\Api\UrlCollectorInterface`:

```php
<?php

namespace YourVendor\YourModule\Model\Collector;

use Blackbird\CacheWarmer\Api\UrlCollectorInterface;
use Magento\Store\Api\Data\StoreInterface;

class YourCustomCollector implements UrlCollectorInterface
{
    /**
     * @inheritDoc
     */
    public function collectUrls(array $stores): array
    {
        $urls = [];
        // Your custom logic to collect URLs
        return $urls;
    }
}
```

2. Create a provider class that implements `Blackbird\CacheWarmer\Api\CollectorTypeProviderInterface`:

```php
<?php

namespace YourVendor\YourModule\Model\CollectorType;

use Blackbird\CacheWarmer\Api\CollectorTypeProviderInterface;
use YourVendor\YourModule\Model\Collector\YourCustomCollector;

class Provider implements CollectorTypeProviderInterface
{
    /**
     * @param YourCustomCollector $customCollector
     */
    public function __construct(
        protected YourCustomCollector $customCollector
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCollectorTypes(): array
    {
        return [
            'your_custom_type' => [
                'sort_order' => 30, // Higher than existing types
                'class' => $this->customCollector,
                'label' => __('Your Custom Type')
            ]
        ];
    }
}
```

3. Register your provider in your module's `di.xml`:

```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Blackbird\CacheWarmer\Model\Service\CollectorTypeService">
        <arguments>
            <argument name="collectorTypeProviders" xsi:type="array">
                <item name="your_provider" xsi:type="object">YourVendor\YourModule\Model\CollectorType\Provider</item>
            </argument>
        </arguments>
    </type>
</config>
```

## Extending with Custom Entity Types

The module provides an extensible architecture that allows third-party modules to add support for additional entity types. To add support for a custom entity type, follow these steps:

1. Create a class that implements `Blackbird\CacheWarmer\Api\EntityUrlProviderInterface`:

```php
<?php

namespace YourVendor\YourModule\Model\EntityUrlProvider;

use Blackbird\CacheWarmer\Api\EntityUrlProviderInterface;
use Blackbird\CacheWarmer\Api\Data\EntityQueueInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class YourCustomEntityUrlProvider implements EntityUrlProviderInterface
{
    /**
     * Get the URL for an entity
     *
     * @param int $entityId The ID of the entity
     * @param int $storeId The store ID
     * @return string The URL of the entity
     * @throws NoSuchEntityException If the entity does not exist
     */
    public function getUrl(int $entityId, int $storeId): string
    {
        // Your custom logic to get the URL for the entity
        return $url;
    }

    /**
     * Check if this provider supports the given entity type
     *
     * @param string $entityType The entity type
     * @return bool True if this provider supports the entity type, false otherwise
     */
    public function supports(string $entityType): bool
    {
        return $entityType === 'your_custom_entity_type';
    }
}
```

2. Register your provider in your module's `di.xml`:

```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Blackbird\CacheWarmer\Model\EntityUrlProvider\CompositeEntityUrlProvider">
        <arguments>
            <argument name="providers" xsi:type="array">
                <item name="your_custom_entity" xsi:type="object">YourVendor\YourModule\Model\EntityUrlProvider\YourCustomEntityUrlProvider</item>
            </argument>
        </arguments>
    </type>
</config>
```

---

## Requirements

- Magento 2.4.5 or higher
- PHP 8.1 or higher
- blackbird/extensions-core >= 100.0.9

---

## Support

- If you have any issue with this code, feel free to [open an issue](https://github.com/blackbird-agency/magento-2-cache-warmer/issues/new).
- If you want to contribute to this project, feel free to [create a pull request](https://github.com/blackbird-agency/magento-2-cache-warmer/compare).

---

## Contact

For further information, contact us:

- by email: hello@bird.eu
- or by form: [https://black.bird.eu/en/contacts/](https://black.bird.eu/contacts/)

---

## Authors

- **Blackbird Team** - *Maintainer* - [They're awesome!](https://github.com/blackbird-agency)

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE.txt) file for details.

---

***That's all folks!***
