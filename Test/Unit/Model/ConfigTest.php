<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Test\Unit\Model;

use Belvg\Sqs\Model\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfigMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $sqsConfig;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->deploymentConfigMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')
            ->disableOriginalConstructor()
            ->setMethods(['getConfigData'])
            ->getMock();
        $this->sqsConfig = $this->objectManager->getObject(
            'Belvg\Sqs\Model\Config',
            [
                'config' => $this->deploymentConfigMock
            ]
        );
    }

    public function testGetNullConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue(null));

        $this->assertNull($this->sqsConfig->getValue(Config::REGION));
        $this->assertNull($this->sqsConfig->getValue(Config::VERSION));
        $this->assertNull($this->sqsConfig->getValue(Config::ACCESS_KEY));
        $this->assertNull($this->sqsConfig->getValue(Config::SECRET_KEY));
        $this->assertNull($this->sqsConfig->getValue(Config::PREFIX));
        $this->assertNull($this->sqsConfig->getValue(Config::ENDPOINT));
    }

    public function testGetEmptyConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue([]));

        $this->assertNull($this->sqsConfig->getValue(Config::REGION));
        $this->assertNull($this->sqsConfig->getValue(Config::VERSION));
        $this->assertNull($this->sqsConfig->getValue(Config::ACCESS_KEY));
        $this->assertNull($this->sqsConfig->getValue(Config::SECRET_KEY));
        $this->assertNull($this->sqsConfig->getValue(Config::PREFIX));
        $this->assertNull($this->sqsConfig->getValue(Config::ENDPOINT));
    }

    public function testGetStandardConfig()
    {
        $expectedRegion = 'test';
        $expectedVersion = 'latest';
        $expectedAccessKey = '123456';
        $expectedSecretKey = '123456';
        $expectedPrefix = 'prefix';
        $expectedEndpoint = 'https://localstack:4567';

        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue(
                [
                    Config::SQS_CONFIG => [
                        'region' => $expectedRegion,
                        'version' => $expectedVersion,
                        'access_key' => $expectedAccessKey,
                        'secret_key' => $expectedSecretKey,
                        'prefix' => $expectedPrefix,
                        'endpoint' => $expectedEndpoint
                    ]
                ]
            ));

        $this->assertEquals($expectedRegion, $this->sqsConfig->getValue(Config::REGION));
        $this->assertEquals($expectedVersion, $this->sqsConfig->getValue(Config::VERSION));
        $this->assertEquals($expectedAccessKey, $this->sqsConfig->getValue(Config::ACCESS_KEY));
        $this->assertEquals($expectedSecretKey, $this->sqsConfig->getValue(Config::SECRET_KEY));
        $this->assertEquals($expectedPrefix, $this->sqsConfig->getValue(Config::PREFIX));
        $this->assertEquals($expectedEndpoint, $this->sqsConfig->getValue(Config::ENDPOINT));

    }
}
