<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Setup;

use Aws\Sqs\SqsClient;

/**
 * Class ConnectionValidator - validates SQS related settings
 */
class ConnectionValidator
{
    /**
     * Checks SQS Connection
     *
     * @param string $region
     * @param string $version
     * @param string $access_key
     * @param string $secret_key
     * @return bool true if the connection succeeded, false otherwise
     */
    public function isConnectionValid($region, $version, $access_key, $secret_key)
    {
        try {
            $connection = new SqsClient([
                'region' => $region,
                'version' => $version,
                'credentials' => [
                    'key' => $access_key,
                    'secret' => $secret_key,
                ]
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
