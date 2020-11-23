<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use Composer\Composer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use PharIo\ComposerDistributor\Config\Config;
use PharIo\ComposerDistributor\Config\Loader;
use PharIo\ComposerDistributor\Service\Installer;

abstract class ConfiguredMediator extends PluginBase
{
    abstract protected function getDistributorConfig(): string;

    public function uninstall(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io       = $io;
        $this->removePhars();
    }

    public function installOrUpdateFunction(PackageEvent $event): void
    {
        $config    = Loader::loadFile($this->getDistributorConfig());
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

    private function removePhars(): void
    {
        $config = Loader::loadFile($this->getDistributorConfig());
        $binDir = $this->composer->getConfig()->get('bin-dir');

        /** @var \PharIo\ComposerDistributor\File $phar */
        foreach ($config->phars() as $phar) {
            $this->deleteFile($phar, $binDir);
        }
    }

    private function deleteFile(File $phar, string $binDir): void
    {
        $pharLocation = $binDir . DIRECTORY_SEPARATOR . $phar->pharName();
        if (is_file($pharLocation)) {
            if (!is_writable($pharLocation)) {
                $this->io->write(sprintf('can not remove phar \'%1$s\' (insufficient permissions)', $phar->pharName()));
                return;
            }
            $this->io->write(sprintf('remove phar \'%1$s\'', $phar->pharName()));
            unlink($pharLocation);
        }
    }
}
