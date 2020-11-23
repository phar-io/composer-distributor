<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use RuntimeException;

final class SomebodyElsesProblem extends RuntimeException
{
    public static function here(string $pluginName) : self
    {
        return new self(sprintf('Plugin %s was called so it\'s not our problem', $pluginName));
    }
}
