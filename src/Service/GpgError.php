<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use RuntimeException;

final class GpgError extends RuntimeException
{
    public static function verificationFailed(string $phar, string $signature): GpgError
    {
        return new self(sprintf('Unable to verify "%s" with "%s"!', $phar, $signature));
    }
}
