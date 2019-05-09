<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Test\Unit\Model;

use Belvg\Sqs\Model\Config;
use Belvg\Sqs\Model\Queue;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class QueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Queue
     */
    private $topology;

    /**
     * @var \Enqueue\Sqs\SqsContext
     */
    private $context;

    /**
     * @var \Enqueue\Sqs\SqsDestination
     */
    private $consumer;

    /**
     * @var \Enqueue\Sqs\SqsDestination
     */
    private $destination;

    /**
     * @var \Enqueue\Sqs\SqsMessage
     */
    private $message;

    /**
     * @var Config
     */
    private $sqsConfig;

    const QUEUE_NAME = 'testqueue';

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->context = $this->getMockBuilder(\Enqueue\Sqs\SqsContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['createQueue','createMessage','createConsumer'])
            ->getMock();

        $this->consumer = $this->getMockBuilder(\Enqueue\Sqs\SqsConsumer::class)
            ->disableOriginalConstructor()
            ->setMethods(['acknowledge','receive'])
            ->getMock();

        $this->destination = $this->getMockBuilder(\Enqueue\Sqs\SqsDestination::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sqsConfig = $this->getMockBuilder(\Belvg\Sqs\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\Envelope::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->message = $this->getMockBuilder(\Enqueue\Sqs\SqsMessage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = $this->objectManager->getObject('Belvg\Sqs\Helper\Data');

        $this->queue = $this->objectManager->getObject(
            'Belvg\Sqs\Model\Queue',
            [
                'sqsConfig' => $this->sqsConfig,
                'queueName' => self::QUEUE_NAME,
                'helper' => $helper
            ]
        );
    }

    public function testAcknowledge()
    {
        $this->sqsConfig->expects($this->exactly(3))
            ->method('getConnection')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('createQueue')
            ->with(self::QUEUE_NAME)
            ->willReturn($this->destination);

        $this->context->expects($this->once())
            ->method('createMessage')
            ->willReturn($this->message);

        $this->context->expects($this->once())
            ->method('createConsumer')
            ->willReturn($this->consumer);

        $this->envelope->expects($this->once())
            ->method('getProperties')
            ->willReturn([]);

        $this->queue->acknowledge($this->envelope);
    }

    public function testDequeue()
    {
        $this->sqsConfig->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('createQueue')
            ->with(self::QUEUE_NAME)
            ->willReturn($this->destination);

        $this->context->expects($this->once())
            ->method('createConsumer')
            ->willReturn($this->consumer);

        $this->assertEquals($this->queue->dequeue(), null);
    }
}
