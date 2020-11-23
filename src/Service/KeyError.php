<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use RuntimeException;

final class KeyError extends RuntimeException
{
    public static function importFailure(): KeyError
    {
        return new self('Could not import required GPG key!');
    }
}
