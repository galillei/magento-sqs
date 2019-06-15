<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Test\Unit\Model;

use Belvg\Sqs\Model\Config;
use Belvg\Sqs\Model\Topology;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TopologyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Topology
     */
    private $topology;

    /**
     * @var \Enqueue\Sqs\SqsContext
     */
    private $context;

    /**
     * @var \Enqueue\Sqs\SqsDestination
     */
    private $destination;

    const QUEUE_NAME = 'testqueue';

    /**
     * @var Config
     */
    private $sqsConfig;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->context = $this->getMockBuilder(\Enqueue\Sqs\SqsContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['createQueue','declareQueue','deleteQueue','purge'])
            ->getMock();

        $this->destination = $this->getMockBuilder(\Enqueue\Sqs\SqsDestination::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sqsConfig = $this->getMockBuilder(\Belvg\Sqs\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = $this->objectManager->getObject('Belvg\Sqs\Helper\Data');

        $this->topology = $this->objectManager->getObject(
            'Belvg\Sqs\Model\Topology',
            [
                'sqsConfig' => $this->sqsConfig,
                'helper' => $helper
            ]
        );
    }

    public function testCreateQueue()
    {
        $this->sqsConfig->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('createQueue')
            ->with(self::QUEUE_NAME)
            ->willReturn($this->destination);

        $this->context->expects($this->once())
            ->method('declareQueue')
            ->with($this->destination);

        $this->topology->create(self::QUEUE_NAME);
    }

    public function testDeleteQueue()
    {
        $this->sqsConfig->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('createQueue')
            ->with(self::QUEUE_NAME)
            ->willReturn($this->destination);

        $this->context->expects($this->once())
            ->method('deleteQueue')
            ->with($this->destination);

        $this->topology->delete(self::QUEUE_NAME);
    }

    public function testPurgeQueue()
    {
        $this->sqsConfig->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('createQueue')
            ->with(self::QUEUE_NAME)
            ->willReturn($this->destination);

        $this->context->expects($this->once())
            ->method('purge')
            ->with($this->destination);

        $this->topology->purge(self::QUEUE_NAME);
    }
}
