<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Test\Unit\Setup;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Belvg\Sqs\Setup\ConfigOptionsList;
use Magento\Framework\Setup\Option\TextConfigOption;

class ConfigOptionsListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigOptionsList
     */
    private $model;

    /**
     * @var \Belvg\Sqs\Setup\ConnectionValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionValidatorMock;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var array
     */
    private $options;

    protected function setUp()
    {
        $this->options = [
            ConfigOptionsList::INPUT_KEY_QUEUE_SQS_REGION => 'region',
            ConfigOptionsList::INPUT_KEY_QUEUE_SQS_VERSION => 'version',
            ConfigOptionsList::INPUT_KEY_QUEUE_SQS_ACCESS_KEY => 'access_key',
            ConfigOptionsList::INPUT_KEY_QUEUE_SQS_SECRET_KEY => 'secret_key',

        ];

        $this->objectManager = new ObjectManager($this);
        $this->connectionValidatorMock = $this->getMockBuilder('Belvg\Sqs\Setup\ConnectionValidator')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->deploymentConfigMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            'Belvg\Sqs\Setup\ConfigOptionsList',
            [
                'connectionValidator' => $this->connectionValidatorMock,
            ]
        );
    }

    public function testGetOptions()
    {
        $expectedOptions = [
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_SQS_REGION,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_SQS_REGION,
                'SQS region',
                ConfigOptionsList::DEFAULT_SQS_REGION
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_SQS_VERSION,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_SQS_VERSION,
                'SQS version',
                ConfigOptionsList::DEFAULT_SQS_VERSION
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_SQS_ACCESS_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_SQS_ACCESS_KEY,
                'SQS access key',
                ConfigOptionsList::DEFAULT_SQS_ACCESS_KEY
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_SQS_SECRET_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_SQS_SECRET_KEY,
                'SQS secret key',
                ConfigOptionsList::DEFAULT_SQS_SECRET_KEY
            )
        ];
        $this->assertEquals($expectedOptions, $this->model->getOptions());
    }

    public function testCreateConfig()
    {
        $expectedConfigData = ['queue' =>
            ['sqs' =>
                [
                    'region' => 'region',
                    'version' => 'version',
                    'access_key' => 'access_key',
                    'secret_key' => 'secret_key',
                 ]
            ]
        ];

        $result = $this->model->createConfig($this->options, $this->deploymentConfigMock);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        /** @var \Magento\Framework\Config\Data\ConfigData $configData */
        $configData = $result[0];
        $this->assertInstanceOf('Magento\Framework\Config\Data\ConfigData', $configData);
        $actualData = $configData->getData();
        $this->assertEquals($expectedConfigData, $actualData);
    }

    public function testValidateInvalidConnection()
    {
        $expectedResult = ['Could not connect to the SQS Service.'];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(false);
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

    public function testValidateValidConnection()
    {
        $expectedResult = [];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(true);
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

    public function testValidateNoOptions()
    {
        $expectedResult = [];
        $options = [];
        $this->connectionValidatorMock->expects($this->never())->method('isConnectionValid');
        $this->assertEquals($expectedResult, $this->model->validate($options, $this->deploymentConfigMock));
    }
}
