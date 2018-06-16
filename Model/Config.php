<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Model;

use Enqueue\Sqs\SqsConnectionFactory;
use Enqueue\Sqs\SqsContext;
use Magento\Framework\App\DeploymentConfig;

/**
 * Reads the SQS config in the deployed environment configuration
 */
class Config
{
    /**
     * Queue config key
     */
    const QUEUE_CONFIG = 'queue';

    /**
     * Sqs config key
     */
    const SQS_CONFIG = 'sqs';

    const REGION = 'region';
    const VERSION = 'version';
    const ACCESS_KEY = 'access_key';
    const SECRET_KEY = 'secret_key';
    const PREFIX = 'prefix';
    const ENDPOINT = 'endpoint';

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var SqsClient
     */
    private $connection;

    /**
     * @var array
     */
    private $channels = [];

    /**
     * Associative array of SQS configuration
     *
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'sqs' => [
     *             'region' => 'region',
     *             'version' => 'latest',
     *             'access_key' => '123456',
     *             'secret_key' => '123456',
     *             'prefix' => 'magento',
     *             'endpoint' => 'http://localhost:4575'
     *         ],
     *     ],
     * </code>
     *
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->deploymentConfig = $config;
    }

    /**
     * Return SQS client
     * @return SqsContext
     */
    public function getConnection()
    {
        if (!isset($this->connection)) {
            $this->connection = (new SqsConnectionFactory(
                [
                    'region' => $this->getValue(Config::REGION),
                    'key' => $this->getValue(Config::ACCESS_KEY),
                    'secret' => $this->getValue(Config::SECRET_KEY),
                    'endpoint' => $this->getValue(Config::ENDPOINT)
                ]
            ))->createContext();
        }

        return $this->connection;
    }

    /**
     * Returns the configuration set for the key.
     *
     * @param string $key
     * @return string
     */
    public function getValue($key)
    {
        $this->load();
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Load the configuration for SQS
     *
     * @return void
     */
    private function load()
    {
        if (null === $this->data) {
            $queueConfig = $this->deploymentConfig->getConfigData(self::QUEUE_CONFIG);
            $this->data = isset($queueConfig[self::SQS_CONFIG]) ? $queueConfig[self::SQS_CONFIG] : [];
        }
    }
}
