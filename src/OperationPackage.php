<?php

declare(strict_types = 1);
namespace PharIo\ComposerDistributor;

use Composer\Composer;
use Composer\DependencyResolver\GenericRule;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\Package\CompletePackage;
use Composer\Package\Package;
use Composer\Semver\Constraint\MultiConstraint;
use RuntimeException;

class OperationPackage {

    public static function createFromEvent(PackageEvent $event, string $pluginName): Package
    {
        if (0 >= \version_compare('2.0.0', Composer::VERSION)) {
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

        return $package;
    }
}
