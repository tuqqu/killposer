<?php

declare(strict_types=1);

namespace Killposer\Tests;

use Killposer\FileManipulator;
use Killposer\VendorPath;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Killposer\FileManipulator
 */
final class FileManipulatorTest extends TestCase
{
    private const PATH = __DIR__ . '/fixtures';

    /**
     * @covers \Killposer\FileManipulator::seek
     */
    public function testValidSeekNoThreshold(): void
    {
        $fileManipulator = new FileManipulator();

        $vendors = $fileManipulator->seek(self::PATH, null);
        \usort($vendors, static function (VendorPath $a, VendorPath $b): int {
            return $a->getSize() <=> $b->getSize();
        });

        self::assertCount(3, $vendors);

        self::assertStringEndsWith('tests/fixtures/vendor', $vendors[0]->getPath());
        self::assertEquals(0, $vendors[0]->getSize());
        self::assertFalse($vendors[0]->isCutSize());

        self::assertStringEndsWith('tests/fixtures/vendor_parent_1/vendor', $vendors[1]->getPath());
        self::assertEquals(1146, $vendors[1]->getSize());
        self::assertFalse($vendors[1]->isCutSize());

        self::assertStringEndsWith('tests/fixtures/vendor_parent_2/vendor', $vendors[2]->getPath());
        self::assertEquals(2691, $vendors[2]->getSize());
        self::assertFalse($vendors[2]->isCutSize());

        $vendors = $fileManipulator->seek(self::PATH, 300);

        self::assertEquals(816, $vendors[1]->getSize());
        self::assertTrue($vendors[1]->isCutSize());
    }

    /**
     * @covers \Killposer\FileManipulator::seek
     */
    public function testInvalidSeek(): void
    {
        $fileManipulator = new FileManipulator();

        $this->expectException(\InvalidArgumentException::class);
        $fileManipulator->seek(self::PATH . '/non/existent', null);
    }

    /**
     * @covers \Killposer\FileManipulator::free
     */
    public function testFree(): void
    {
        $removePath = self::PATH . '/for_delete';
        $vendorPath = $removePath . '/vendor';
        $filePath = $vendorPath . '/to_be_removed.txt';

        $filesystem = new Filesystem();
        $filesystem->mkdir($vendorPath);
        $filesystem->dumpFile($filePath, 'Good Bye');

        self::assertDirectoryExists($removePath);
        self::assertDirectoryExists($vendorPath);
        self::assertFileExists($filePath);

        $fileManipulator = new FileManipulator();
        $fileManipulator->free($vendorPath);

        self::assertDirectoryExists($removePath);
        self::assertDirectoryNotExists($vendorPath);
        self::assertFileNotExists($filePath);
    }
}
