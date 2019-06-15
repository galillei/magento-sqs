# Magento SQS MessageQueue

The module you are able to connect to Amazon SQS from Magento to publish and consume messages. Use it like other queues in Magento 2 

Compatible with:
* Magento 2.1 EE
* Magento 2.2 Commerce
* Magento 2.3 Commerce
 
# Installation #
 
## Via composer ##
 
To install this package with composer you need access to the command line of your server and you need to have Composer. Install with the following commands:
 
```
#!bash
 
cd <your magento path>
composer require belvg/module-sqs:dev-master
php bin/magento setup:upgrade
php bin/magento cache:clean
php bin/magento setup:static-content:deploy
```
 
## Manually ##
 
To install this package manually you need access to your server file system and you need access to the command line of your server. And take the following steps:
 
* Download the zip file from the Github repository.
* Upload the contents to <your magento path>/{path name}.
* Execute the following commands:
 
```
#!bash
cd <your magento path>
php bin/magento setup:upgrade
php bin/magento cache:clean
phpbin/magento setup:static-content:deploy
```

## Configuration ##

Add the SQS queue configuration to the env.php in order to connect to Amazon SQS.
```
    'queue' => [
        'sqs' => [
            'region' => 'eu-west-1',
            'prefix' => 'development',
            'version' => 'latest',
            'access_key' => 'access_key',
            'secret_key' => 'secret_key',
            'endpoint' => 'http://localhost:4576/'
        ]
    ]
```

* region: The name of the region in Amazon to use.
* prefix: A variable to will be prefixed to the name of the queue (for example: development). 
* version: The Amazon SQS version to use.
* access_key: Your AWS access key.
* secret_key: Your AWS secret key.
* endpoint: Overwrite the region, you can specify a specific endpoint to use, i.e. for the use of SQS on Localhost.

# Usage #
 
## Publisher ##

communication.xml
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/communication.xsd">

    <!-- Topic is defined here -->
    <topic name="test_topic" request="MyCompany\MyModule\Api\Data\TopicMessageInterface" />

</config>
```

queue_publisher.xml
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">

    <!-- Setting exchange as test_topic for all messages posted to test_topic -->
    <publisher topic="test_topic">
        <connection name="sqs" exchange="test_exchange" />
    </publisher>
</config>
```

queue_topology.xml
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">

    <!-- Exchange is defined here -->
    <exchange name="test_exchange" type="topic" connection="sqs" />

</config>
```

## Consumer ##

queue_consumer.xml
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">

    <!-- Defines a consumer for our test topic -->
    <consumer name="test_queue_consumer" queue="test_topic" connection="sqs"
              consumerInstance="Magento\Framework\MessageQueue\Consumer"
              handler="MyCompany\MyModule\Model\ConsumerTest::process"/>
</config>
```

queue_topology.xml
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">

    <!-- Binds the queue test_queue to the topic test_topic -->
    <exchange name="test_exchange" connection="sqs">
        <binding id="test_binding" topic="test_topic"
                 destinationType="queue" destination="test_queue"/>
    </exchange>
</config>
```
