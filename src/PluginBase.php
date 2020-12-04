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
use SplFileInfo;

abstract class PluginBase implements PluginInterface, EventSubscriberInterface
{
    protected $composer;

    protected $io;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => [
                ['installOrUpdateFunction', 0],
            ],
            PackageEvents::POST_PACKAGE_UPDATE => [
                ['installOrUpdateFunction', 0],
            ],
        ];
    }

    public function createInstaller(string $pluginName, string $keyDirectory, PackageEvent $event) : Installer
    {
        return new Installer(
            $pluginName,
            new KeyDirectory(new SplFileInfo($keyDirectory)),
            $this->io,
            $event
        );
    }

    abstract public function installOrUpdateFunction(PackageEvent $event) : void;
}
