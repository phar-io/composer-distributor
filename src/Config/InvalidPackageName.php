<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use RuntimeException;

final class InvalidPackageName extends RuntimeException
{
    public static function fromString(string $name): InvalidPackageName
    {
        return new self(sprintf('Invalid package name: %s', $name));
    }
}
