<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Model;

use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * AWS SQS connection type resolver.
 *
 * @api
 */
class ConnectionTypeResolver implements ConnectionTypeResolverInterface
{
    /**
     * AWS SQS connection names.
     *
     * @var string[]
     */
    private $sqsConnectionName = [];

    /**
     * Initialize dependencies.
     *
     * @param DeploymentConfig $deploymentConfig
     * @since 100.0.0
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $queueConfig = $deploymentConfig->getConfigData(Config::QUEUE_CONFIG);
        if (isset($queueConfig['connections']) && is_array($queueConfig['connections'])) {
            $this->sqsConnectionName = array_keys($queueConfig['connections']);
        }
        if (isset($queueConfig[Config::SQS_CONFIG])) {
            $this->sqsConnectionName[] = Config::SQS_CONFIG;
        }
    }

    /**
     * {@inheritdoc}
     * @since 100.0.0
     */
    public function getConnectionType($connectionName)
    {
        return in_array($connectionName, $this->sqsConnectionName) ? 'sqs' : null;
    }
}
