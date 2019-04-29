<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Model;

use Belvg\Sqs\Helper\Data;
use Enqueue\Psr\PsrMessage;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Queue
 */
class Queue implements QueueInterface
{
    const TIMEOUT_PROCESS = 20000;

    /**
     * @var Config
     */
    private $sqsConfig;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var \Enqueue\Sqs\SqsDestination
     */
    private $queue;

    /**
     * @var \Enqueue\Sqs\SqsConsumer
     */
    private $consumer;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param EnvelopeFactory $envelopeFactory
     * @param string $queueName
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        Config $sqsConfig,
        EnvelopeFactory $envelopeFactory,
        $queueName,
        LoggerInterface $logger,
        Data $helper = null
    )
    {
        $this->sqsConfig = $sqsConfig;
        $this->queueName = $queueName;
        $this->envelopeFactory = $envelopeFactory;
        $this->logger = $logger;
        $this->helper = $helper ?: ObjectManager::getInstance()->get(Data::class);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        /**
         * @var \Enqueue\Sqs\SqsMessage $message
         */
        $message = $this->createConsumer()->receive(self::TIMEOUT_PROCESS);
        if (null !== $message) {
            $envelope = $this->createEnvelop($message);
            return $envelope;
        }
        return null;

    }

    /**
     * @return \Enqueue\Sqs\SqsConsumer
     */
    public function createConsumer()
    {
        if (!$this->consumer) {
            $this->consumer = $this->sqsConfig->getConnection()->createConsumer($this->getQueue());
        }
        return $this->consumer;
    }

    /**
     * @return \Enqueue\Sqs\SqsDestination
     */
    public function getQueue()
    {
        return $this->sqsConfig->getConnection()->createQueue($this->getQueueName());
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->helper->prepareQueueName($this->queueName, true);
    }

    /**
     * @param PsrMessage $message
     * @return \Magento\Framework\MessageQueue\Envelope
     */
    protected function createEnvelop(PsrMessage $message)
    {
        return $this->envelopeFactory->create([
            'body' => $message->getBody(),
            'properties' => [
                'properties' => $message->getProperties(),
                'receiptHandle' => $message->getReceiptHandle(),
                'topic_name' => $this->queueName,
                'message_id' => $message->getReceiptHandle()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $message = $this->createMessage($envelope);
        $this->createConsumer()->acknowledge($message);
        // @codingStandardsIgnoreEnd
    }

    /**
     * It's possible to use after* plugin to set `message_group_id` if your queue type is FIFO:
     * $message->setMessageGroupId($groupId)
     *
     * @param EnvelopeInterface $envelopereceiptHandle
     * @return \Enqueue\Sqs\SqsMessage
     */
    public function createMessage(EnvelopeInterface $envelope)
    {
        $mergerProperties = $envelope->getProperties();
        $properties = array_key_exists('properties', $mergerProperties) ? $mergerProperties['properties'] : [];
        $receiptHandler = array_key_exists('receiptHandle', $mergerProperties) ? $mergerProperties['receiptHandle'] : null;
        $message = $this->sqsConfig->getConnection()->createMessage($envelope->getBody(), $properties);
        if ($receiptHandler) {
            $message->setReceiptHandle($receiptHandler);
        }
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe($callback, int $qtyOfMessages = null)
    {

        $index = 0;
        while (true) {
            /**
             * @var \Enqueue\Sqs\SqsMessage $message
             */
            if ($message = $this->createConsumer()->receive(self::TIMEOUT_PROCESS)) {
                $index++;
                $envelope = $this->createEnvelop($message);

                if ($callback instanceof \Closure) {
                    $callback($envelope);
                } else {
                    call_user_func($callback, $envelope);
                }
                //$this->createConsumer()->acknowledge($message);
                if (null !== $qtyOfMessages && $index >= $qtyOfMessages) {
                    break;
                }
            }
        }
    }

    /**
     * (@inheritdoc)
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null)
    {
        $message = $this->createMessage($envelope);
        $consumer = $this->createConsumer();
        $consumer->reject($message, $requeue);
    }

    /**
     * (@inheritdoc)
     */
    public function push(EnvelopeInterface $envelope)
    {
        $message = $this->createMessage($envelope);
        $this->sqsConfig->getConnection()->createProducer()->send($this->getQueue(), $message);
    }

    /**
     * @return \Enqueue\Sqs\SqsContext
     */
    public function getConnection()
    {
        return $this->sqsConfig->getConnection();
    }

}
