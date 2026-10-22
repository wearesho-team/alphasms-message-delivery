<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wearesho\Delivery\AlphaSms\Service;
use Wearesho\Delivery\AlphaSms\TelegramOtp;
use Wearesho\Delivery\Exception;

class TelegramOtpCommand extends Command
{
    public function __construct(
        private readonly Service $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram-otp')
            ->setDescription('Send an OTP code via Telegram')
            ->addArgument(
                'phone',
                InputArgument::REQUIRED,
                'Phone number to send the OTP code to'
            )
            ->addOption(
                'code',
                'c',
                InputOption::VALUE_REQUIRED,
                'OTP code to send (4-8 digits), random 6 digits by default',
                null
            )
            ->addOption(
                'id',
                'i',
                InputOption::VALUE_REQUIRED,
                'Custom ID for the request',
                null
            )
            ->addOption(
                'lifetime',
                'l',
                InputOption::VALUE_REQUIRED,
                'Telegram message lifetime in seconds (min 60)',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $phone = $input->getArgument('phone');
        $code = $input->getOption('code') ?? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $id = $input->getOption('id') ? (int) $input->getOption('id') : random_int(10000, 99999);
        $lifetime = $input->getOption('lifetime') ? (int) $input->getOption('lifetime') : null;

        $io->title('AlphaSMS Telegram OTP');
        $io->text("Sending Telegram OTP to: $phone");
        $io->text("Request ID: $id");
        $io->text("Code: $code");

        try {
            $request = new TelegramOtp\Request($id, $phone, $code, $lifetime);
            $response = $this->service->telegramOtp($request);

            $io->success([
                'Telegram OTP sent successfully!',
                'Message ID: ' . $response->messageId(),
            ]);

            return Command::SUCCESS;
        } catch (\InvalidArgumentException $e) {
            $io->error('Invalid request: ' . $e->getMessage());

            return Command::INVALID;
        } catch (Exception $e) {
            $io->error('Failed to send Telegram OTP: ' . $e->getMessage());

            if ($output->isVerbose()) {
                $io->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error('Unexpected error: ' . $e->getMessage());

            if ($output->isVerbose()) {
                $io->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}
