<?php

namespace Belvg\Sqs\Model;

use Belvg\Sqs\Helper\Data;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * Class Topology creates topology for Amqp messaging
 *
 */
class Topology
{
    /**
     * Type of exchange
     */
    const TOPIC_EXCHANGE = 'topic';

    /**
     * SQS connection
     */
    const SQS_CONNECTION = 'sqs';
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var Config
     */
    private $sqsConfig;
    /**
     * @var QueueConfig
     */
    private $queueConfig;
    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * Topology constructor.
     * @param Config $sqsConfig
     * @param QueueConfig $queueConfig
     * @param CommunicationConfig $communicationConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Config $sqsConfig,
        QueueConfig $queueConfig,
        CommunicationConfig $communicationConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->sqsConfig = $sqsConfig;
        $this->queueConfig = $queueConfig;
        $this->communicationConfig = $communicationConfig;
        $this->logger = $logger;
    }

    /**
     * Install SQS Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install()
    {
        $availableQueues = $this->getQueuesList(self::SQS_CONNECTION);
        $availableExchanges = $this->getExchangesList(self::SQS_CONNECTION);
        foreach ($this->queueConfig->getBinds() as $bind) {
            $queueName = $bind[QueueConfig::BIND_QUEUE];
            $exchangeName = $bind[QueueConfig::BIND_EXCHANGE];
            if (in_array($queueName, $availableQueues) && in_array($exchangeName, $availableExchanges)) {
                try {
                    $this->declareQueue($queueName);
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            'There is a problem with creating or binding queue "%s" and an exchange "%s". Error:%s',
                            $queueName,
                            $exchangeName,
                            $e->getTraceAsString()
                        )
                    );
                }
            }
        }
    }

    /**
     * Return list of queue names, that are available for connection
     *
     * @param string $connection
     * @return array List of queue names
     */
    private function getQueuesList($connection)
    {
        $queues = [];
        foreach ($this->queueConfig->getConsumers() as $consumer) {
            if ($consumer[QueueConfig::CONSUMER_CONNECTION] === $connection) {
                $queues[] = $consumer[QueueConfig::CONSUMER_QUEUE];
            }
        }
        foreach (array_keys($this->communicationConfig->getTopics()) as $topicName) {
            if ($this->queueConfig->getConnectionByTopic($topicName) === $connection) {
                $queues = array_merge($queues, $this->queueConfig->getQueuesByTopic($topicName));
            }
        }
        $queues = array_unique($queues);
        return $queues;
    }

    /**
     * Return list of exchange names, that are available for connection
     *
     * @param string $connection
     * @return array List of exchange names
     */
    private function getExchangesList($connection)
    {
        $exchanges = [];
        $queueConfig = $this->queueConfig->getPublishers();
        foreach ($queueConfig as $publisher) {
            if ($publisher[QueueConfig::PUBLISHER_CONNECTION] === $connection) {
                $exchanges[] = $publisher[QueueConfig::PUBLISHER_EXCHANGE];
            }
        }
        $exchanges = array_unique($exchanges);
        return $exchanges;
    }

    /**
     * Declare SQS Queue
     *
     * @param string $queueName
     * @return void
     */
    private function declareQueue($queueName)
    {
        $sqsQueueName = $this->getConnection()->createQueue($this->getQueueName($queueName));
        $this->getConnection()->declareQueue($sqsQueueName);
    }

    /**
     * Return SQS connection
     *
     * @return \Enqueue\Sqs\SqsContext
     */
    private function getConnection()
    {
        return $this->sqsConfig->getConnection();
    }

    protected function getQueueName($queueName)
    {
        return Data::prepareQueueName($queueName);
    }
}