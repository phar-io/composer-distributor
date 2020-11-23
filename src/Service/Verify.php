<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use GnuPG;
use PharIo\ComposerDistributor\KeyDirectory;
use SplFileInfo;
use function array_filter;
use function count;
use function file_get_contents;

final class Verify
{
    /** @var Gnupg */
    private $gpg;

    public function __construct(KeyDirectory $keys, GnuPG $gpg)
    {
        $this->gpg = $gpg;
        $result = [];

        foreach ($keys->getList() as $key) {
            $result[] = $this->gpg->import(file_get_contents($key->getPathname()));
        }

        $array = array_filter($result, function ($item) {
            return ($item['imported'] !== 0 || isset($item['fingerprint']));
        });

        if (0 >= count($array)) {
            // when imported is 0 but fingerprint is available the key is already imported
            throw KeyError::importFailure();
        }
    }

    public function fileWithSignature(SplFileInfo $file, SplFileInfo $signature) : bool
    {
        $result = $this->gpg->verify(
            file_get_contents($file->getPathname()),
            file_get_contents($signature->getPathname()),
        );

        if ($result === false || $result[0]['summary'] !== 0) {
            throw GpgError::verificationFailed($file->getFilename(), $signature->getFilename());
        }
        return true;
    }
}
