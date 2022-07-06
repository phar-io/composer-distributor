<?php

namespace PharIo\ComposerDistributorTest\unit\Service;

use PharIo\ComposerDistributor\Service\Download;
use PharIo\ComposerDistributor\Url;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use function file_get_contents;
use function putenv;
use function sys_get_temp_dir;
use function tempnam;

class DownloadTest extends TestCase
{
    public function testDownloadWithoutProxy(): void
    {
        putenv('http_proxy=');

        $temp = new SplFileInfo(tempnam(sys_get_temp_dir(), 'tests'));

        $download = new Download(Url::fromString('https://example.org'));

        $download->toLocation($temp);

        self::assertStringContainsString('example', file_get_contents($temp->getPathname()));
    }

    public function testDownloadWithProxy(): void
    {
        putenv('http_proxy=tcp://172.16.1.184:8888');

        $temp = new SplFileInfo(tempnam(sys_get_temp_dir(), 'tests'));

        $download = new Download(Url::fromString('https://example.org'));

        $this->expectWarning();

        $download->toLocation($temp);
    }
}
