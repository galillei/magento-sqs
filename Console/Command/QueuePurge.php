<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueuePurge extends QueueMain
{
    protected function configure()
    {
        $this->setName('queue:belvg:sqs:purge');
        $this->setDescription('It will has purged All queues on Amazon.');

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

        $this->topologySQS->purge();

        $this->output->writeln('Queues have created. Finish (' . (microtime(true) - $start)/60 . ' min).');
    }
}