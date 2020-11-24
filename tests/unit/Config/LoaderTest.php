<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributorTest\Config;

use PharIo\ComposerDistributor\Config\FileNotFound;
use PharIo\ComposerDistributor\Config\InvalidXML;
use PharIo\ComposerDistributor\Config\Loader;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
   public function testLoadConfigSuccess(): void
   {
       $config = Loader::loadFile(__DIR__ . '/../../_assets/config/valid-config.xml');

       self::assertEquals('phar-io/phive', $config->package());
       self::assertTrue(!is_null($config->keyDirectory()));
   }

   public function testConfigDoesNotExist(): void
   {
       self::expectException(FileNotFound::class);
       Loader::loadFile('foo.txt');
   }

    public function testLoadInvalidXml(): void
    {
        self::expectException(InvalidXML::class);
        Loader::loadFile(__DIR__ . '/../../_assets/config/invalid.xml');
    }
}
