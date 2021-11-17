<?php

/*
 * This file is part of package:
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Filesystem;

final class TemporaryFilesystem
{
    /** @var string */
    private $directory;

    public function __construct(?string $targetDirectory = null)
    {
        $this->directory = rtrim($targetDirectory ?? sys_get_temp_dir(), \DIRECTORY_SEPARATOR);
    }

    public function executeWithFile(string $filename, string $content, callable $callback): void
    {
        $filepath = $this->directory . \DIRECTORY_SEPARATOR . $filename;

        if (!file_put_contents($filepath, $content)) {
            throw new \RuntimeException(sprintf('Not create file "%s"!', $filepath));
        }

        try {
            $callback($filepath);
        } finally {
            unlink($filepath);
        }
    }
}
