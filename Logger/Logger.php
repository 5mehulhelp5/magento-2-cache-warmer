<?php

namespace Blackbird\CacheWarmer\Logger;

use Magento\Framework\Logger\Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

/**
 * Custom logger for Blackbird CacheWarmer module
 */
class Logger extends Monolog
{
    /**
     * Log levels
     */
    public const LOG_LEVEL_DEBUG = 'debug';
    public const LOG_LEVEL_INFO = 'info';
    public const LOG_LEVEL_NOTICE = 'notice';
    public const LOG_LEVEL_WARNING = 'warning';
    public const LOG_LEVEL_ERROR = 'error';
    public const LOG_LEVEL_CRITICAL = 'critical';
    public const LOG_LEVEL_ALERT = 'alert';
    public const LOG_LEVEL_EMERGENCY = 'emergency';

    /**
     * @var array<string, int>
     */
    private static array $levelMap = [
        self::LOG_LEVEL_DEBUG => MonologLogger::DEBUG,
        self::LOG_LEVEL_INFO => MonologLogger::INFO,
        self::LOG_LEVEL_NOTICE => MonologLogger::NOTICE,
        self::LOG_LEVEL_WARNING => MonologLogger::WARNING,
        self::LOG_LEVEL_ERROR => MonologLogger::ERROR,
        self::LOG_LEVEL_CRITICAL => MonologLogger::CRITICAL,
        self::LOG_LEVEL_ALERT => MonologLogger::ALERT,
        self::LOG_LEVEL_EMERGENCY => MonologLogger::EMERGENCY,
    ];

    /**
     * @param string $name
     * @param \Blackbird\CacheWarmer\Model\Config $config
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        string $name,
        private \Blackbird\CacheWarmer\Model\Config $config,
        array $handlers = [],
        array $processors = []
    ) {
        $logLevel = $this->config->getLogLevel(null) ?? self::LOG_LEVEL_ERROR;

        // Convert string log level to Monolog constant
        $monologLevel = self::$levelMap[$logLevel] ?? MonologLogger::ERROR;

        // Add a stream handler for warmer.log
        $handlers[] = new StreamHandler(
            BP . '/var/log/warmer.log',
            $monologLevel
        );

        parent::__construct($name, $handlers, $processors);
    }
}
