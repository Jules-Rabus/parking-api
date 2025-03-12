<?php

namespace App\Command;

use App\Service\MessageCasesHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-batch',
    description: 'Create an message batch',
)]
class CreateBatchCommand extends Command
{
    public function __construct(private readonly MessageCasesHandler $batchHandler)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->batchHandler->process();
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Batch created successfully');

        return Command::SUCCESS;
    }
}
