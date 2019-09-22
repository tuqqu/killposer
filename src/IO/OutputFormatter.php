<?php

declare(strict_types=1);

namespace Killposer\IO;

use Killposer\VendorPath;
use Symfony\Component\Console\Terminal;

final class OutputFormatter
{
    private const PATH_SHARE = 70;
    private const PATH_MAX = 100;
    private const BYTE_MAP = [
        'kib' => 1,
        'mib' => 2,
        'gib' => 3
    ];

    /** @var Terminal */
    private $terminal;

    /** @var int */
    private $offset = 0;
    private $diff = 0;

    public function __construct(Terminal $terminal)
    {
        $this->terminal = $terminal;
    }

    public function formatPath(string $path, int $additionalChars): string
    {
        $width = $this->getWidth();
        $pathLength = \mb_strlen($path);

        if (0 < ($diff = $pathLength - $width)) {
            $path = \substr_replace($path, '...', 7, $diff + $additionalChars);
        }

        return \str_pad($path, $width - $additionalChars + 5/* the ... length */);
    }

    public function formatBytes(int $bytes, string $format): string
    {
        if (!\array_key_exists($format, self::BYTE_MAP)) {
            throw new \InvalidArgumentException('Unknown format.');
        }

        if ($bytes < 0) {
            throw new \InvalidArgumentException('Bytes count cannot be negative.');
        }

        $bytes /= 1024 ** self::BYTE_MAP[$format];

        return \sprintf('%.2f %s', $bytes, $format);
    }

    /**
     * @param VendorPath[] $rows
     * @return VendorPath[]
     */
    public function formatRows(array $rows, int $selectedRow, int $header): array
    {
        $height = $this->terminal->getHeight() - $header;
        $rowCount = \count($rows);
        $newDiff = $height - $selectedRow;

        if ($newDiff > 0) {
            $this->offset = 0;
        } elseif ($newDiff !== $this->diff) {
            $newDiff < $this->diff ? ++$this->offset : --$this->offset;
        }
        $this->diff = $newDiff;

        return $rowCount > $height ? \array_slice($rows, $this->offset, $height, true) : $rows;
    }

    public function formatInfoBar(string $bar): string
    {
        return \str_pad($bar, $this->getWidth());
    }

    private function getWidth(): int
    {
        $width = \floor($this->terminal->getWidth() * self::PATH_SHARE / 100);

        if ($width > self::PATH_MAX) {
            $width = self::PATH_MAX;
        }

        return (int) $width;
    }
}
