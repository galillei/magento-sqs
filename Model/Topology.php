<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Model;

use Belvg\Sqs\Helper\Data;
use Magento\Framework\App\ObjectManager;
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
     * @var Data
     */
    private $helper;

    /**
     * Topology constructor.
     * @param Config $sqsConfig
     * @param QueueConfig $queueConfig
     * @param CommunicationConfig $communicationConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        Config $sqsConfig,
        QueueConfig $queueConfig,
        CommunicationConfig $communicationConfig,
        \Psr\Log\LoggerInterface $logger,
        Data $helper = null
    )
    {
        $this->sqsConfig = $sqsConfig;
        $this->queueConfig = $queueConfig;
        $this->communicationConfig = $communicationConfig;
        $this->logger = $logger;
        $this->helper = $helper ?: ObjectManager::getInstance()->get(Data::class);
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
                            'There is a problem with creating or binding queue "%s" and an exchange "%s". Error: %s',
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
     * Create SQS Queues
     *
     * @return void
     */
    public function create($queueName = '')
    {
        if ($queueName) {
            $availableQueues[] = $queueName;
        } else {
            $availableQueues = $this->getQueuesList(self::SQS_CONNECTION);
        }

        foreach ($availableQueues as $queue) {
            try {
                $this->declareQueue($queue);
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf(
                        'There is a problem with creating queue "%s". Error: %s',
                        $queue,
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * Delete SQS Queues
     *
     * @return void
     */
    public function delete($queueName = '')
    {
        if ($queueName) {
            $availableQueues[] = $queueName;
        } else {
            $availableQueues = $this->getQueuesList(self::SQS_CONNECTION);
        }

        foreach ($availableQueues as $queue) {
                try {
                    $this->deleteQueue($queue);
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            'There is a problem with removing queue "%s". Error: %s',
                            $queue,
                            $e->getMessage()
                        )
                    );
                }
            }
    }

    /**
     * Purge SQS Queues
     *
     * @return void
     */
    public function purge($queueName = '')
    {
        if ($queueName) {
            $availableQueues[] = $queueName;
        } else {
            $availableQueues = $this->getQueuesList(self::SQS_CONNECTION);
        }

        foreach ($availableQueues as $queue) {
                try {
                    $this->purgeQueue($queue);
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            'There is a problem with purging queue "%s". Error: %s',
                            $queue,
                            $e->getMessage()
                        )
                    );
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
     * Delete SQS Queue
     *
     * @param string $queueName
     * @return void
     */
    private function deleteQueue($queueName)
    {
        $sqsQueueName = $this->getConnection()->createQueue($this->getQueueName($queueName));
        $this->getConnection()->deleteQueue($sqsQueueName);
    }

    /**
     * Purge SQS Queue
     *
     * @param string $queueName
     * @return void
     */
    private function purgeQueue($queueName)
    {
        $sqsQueueName = $this->getConnection()->createQueue($this->getQueueName($queueName));
        $this->getConnection()->purge($sqsQueueName);
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

    /**
     * @param $queueName
     * @return mixed
     */
    protected function getQueueName($queueName)
    {
        return $this->helper->prepareQueueName($queueName, true);
    }
}
