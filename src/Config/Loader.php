<?php

namespace PharIo\ComposerDistributor\Config;

use DOMDocument;

class Loader
{
    public static function loadFile(string $configFile): Config
    {
        if (!is_file($configFile)) {
            throw FileNotFound::fromFile($configFile);
        }
        $domDocument = self::loadXmlFile($configFile);
        $mapper = new Mapper();
        return $mapper->createConfig($domDocument);
    }

    private static function loadXmlFile(string $filename): DOMDocument
    {
        $contents  = file_get_contents($filename);
        $document  = new DOMDocument;
        $internal  = libxml_use_internal_errors(true);
        $reporting = error_reporting(0);

        $document->documentURI = $filename;
        $loaded                = $document->loadXML($contents);
        $errors                = libxml_get_errors();

        libxml_use_internal_errors($internal);
        error_reporting($reporting);

        if ($loaded === false || count($errors) > 0) {
            throw InvalidXML::fromXMLErrors($errors);
        }
        return $document;
    }
}
