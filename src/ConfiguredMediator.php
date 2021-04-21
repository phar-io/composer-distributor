<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use Composer\Composer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Exception;
use GnuPG;
use PharIo\ComposerDistributor\Config\Config;
use PharIo\ComposerDistributor\Config\Loader;
use PharIo\ComposerDistributor\Service\Installer;
use PharIo\FileSystem\Directory;
use PharIo\GnuPG\Factory;
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
        parent::uninstall($composer, $io);
        $this->removePhars();
    }

    public function installOrUpdateFunction(PackageEvent $event): void
    {
        if (!$this->isDesiredPackageEvent($event, $this->config->package())) {
            return;
        }
        $gnuPG = $this->createGnuPG();
        // we do not want to crash if no GnuPG was found
        // but display a noticeable warning to the user
        if ($gnuPG === null) {
            $this->getIO()->write(
                PHP_EOL .
                '    <warning>WARNING</warning>' . PHP_EOL .
                '    No GPG installation found! Use installed PHARs with care. ' . PHP_EOL .
                '    Consider installing GnuPG to verify PHAR authenticity.' . PHP_EOL .
                '    If you need help installing GnuPG visit http://phar.io/install-gnupg' . PHP_EOL
            );
        }

        $installer = $this->createInstallerFromConfig($this->config, $event);
        $installer->install(
            $this->config->phars(),
            $this->config->keyDirectory() ? $this->createKeyDirectory($this->config) : null,
            $gnuPG
        );
    }

    public function createGnuPG(): ?GnuPG
    {
        $factory = new Factory();
        try {
            $gnuPG = $factory->createGnuPG(new Directory(sys_get_temp_dir()));
        } catch (Exception $e) {
            $gnuPG = null;
        }
        return $gnuPG;
    }

    private function createInstallerFromConfig(Config $config, PackageEvent $event): Installer
    {
        return new Installer(
            $config->package(),
            $this->getIO(),
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
        $binDir = $this->getComposer()->getConfig()->get('bin-dir');

        foreach ($this->config->phars()->getList() as $phar) {
            $this->deleteFile($phar, $binDir);
        }
    }

    private function deleteFile(File $phar, string $binDir): void
    {
        $pharLocation = $binDir . DIRECTORY_SEPARATOR . $phar->pharName();

        if (is_file($pharLocation)) {
            if (!is_writable($pharLocation)) {
                $this->getIO()->write(
                    sprintf('  - Can not remove phar \'%1$s\' (insufficient permissions)', $phar->pharName())
                );
                return;
            }
            $this->getIO()->write(sprintf('  - Removing phar <info>%1$s</info>', $phar->pharName()));
            unlink($pharLocation);
        }
    }
}
