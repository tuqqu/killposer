<?php

declare(strict_types=1);

namespace Killposer\Tests\IO;

use Killposer\IO\OutputFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Terminal;

/**
 * @covers \Killposer\IO\OutputFormatter
 */
final class OutputFormatterTest extends TestCase
{
    /**
     * @covers \Killposer\IO\OutputFormatter::formatPath
     * @dataProvider providePaths
     */
    public function testFormatPath(string $path, string $expectedPath, int $additionalChars, int $width): void
    {
        $outputFormatter = new OutputFormatter(new class($width) extends Terminal {
            /** @var int */
            private $width;

            public function __construct(int $width)
            {
                $this->width = $width;
            }

            public function getWidth(): int
            {
                return $this->width;
            }
        });
        $formattedPath = $outputFormatter->formatPath($path, $additionalChars);

        self::assertEquals($formattedPath, $expectedPath);
    }

    /**
     * @covers \Killposer\IO\OutputFormatter::formatBytes
     * @dataProvider provideValidBytes
     */
    public function testFormatValidBytes(int $bytes, string $format, string $expectedString): void
    {
        $outputFormatter = new OutputFormatter(new Terminal());

        self::assertEquals($outputFormatter->formatBytes($bytes, $format), $expectedString);
    }

    /**
     * @covers \Killposer\IO\OutputFormatter::formatBytes
     * @dataProvider provideInvalidBytes
     */
    public function testFormatInvalidBytes(int $bytes, string $format): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $outputFormatter = new OutputFormatter(new Terminal());
        $outputFormatter->formatBytes($bytes, $format);
    }

    /**
     * @covers \Killposer\IO\OutputFormatter::formatInfoBar
     * @dataProvider provideBars
     */
    public function testFormatBar(string $bar, string $expectedBar): void
    {
        $outputFormatter = new OutputFormatter(new Terminal());
        $bar = $outputFormatter->formatInfoBar($bar);

        self::assertEquals($bar, $expectedBar);
    }

    public function providePaths(): array
    {
        return [
            [
                // no shrinkage
                '/usr/bin/not/a/long/path',
                '/usr/bin/not/a/long/path         ',
                0,
                40,
            ],
            [
                // same with additional chars
                '/usr/bin/not/a/long/path',
                '/usr/bin/not/a/long/path      ',
                10,
                50,
            ],
            [
                // with shrinkage
                '/usr/bin/quite/a/long/path/to/be/honest/with/you',
                '/usr/bi...g/path/to/be/honest/with/you  ',
                0,
                50,
            ],
            [
                // with shrinkage and additional chars
                '/usr/bin/quite/a/long/path/to/be/honest/with/you',
                '/usr/bi...with/you  ',
                20,
                50,
            ],
            [
                '/usr/bin/very/long/long/not/int/but/path/to/be/honest/with/you',
                '/usr/bi.../long/not/int/but/path/to/be/honest/with/you  ',
                5,
                80,
            ],
        ];
    }

    public function provideValidBytes(): array
    {
        return [
            [20000, 'kib', '19.53 kib'],
            [500000000, 'kib', '488281.25 kib'],
            [0, 'kib', '0.00 kib'],
            [150000000, 'mib', '143.05 mib'],
            [60000, 'mib', '0.06 mib'],
            [0, 'mib', '0.00 mib'],
            [100000000000, 'gib', '93.13 gib'],
            [0, 'gib', '0.00 gib'],
        ];
    }

    public function provideInvalidBytes(): array
    {
        return [
            [-100, 'kib'],
            [-9, 'mib'],
            [-1000000, 'gib'],
        ];
    }

    public function provideBars(): array
    {
        return [
            [
                'This is a short info bar',
                'This is a short info bar                                '
            ],
            [
                'And this one is rather long info bar, is it not? Anyways it is longer than the previous one',
                'And this one is rather long info bar, is it not? Anyways it is longer than the previous one'
            ]
        ];
    }
}
