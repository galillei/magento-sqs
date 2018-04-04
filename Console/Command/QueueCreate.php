<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */
namespace Belvg\Sqs\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Belvg\Sqs\Model\Topology;

class QueueCreate extends Command
{
    /**
     * @var Topology
     */
    private $topologySQS;

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
        $this->setName('queue:belvg:sqs:create');
        $this->setDescription('It will has created queues on Amazon.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $start = microtime(true);

        $this->topologySQS->install();

        $this->output->writeln('Queues have created. Finish (' . (microtime(true) - $start)/60 . ' min).');
    }
}