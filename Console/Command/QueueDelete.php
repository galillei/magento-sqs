<?php
/**
 *  @package BelVG AWS Sqs.
 *  @copyright 2018
 *
 */

namespace Belvg\Sqs\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueDelete extends QueueMain
{
    protected function configure()
    {
        $this->setName('queue:belvg:sqs:delete');
        $this->setDescription('It will has deleted All queues on Amazon.');

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

        $this->topologySQS->delete();

        $this->output->writeln('Queues have deleted. Finish (' . (microtime(true) - $start)/60 . ' min).');
    }
}