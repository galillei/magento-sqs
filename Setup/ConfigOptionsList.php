<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the options
     */
    const INPUT_KEY_QUEUE_SQS_REGION = 'sqs-region';
    const INPUT_KEY_QUEUE_SQS_VERSION = 'sqs-version';
    const INPUT_KEY_QUEUE_SQS_ACCESS_KEY = 'sqs-access-key';
    const INPUT_KEY_QUEUE_SQS_SECRET_KEY = 'sqs-secret-key';

    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_QUEUE_SQS_REGION = 'queue/sqs/region';
    const CONFIG_PATH_QUEUE_SQS_VERSION = 'queue/sqs/version';
    const CONFIG_PATH_QUEUE_SQS_ACCESS_KEY = 'queue/sqs/access_key';
    const CONFIG_PATH_QUEUE_SQS_SECRET_KEY = 'queue/sqs/secret_key';


    /**
     * Default values
     */
    const DEFAULT_SQS_REGION = 'region';
    const DEFAULT_SQS_VERSION = 'latest';
    const DEFAULT_SQS_ACCESS_KEY = '';
    const DEFAULT_SQS_SECRET_KEY = '';

    /**
     * @var ConnectionValidator
     */
    private $connectionValidator;

    /**
     * Constructor
     *
     * @param ConnectionValidator $connectionValidator
     */
    public function __construct(ConnectionValidator $connectionValidator)
    {
        $this->connectionValidator = $connectionValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_SQS_REGION,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_SQS_REGION,
                'SQS region',
                self::DEFAULT_SQS_REGION
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_SQS_VERSION,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_SQS_VERSION,
                'SQS version',
                self::DEFAULT_SQS_VERSION
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_SQS_ACCESS_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_SQS_ACCESS_KEY,
                'SQS access key',
                self::DEFAULT_SQS_ACCESS_KEY
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_SQS_SECRET_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_SQS_SECRET_KEY,
                'SQS secret key',
                self::DEFAULT_SQS_SECRET_KEY
            ),
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (isset($data[self::INPUT_KEY_QUEUE_SQS_REGION])) {
            $configData->set(self::CONFIG_PATH_QUEUE_SQS_REGION, $data[self::INPUT_KEY_QUEUE_SQS_REGION]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_SQS_VERSION])) {
            $configData->set(self::CONFIG_PATH_QUEUE_SQS_VERSION, $data[self::INPUT_KEY_QUEUE_SQS_VERSION]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_SQS_ACCESS_KEY])) {
            $configData->set(self::CONFIG_PATH_QUEUE_SQS_ACCESS_KEY, $data[self::INPUT_KEY_QUEUE_SQS_ACCESS_KEY]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_SQS_SECRET_KEY])) {
            $configData->set(self::CONFIG_PATH_QUEUE_SQS_SECRET_KEY, $data[self::INPUT_KEY_QUEUE_SQS_SECRET_KEY]);
        }

        return [$configData];
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        if (isset($options[self::INPUT_KEY_QUEUE_SQS_ACCESS_KEY])
            && $options[self::INPUT_KEY_QUEUE_SQS_SECRET_KEY] !== ''
        ) {

            $result = $this->connectionValidator->isConnectionValid(
                $options[self::INPUT_KEY_QUEUE_SQS_REGION],
                $options[self::INPUT_KEY_QUEUE_SQS_VERSION],
                $options[self::INPUT_KEY_QUEUE_SQS_ACCESS_KEY],
                $options[self::INPUT_KEY_QUEUE_SQS_SECRET_KEY]
            );

            if (!$result) {
                $errors[] = "Could not connect to the SQS Service.";
            }
        }

        return $errors;
    }
}
