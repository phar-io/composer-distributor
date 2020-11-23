<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use RuntimeException;

final class FileNotFound extends RuntimeException
{
    public static function fromFile(string $configFile): FileNotFound
    {
        return new self(sprintf('Config file %s not found', $configFile));
    }
}
