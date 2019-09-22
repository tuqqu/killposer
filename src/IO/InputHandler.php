<?php

declare(strict_types=1);

namespace Killposer\IO;

final class InputHandler
{
    /** @var \Generator */
    private $coroutine;

    /** @var bool */
    private $yielded = false;

    public function __construct(\Generator $coroutine)
    {
        $this->coroutine = $coroutine;
    }

    public function run(): void
    {
        while (true) {
            $this->read();
        }
    }

    public function read(): void
    {
        if ($this->yielded) {
            $this->coroutine->send(null);

            return;
        }

        $this->yielded = true;
    }
}
