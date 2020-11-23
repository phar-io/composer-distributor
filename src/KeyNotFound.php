<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use RuntimeException;

final class KeyNotFound extends RuntimeException
{
    public static function fromInvalidPath(string $path) : self
    {
        return new self(sprintf('Invalid key location "%s"', $path));
    }
}
