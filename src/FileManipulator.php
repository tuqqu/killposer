<?php

declare(strict_types=1);

namespace Killposer;

use Symfony\Component\Filesystem\Filesystem;

final class FileManipulator
{
    private const TARGET = 'vendor';

    /** @var Filesystem */
    private $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @return VendorPath[]
     * @throws \InvalidArgumentException
     */
    public function seek(string $path, ?int $byteThreshold): array
    {
        $exists = \is_readable($path);
        if (!$exists) {
            throw new \InvalidArgumentException(\sprintf('Path "%s" is either not readable or does not exist.', $path));
        }

        $vendors = [];

        $fileIterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST,
                \RecursiveIteratorIterator::CATCH_GET_CHILD
            ),
            \sprintf('/%s$/', self::TARGET)
        );

        foreach ($fileIterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDir() && self::TARGET === $file->getFilename()) {
                $vendors[] = new VendorPath(
                    $absolutePath = $file->getRealPath(),
                    ...$this->calculateSize($absolutePath, $byteThreshold)
                );
            }
        }

        return $vendors;
    }

    public function free(string $path): void
    {
        $this->filesystem->remove($path);
    }

    private function calculateSize(string $path, ?int $threshold): array
    {
        $fileIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $size = 0;

        foreach ($fileIterator as $file) {
            /** @var \SplFileInfo $file */
            try {
                $size += $file->getSize();

                if (null !== $threshold && $size > $threshold) {
                    return [$size, true];
                }
            } catch (\RuntimeException $exception) {
            }
        }

        return [$size, false];
    }
}
