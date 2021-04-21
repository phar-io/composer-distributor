<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use PharIo\ComposerDistributor\Service\Installer;

abstract class PluginBase implements PluginInterface, EventSubscriberInterface
{
    /** @var \Composer\Composer */
    private $composer;

    /** @var \Composer\IO\IOInterface */
    private $io;

    abstract public function installOrUpdateFunction(PackageEvent $event) : void;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io       = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io       = $io;
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io       = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => [
                ['installOrUpdateFunction', 0],
            ],
            PackageEvents::POST_PACKAGE_UPDATE  => [
                ['installOrUpdateFunction', 0],
            ],
        ];
    }

    public function createInstaller(string $pluginName, PackageEvent $event) : Installer
    {
        return new Installer(
            $pluginName,
            $this->io,
            $event
        );
    }

    protected function getIO(): IOInterface
    {
        if (!$this->io) {
            throw new \RuntimeException('IO not set');
        }
        return $this->io;
    }

    protected function getComposer(): Composer
    {
        if (!$this->composer) {
            throw new \RuntimeException('Composer not set');
        }
        return $this->composer;
    }

    protected function isDesiredPackageEvent(PackageEvent $event, string $pluginName): bool
    {
        $package = OperationPackage::createFromEvent($event, $pluginName);

        return $package->getName() === $pluginName;
    }
}
