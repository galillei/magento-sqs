<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.6.17
 * Time: 10.40
 */

namespace Belvg\Sqs\Helper;


class Data
{
    public static function prepareQueueName(string $queueName)
    {
        return str_replace('.', '_', $queueName);
    }
}