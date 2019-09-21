<?php

declare(strict_types=1);

namespace Killposer;

final class VendorPath
{
    /** @var string */
    private $path;

    /** @var int */
    private $size;

    /** @var bool */
    private $emptied = false;

    /** @var bool */
    private $cutSize;

    public function __construct(string $path, int $size, bool $cutSize)
    {
        $this->path = $path;
        $this->size = $size;
        $this->cutSize = $cutSize;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setEmptied(bool $emptied): void
    {
        $this->emptied = $emptied;
    }

    public function isEmptied(): bool
    {
        return $this->emptied;
    }

    public function isCutSize(): bool
    {
        return $this->cutSize;
    }
}
