<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use PharIo\ComposerDistributor\FileList;
use RuntimeException;

class Config
{
    /** @var string */
    private $package;

    /** @var string|null */
    private $keyDirectory;

    /** @var \PharIo\ComposerDistributor\FileList */
    private $phars;

    public function __construct(string $package, FileList $phars, ?string $keyDir = null)
    {
        if (strpos($package, '/') === false) {
            throw new RuntimeException('Invalid package name');
        }
        $this->package      = $package;
        $this->phars        = $phars;
        $this->keyDirectory = $keyDir;
    }

    public function package(): string
    {
        return $this->package;
    }

    public function keyDirectory(): ?string
    {
        return $this->keyDirectory;
    }

    public function phars(): FileList
    {
        return $this->phars;
    }
}
