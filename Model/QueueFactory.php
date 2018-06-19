<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Model;

/**
 * Factory class for @see \Belvg\Sqs\Model\Queue
 *
 * @api
 * @since 100.0.0
 */
class QueueFactory implements \Magento\Framework\MessageQueue\QueueFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 100.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Config $configPool,
        $instanceName = \Belvg\Sqs\Model\Queue::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     * @since 100.0.0
     */
    public function create($queueName, $connectionName = '')
    {
        return $this->objectManager->create(
            $this->instanceName,
            [
                'queueName' => $queueName
            ]
        );
    }
}
