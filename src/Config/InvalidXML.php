<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use RuntimeException;

final class InvalidXML extends RuntimeException
{
    /**
     * @param \LibXMLError[] $errors
     */
    public static function fromXMLErrors(array $errors): InvalidXml
    {
        $message = 'Error loading config file' . PHP_EOL;

        foreach ($errors as $error) {
            $message .= ' - ' . $error->message . PHP_EOL;
        }

        return new self($message);
    }
}
