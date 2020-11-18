<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use RuntimeException;

final class ValidationFailed extends RuntimeException
{
    /**
     * @param \LibXMLError[] $errors
     */
    public static function fromXMLErrors(array $errors): ValidationFailed
    {
        $mapped = [];
        foreach ($errors as $error) {
            if (!isset($mapped[$error->line])) {
                $mapped[$error->line] = [];
            }
            $mapped[$error->line][] = \trim($error->message);
        }

        $message = "The configuration file did not pass validation!" . PHP_EOL
                 . "The following problems have been detected:" . PHP_EOL;

        foreach ($mapped as $line => $error) {
            $message .= sprintf("\n  Line %d:\n", $line);
            foreach ($error as $msg) {
                $message .= sprintf("  - %s\n", $msg);
            }
        }
        $message .= PHP_EOL;

        return new self($message);
    }
}
