<?php

declare(strict_types=1);

namespace Killposer\IO;

use Killposer\{FileManipulator, VendorPath};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

final class OutputHandler
{
    private const MOVE_UP = 10;
    private const DELETED_LABEL = ' [DELETED] ';
    private const HEADER_TEMPLATE = <<<HEADER
<fg=magenta>      __ __ _  __ __</>
<fg=magenta>     / //_/(_)/ // /</>___  ___   ___ ___  ____
<fg=magenta>    / ,<  / // // /</>/ _ \/ _ \ (_-</ -_)/ __/
<fg=magenta>   /_/|_|/_//_//_/</>/ .__/\___//___/\__//_/        total space: %s
                 /_/                             freed space: <fg=cyan>%s</>
        
HEADER;

    /** @var OutputInterface */
    private $output;

    /** @var FileManipulator */
    private $fileManipulator;

    /** @var VendorPath[] */
    private $vendors;

    /** @var int */
    private $vendorCount;

    /** @var RowSelector */
    private $rowSelector;

    /** @var OutputFormatter */
    private $formatter;

    /** @var string */
    private $byteFormat;

    /** @var int|null */
    private $byteThreshold;

    /** @var int */
    private $totalBytes;

    /** @var string */
    private $path;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(OutputInterface $output, string $path, string $byteFormat, ?int $byteThreshold)
    {
        $this->output = $output;
        $this->byteFormat = $byteFormat;
        $this->rowSelector = new RowSelector();
        $this->formatter = new OutputFormatter(new Terminal());
        $this->fileManipulator = new FileManipulator();
        $this->path = $path;
        $this->byteThreshold = $byteThreshold;

        $this->clear();
        $this->writeLoadingScreen();
        $this->seekVendors();

        $this->vendorCount = \count($this->vendors);
        if (0 === $this->vendorCount) {
            $this->writeNotFound();
        }
    }

    public function writeOutput(?int $selectRow = null): void
    {
        $newlySelectedRow = $this->rowSelector->getSelectedRow() + (int) $selectRow;

        if ($newlySelectedRow < $this->vendorCount && $newlySelectedRow >= 0) {
            $this->rowSelector->setSelectedRow($newlySelectedRow);
        }

        $selectedRow = $this->rowSelector->getSelectedRow();
        $this->moveUp($this->vendorCount + 20);

        $message = '';
        $freedSpace = 0;
        $vendors = $this->formatter->formatRows($this->vendors, $selectedRow, 9 /* header rows */);

        foreach ($vendors as $row => $vendor) {
            if ($vendor->isEmptied()) {
                $freedSpace += $vendor->getSize();
            }

            $message .= \sprintf(
                " %s %s%s %s%s %s\n",
                $vendor->isEmptied() ? \sprintf('<fg=cyan>%s</>', self::DELETED_LABEL) : '',
                $selectedRow === $row ? '<fg=black;bg=cyan>' : '',
                $this->formatter->formatPath($vendor->getPath(), $vendor->isEmptied() ? \strlen(self::DELETED_LABEL) : 0),
                $vendor->isCutSize() ? '>' : '',
                $this->formatter->formatBytes($vendor->getSize(), $this->byteFormat),
                $selectedRow === $row ? '</>' : ''
            );
        }

        $this->writeHeader(
            $this->formatter->formatBytes($this->totalBytes, $this->byteFormat),
            $this->formatter->formatBytes($freedSpace, $this->byteFormat)
        );
        $this->writeInfoBar('Navigate with "W", "S", press "K" key to delete, "Q" to quit', 'bg=yellow;fg=black');
        $this->output->write($message);
    }

    public function kill(): void
    {
        foreach ($this->vendors as $row => $vendor) {
            if ($row === $this->rowSelector->getSelectedRow()) {
                $this->fileManipulator->free($vendor->getPath());
                $vendor->setEmptied(true);
            }
        }
    }

    private function writeInfoBar(string $text, string $format): void
    {
        $this->output->writeln($this->formatter->formatInfoBar(\sprintf('   <%s> %s  </>', $format, $text)));
    }

    private function writeHeader(string $total, string $free): void
    {
        $this->output->writeln(\sprintf(self::HEADER_TEMPLATE, $total, $free));
    }

    private function moveUp(int $lines = 0): void
    {
        $this->output->writeln(\sprintf("\033[%dA", self::MOVE_UP + $lines));
    }

    private function writeLoadingScreen(): void
    {
        $this->moveUp();
        $this->writeHeader('...', '...');
        $this->writeInfoBar('Searching and calculating sizes...', 'bg=cyan;fg=black');
    }

    private function writeNotFound(): void
    {
        $this->moveUp();
        $this->writeHeader('N/A', 'N/A');
        $this->writeInfoBar('No vendors found.', 'bg=yellow;fg=black');
        exit(0);
    }

    private function clear(): void
    {
        $this->output->write("\e[H\e[J");
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function seekVendors(): void
    {
        $this->vendors = $this->fileManipulator->seek($this->path, $this->byteThreshold);

        foreach ($this->vendors as $row => $vendor) {
            $this->totalBytes += $vendor->getSize();
        }
    }
}
