<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use Composer\Composer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use PharIo\ComposerDistributor\Config\Config;
use PharIo\ComposerDistributor\Config\Loader;
use PharIo\ComposerDistributor\Service\Installer;
use SplFileInfo;

abstract class ConfiguredMediator extends PluginBase
{
    /** @var \PharIo\ComposerDistributor\Config\Config */
    private $config;

    /**
     * Config has to be loaded on instantiation because on uninstall all external dependencies are
     * removed before `uninstall` is called and auto-loading any external phar-io dependencies then will fail.
     */
    public function __construct()
    {
        $this->config = Loader::loadFile($this->getDistributorConfig());
    }

    abstract protected function getDistributorConfig(): string;

    public function uninstall(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io       = $io;
        $this->removePhars();
    }

    public function installOrUpdateFunction(PackageEvent $event): void
    {
        $installer = $this->createInstallerFromConfig($this->config, $event);
        $installer->install($this->config->phars());
    }

    private function createInstallerFromConfig(Config $config, PackageEvent $event): Installer
    {
        return new Installer(
            $config->package(),
            $config->keyDirectory() ? $this->createKeyDirectory($config) : null,
            $this->io,
            $event
        );
    }

    private function createKeyDirectory(Config $config): KeyDirectory
    {
        $keyDirLocation = new SplFileInfo(
            dirname($this->getDistributorConfig())
            . DIRECTORY_SEPARATOR
            . $config->keyDirectory()
        );

        if (!$keyDirLocation->isReadable()) {
            throw KeyNotFound::fromInvalidPath($config->keyDirectory());
        }

        return new KeyDirectory($keyDirLocation);
    }

    private function removePhars(): void
    {
        $binDir = $this->composer->getConfig()->get('bin-dir');

        foreach ($this->config->phars()->getList() as $phar) {
            $this->deleteFile($phar, $binDir);
        }
    }

    private function deleteFile(File $phar, string $binDir): void
    {
        $pharLocation = $binDir . DIRECTORY_SEPARATOR . $phar->pharName();

        if (is_file($pharLocation)) {
            if (!is_writable($pharLocation)) {
                $this->io->write(
                    sprintf('    Can not remove phar \'%1$s\' (insufficient permissions)', $phar->pharName())
                );
                return;
            }
            $this->io->write(sprintf('    Removing phar \'%1$s\'', $phar->pharName()));
            unlink($pharLocation);
        }
    }
}
