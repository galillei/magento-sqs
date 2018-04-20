<?php
namespace Belvg\Sqs\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Belvg\Sqs\Model\Topology;
use Belvg\Sqs\Console\Command\QueueMain;

class QueueCreate extends QueueMain
{
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

        $queueName = (string) $input->getOption(self::QUEUE_NAME);

        $this->topologySQS->create($queueName);

        $this->output->writeln('Queues have created. Finish (' . (microtime(true) - $start)/60 . ' min).');
    }
}