<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use DOMDocument;
use PharIo\ComposerDistributor\File;
use PharIo\ComposerDistributor\FileList;
use PharIo\ComposerDistributor\Url;

class Mapper
{
    /** @var \DOMDocument */
    private $document;

    public function createConfig(DOMDocument $docDocument): Config
    {
        $this->document = $docDocument;
        $this->validateConfigurationAgainstSchema();

        return new Config(
            $this->packageName(),
            $this->createPhars(),
            $this->createKeyDir()
        );
    }

    private function validateConfigurationAgainstSchema()
    {
        $original    = \libxml_use_internal_errors(true);
        $xsdFilename = __DIR__ . '/../../distributor.xsd';

        if ($this->document->schemaValidate($xsdFilename)) {
            return;
        }

        $errors = \libxml_get_errors();

        \libxml_clear_errors();
        \libxml_use_internal_errors($original);

        if (count($errors) > 0) {
            throw ValidationFailed::fromXMLErrors($errors);
        }
    }

    private function packageName(): string
    {
        return $this->document->documentElement->getAttribute('packageName');
    }

    private function createPhars(): FileList
    {
        $phars = [];
        foreach ($this->document->getElementsByTagName('phar') as $phar) {
            $phars[] = new File(
                $phar->getAttribute('name'),
                Url::fromString($phar->getAttribute('file')),
                $phar->hasAttribute('signature') ? Url::fromString($phar->getAttribute('signature')) : null
            );
        }

        return new FileList(...$phars);
    }

    private function createKeyDir(): ?string
    {
        $root = $this->document->documentElement;

        return $root->hasAttribute('keyDirectory')
            ? $root->getAttribute('keyDirectory')
            : null;
    }
}
