<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Model;

/**
 * Factory class for @see \Belvg\Sqs\Model\Exchange
 *
 * @api
 */
class ExchangeFactory implements \Magento\Framework\MessageQueue\ExchangeFactoryInterface
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
     * @param ConfigPool $configPool
     * @param string $instanceName
     * @since 100.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Belvg\Sqs\Model\Exchange::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     * @since 100.0.0
     */
    public function create($connectionName, array $data = [])
    {
        return $this->objectManager->create($this->instanceName);
    }
}
