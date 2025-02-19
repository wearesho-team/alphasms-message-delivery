<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wearesho\Delivery\AlphaSms;
use Wearesho\Delivery;

class SendMessageCommand extends Command
{
    public function __construct(
        private readonly AlphaSms\Service $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('send')
            ->setDescription('Send SMS messages interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $messages = [];

        $io->title('Interactive SMS Sender');

        try {
            do {
                // Ask for recipient
                $recipientQuestion = new Question('Enter recipient phone number: ');
                $recipientQuestion->setValidator(function ($value) {
                    if (trim($value) === '') {
                        throw new \Exception('The phone number cannot be empty');
                    }
                    // Add more phone number validation if needed
                    return $value;
                });
                $recipient = $helper->ask($input, $output, $recipientQuestion);

                // Ask for message text
                $messageQuestion = new Question('Enter message text: ');
                $messageQuestion->setValidator(function ($value) {
                    if (trim($value) === '') {
                        throw new \Exception('The message cannot be empty');
                    }
                    return $value;
                });
                $text = $helper->ask($input, $output, $messageQuestion);

                // Create message object
                $messages[] = new Delivery\Message($text, $recipient);

                // Ask if user wants to send another message
                $continue = $io->confirm('Do you want to send another message?', false);
            } while ($continue);

            // Show summary before sending
            $io->section('Message Summary');
            $io->table(
                ['Recipient', 'Message'],
                array_map(fn($msg) => [
                    $msg->getRecipient(),
                    $msg->getText()
                ], $messages)
            );

            if (!$io->confirm('Do you want to proceed with sending these messages?', true)) {
                $io->warning('Operation cancelled by user');
                return Command::SUCCESS;
            }

            // Send messages
            $io->section('Sending Messages');
            $io->progressStart(count($messages));

            $results = $this->service->batch($messages);
            $io->progressFinish();

            // Show results
            $io->section('Results');
            $resultRows = [];
            foreach ($results as $result) {
                $status = $result->status();
                $statusText = $status->value;
                $statusStyle = $status->isSuccess() ? 'fg=green' : 'fg=red';

                $resultRows[] = [
                    $result->messageId(),
                    $result->message()->getRecipient(),
                    "<$statusStyle>$statusText</>",
                    $result->reason() ?? 'N/A'
                ];
            }

            $io->table(
                ['Message ID', 'Recipient', 'Status', 'Reason'],
                $resultRows
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
