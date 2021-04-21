<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use Composer\Composer;
use Composer\DependencyResolver\GenericRule;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\Package\CompletePackage;
use Composer\Semver\Constraint\MultiConstraint;
use RuntimeException;
use Throwable;
use function version_compare;

final class PackageVersion
{
    /** @var string */
    private $name;

    /** @var string */
    private $versionString;

    /** @var \PharIo\ComposerDistributor\SemanticVersion|null */
    private $semver;

    private function __construct(string $name, string $versionString)
    {
        $this->name = $name;
        $this->versionString = $versionString;
        try {
            $this->semver = SemanticVersion::fromVersionString($versionString);
        } catch (Throwable $e) {
            $this->semver = null;
        }
    }

    public function name() : string
    {
        return $this->name;
    }

    public function fullVersion() : string
    {
        return $this->versionString;
    }

    public function major() : string
    {
        if (!$this->semver instanceof SemanticVersion) {
            return '';
        }

        return (string)$this->semver->major();
    }

    public function minor() : string
    {
        if (!$this->semver instanceof SemanticVersion) {
            return '';
        }
        return (string)$this->semver->minor();
    }

    public function patch() : string
    {
        if (!$this->semver instanceof SemanticVersion) {
            return '';
        }
        return (string)$this->semver->patch();
    }

    public function build() : string
    {
        if (!$this->semver instanceof SemanticVersion) {
            return '';
        }
        return $this->semver->build();
    }

    public function preRelease() : string
    {
        if (!$this->semver instanceof SemanticVersion) {
            return '';
        }
        return $this->semver->preRelease();
    }

    public static function fromPackageEvent(PackageEvent $event, string $pluginName) : self
    {
        $package = OperationPackage::createFromEvent($event, $pluginName);
        return new self($package->getName(), $package->getFullPrettyVersion());
    }
}
