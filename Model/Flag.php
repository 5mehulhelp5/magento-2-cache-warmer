<?php

declare(strict_types=1);

namespace Blackbird\CacheWarmer\Model;

/**
 * Flag constants for cache warmer
 */
class Flag
{
    /**
     * Flag code to trigger cache warming
     */
    public const TRIGGER_WARMING_FLAG_CODE = 'blackbird_cachewarmer_trigger_warming';

    /**
     * Flag code to indicate cache warming is in progress
     */
    public const WARMING_IN_PROGRESS_FLAG_CODE = 'blackbird_cachewarmer_in_progress';
}
