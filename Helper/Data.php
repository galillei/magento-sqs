<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Helper;

use Belvg\Sqs\Model\Config;

class Data
{
    /**
     * @var Config
     */
    private $sqsConfig;

    /**
     * Data constructor.
     * @param Config $sqsConfig
     */
    public function __construct(
        Config $sqsConfig
    ) {
        $this->sqsConfig = $sqsConfig;
    }

    /**
     * Prepare queue name
     *
     * @param string $queueName
     * @param bool $addPrefix
     * @return string
     */
    public function prepareQueueName(string $queueName, $addPrefix = false)
    {
        $queueName = str_replace('.', '_', $queueName);
        if ($addPrefix) {
            $prefix = $this->sqsConfig->getValue(Config::PREFIX);
            if ($prefix) {
                $queueName = $prefix . '_' . $queueName;
            }
        }

        return $queueName;
    }
}
