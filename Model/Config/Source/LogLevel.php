<?php

namespace Blackbird\CacheWarmer\Model\Config\Source;

use Blackbird\CacheWarmer\Logger\Logger;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for log level configuration
 */
class LogLevel implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Logger::LOG_LEVEL_DEBUG, 'label' => __('Debug')],
            ['value' => Logger::LOG_LEVEL_INFO, 'label' => __('Info')],
            ['value' => Logger::LOG_LEVEL_NOTICE, 'label' => __('Notice')],
            ['value' => Logger::LOG_LEVEL_WARNING, 'label' => __('Warning')],
            ['value' => Logger::LOG_LEVEL_ERROR, 'label' => __('Error')],
            ['value' => Logger::LOG_LEVEL_CRITICAL, 'label' => __('Critical')],
            ['value' => Logger::LOG_LEVEL_ALERT, 'label' => __('Alert')],
            ['value' => Logger::LOG_LEVEL_EMERGENCY, 'label' => __('Emergency')],
        ];
    }
}
