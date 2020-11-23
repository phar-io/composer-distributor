<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributorTest\Config;

use DOMDocument;
use PharIo\ComposerDistributor\Config\Mapper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MapperTest extends TestCase
{
   public function testLoadConfigSuccessfully(): void
   {
       $contents = file_get_contents(__DIR__ . '/../../_assets/config/valid-config.xml');
       $document = new DOMDocument;
       $document->loadXML($contents);

       $mapper = new Mapper();
       $config = $mapper->createConfig($document);

       self::assertEquals('phar-io/phive', $config->package());
       self::assertTrue(!is_null($config->keyDirectory()));
   }

   public function testLoadInvalidConfigFailure(): void
   {
       $contents  = file_get_contents(__DIR__ . '/../../_assets/config/invalid-config.xml');
       $document  = new DOMDocument;
       $document->loadXML($contents);

       self::expectException(RuntimeException::class);

       $mapper = new Mapper();
       $mapper->createConfig($document);
   }
}
