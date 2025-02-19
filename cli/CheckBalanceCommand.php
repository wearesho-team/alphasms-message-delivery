<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wearesho\Delivery\AlphaSms;
use Wearesho\Delivery\Exception;

class CheckBalanceCommand extends Command
{
    public function __construct(
        private readonly AlphaSms\Service $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setName('balance')
            ->setDescription("Check current SMS service balance.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $balance = $this->service->balance();
        } catch (Exception $exception) {
            $output->writeln("[{$exception->getCode()}] " . $exception->getMessage());
            return Command::FAILURE;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Amount', 'Currency'])
            ->setRows([[
                $balance->getAmount(),
                $balance->getCurrency() ?? 'N/A'
            ]]);
        $table->render();

        return Command::SUCCESS;
    }
}
