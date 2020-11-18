<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use Composer\Installer\PackageEvent;
use PharIo\ComposerDistributor\Config\Config;
use PharIo\ComposerDistributor\Config\Loader;
use PharIo\ComposerDistributor\Service\Installer;

abstract class ConfiguredMediator extends PluginBase
{
    abstract protected function getMediatorConfig(): string;

    public function installOrUpdateFunction(PackageEvent $event): void
    {
        $config    = Loader::loadFile($this->getMediatorConfig());
        $installer = $this->createInstallerFromConfig($config, $event);

        $installer->install($config->phars());
    }

    private function createInstallerFromConfig(Config $config, PackageEvent $event): Installer
    {
        return new Installer(
            $config->package(),
            $config->keyDirectory() ? new KeyDirectory($config->keyDirectory()) : null,
            $this->io,
            $event
        );
    }
}
