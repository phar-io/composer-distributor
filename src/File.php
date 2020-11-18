<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

final class File
{
    /** @var string  */
    private $name;

    /** @var \PharIo\ComposerDistributor\Url */
    private $pharLocation;

    /** @var \PharIo\ComposerDistributor\Url|null */
    private $signatureLocation;

    public function __construct(string $name, Url $pharLocation, ?Url $signatureLocation = null)
    {
        $this->name              = $name;
        $this->pharLocation      = $pharLocation;
        $this->signatureLocation = $signatureLocation;
    }

    public function pharName() : string
    {
        return $this->name;
    }

    public function pharUrl() : Url
    {
        return $this->pharLocation;
    }

    public function signatureUrl() : ?Url
    {
        return $this->signatureLocation;
    }
}
