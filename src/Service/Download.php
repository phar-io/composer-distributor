<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use PharIo\ComposerDistributor\Url;
use SplFileInfo;
use function feof;
use function fwrite;

final class Download
{
    private $url;

    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    public function toLocation(SplFileInfo $downloadLocation) : void
    {
        $source = fopen($this->url->toString(), 'r');
        $target = fopen($downloadLocation->getPathname(), 'w');
        while (!feof($source)) {
            fwrite($target, fread($source, 1024));
        }
        fclose($source);
        fclose($target);
    }
}
