<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Model;

use Belvg\Sqs\Model\QueueFactory;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\Phrase;

class Exchange implements ExchangeInterface
{
    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var array
     */
    protected $queues = [];

    /**
     * Exchange constructor.
     * @param \Belvg\Sqs\Model\QueueFactory $queueFactory
     */
    public function __construct(
        QueueFactory $queueFactory
    )
    {
        $this->queueFactory = $queueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {

        $queue = $this->createQueue($topic);
        $queue->push($envelope);
        return null;
    }

    /**
     * @param $topicName same as queue name
     * @return Queue
     */
    protected function createQueue($topicName)
    {
        if (array_key_exists($topicName, $this->queues)) {
            return $this->queues[$topicName];
        }
        $this->queues[$topicName] = $this->queueFactory->create($topicName);
        return $this->queues[$topicName];
    }

}
