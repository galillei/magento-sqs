<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Helper;

class Data
{
    /**
     * Prepare queue name
     *
     * @param string $queueName
     * @return mixed
     */
    public static function prepareQueueName(string $queueName)
    {
        return str_replace('.', '_', $queueName);
    }
}