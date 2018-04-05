<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Belvg\Sqs\Model\Topology;

class QueueMain extends Command
{
    const QUEUE_NAME = 'queueName';

    /**
     * @var Topology
     */
    protected $topologySQS;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructor.
     */
    public function __construct(
        Topology $topology
    )
    {
        $this->topologySQS = $topology;

        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                self::QUEUE_NAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'Queue Name'
            ),

        ];

        $this->setDefinition($options);

        parent::configure();
    }
}