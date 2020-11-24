<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use RuntimeException;

final class NoSemanticVersioning extends RuntimeException
{
    public static function fromVersionString(string $version) : self
    {
        return new self(sprintf('The version string "%s" does not follow semantic versioning', $version));
    }
}
