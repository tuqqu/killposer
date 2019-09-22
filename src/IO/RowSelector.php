<?php

declare(strict_types=1);

namespace Killposer\IO;

final class RowSelector
{
    /** @var int|null */
    private $selectedRow;

    public function getSelectedRow(): ?int
    {
        return $this->selectedRow;
    }

    public function setSelectedRow(int $selectedRow): void
    {
        $this->selectedRow = $selectedRow;
    }
}
