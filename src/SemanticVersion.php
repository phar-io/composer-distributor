<?php
/**
 * Copyright by the ComposerDistributor-Team
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use function explode;
use function is_numeric;

final class SemanticVersion
{
    private $major;

    private $minor;

    private $patch;

    private $build;

    private $preRelease;

    private function __construct(int $major, int $minor, int $patch, string $preRelease, string $build)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->preRelease = $preRelease;
        $this->build = $build;
    }

    public static function fromVersionString(string $versionString) : self
    {
        $originalVersionString = $versionString;

        $a = explode('+', $versionString);
        $build = '';
        if (isset($a[1])) {
            $build = $a[1];
            $versionString = $a[0];
        }

        $a = explode('-', $versionString);
        $preRelease = '';
        if (isset($a[1])) {
            $preRelease = $a[1];
        }


        $b = explode('.', $a[0]);
        if (!$b) {
            throw NoSemanticVersioning::fromVersionString($originalVersionString);
        }

        if (3 !== count($b)) {
            throw NoSemanticVersioning::fromVersionString($originalVersionString);
        }

        foreach ($b as $i) {
            if (!is_numeric($i)) {
                throw NoSemanticVersioning::fromVersionString($originalVersionString);
            }
        }

        return new self((int)$b[0], (int)$b[1], (int)$b[2], $preRelease, $build);
    }

    public function major() : int
    {
        return $this->major;
    }

    public function minor() : int
    {
        return $this->minor;
    }

    public function patch() : int
    {
        return $this->patch;
    }

    public function preRelease() : string
    {
        return $this->preRelease;
    }

    public function build() : string
    {
        return $this->build;
    }
}
