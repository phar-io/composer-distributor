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
    protected $composer;

    /** @var \Composer\IO\IOInterface */
    protected $io;

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

    abstract public function installOrUpdateFunction(PackageEvent $event) : void;
}
