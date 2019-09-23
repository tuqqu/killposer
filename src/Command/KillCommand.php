<?php

declare(strict_types=1);

namespace Killposer\Command;

use Killposer\IO\{InputHandler, OutputHandler};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

final class KillCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('kill')
            ->setDescription('Find and delete unused composer /vendor/ directories.')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Path to search in.', '.')
            ->addOption('byte-format', 'f', InputOption::VALUE_REQUIRED, 'Bytes format: kib, mib, gib.', 'mib')
            ->addOption('byte-threshold', 't', InputOption::VALUE_REQUIRED, 'Directory size after which there is no need to keep calculating it.')
        ;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getOption('path');
        $byteFormat = $input->getOption('byte-format');
        $byteThreshold = $input->getOption('byte-threshold');

        $outputHandler = new OutputHandler($output, $path, $byteFormat, $byteThreshold);
        $outputHandler->writeOutput();

        $inputHandler = new InputHandler((static function (OutputHandler $outputHandler): \Generator {
            \readline_callback_handler_install('', static function (): void {});

            while (true) {
                if (
                    @\stream_select($read = [STDIN], $write = null, $except = null, null)
                    && \in_array(STDIN, $read, true)
                ) {
                    switch (\stream_get_contents(STDIN, 1)) {
                        case 'w':
                            $outputHandler->writeOutput(-1);
                            break;

                        case 's':
                            $outputHandler->writeOutput(+1);
                            break;

                        case 'k':
                            $outputHandler->kill();
                            $outputHandler->writeOutput(0);
                            break;

                        case 'q':
                            exit(0);
                    }
                }

                yield;
            }
        })($outputHandler));

        $inputHandler->run();
    }
}
