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
use Wearesho\Delivery\AlphaSms\VoiceOtp;
use Wearesho\Delivery\Exception;

class VoiceOtpCommand extends Command
{
    public function __construct(
        private readonly Service $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('voice-otp')
            ->setDescription('Send a voice OTP code')
            ->addArgument(
                'phone',
                InputArgument::REQUIRED,
                'Phone number to send the OTP code to'
            )
            ->addOption(
                'id',
                'i',
                InputOption::VALUE_REQUIRED,
                'Custom ID for the request',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $phone = $input->getArgument('phone');
        $id = $input->getOption('id') ? (int) $input->getOption('id') : random_int(10000, 99999);

        $io->title('AlphaSMS Voice OTP');
        $io->text("Sending voice OTP to: $phone");
        $io->text("Request ID: $id");

        try {
            $request = new VoiceOtp\Request($id, $phone);
            $response = $this->service->voiceOtp($request);

            $io->success([
                'Voice OTP sent successfully!',
                'Code: ' . $response->code(),
                'Price: ' . $response->price(),
            ]);

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error('Failed to send voice OTP: ' . $e->getMessage());

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
