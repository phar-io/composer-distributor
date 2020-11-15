<?php
/**
 * Copyright by the ComposerDistributor-Team
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

declare(strict_types=1);

/**
 * Copyright Andreas Heigl <andreas@heigl.org>
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

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
use function var_dump;
use function version_compare;

final class PackageVersion
{
	private $name;

	private $versionString;

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
		$package = null;
		if (0 >= version_compare('2.0.0', Composer::VERSION)) {
			$operation = $event->getOperation();
			switch (true) {
				case $operation instanceof InstallOperation:
				case $operation instanceof UninstallOperation:
					$package = $operation->getPackage();
					break;
				case $operation instanceof UpdateOperation:
					$package = $operation->getTargetPackage();
					break;
				default:
					throw new RuntimeException('No valid operation found');
			}
		} else {
			/** @var GenericRule $rule */
			$rule = $event->getOperation()->getReason();
			/** @var MultiConstraint $constraint */
			$constraint = $rule->getJob()['constraint'];
			if ($rule->getRequiredPackage() !== $pluginName) {
				throw SomebodyElsesProblem::here($pluginName);
			}

			/** @var CompletePackage $packages */
			$package = $event->getInstalledRepo()->findPackage($rule->getRequiredPackage(), $constraint->getPrettyString());
		}
		return new self($package->getName(), $package->getFullPrettyVersion());
	}
}
