<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use Composer\Installer\PackageEvent;
use PharIo\ComposerDistributor\Config;

abstract class ConfiguredMediator extends PluginBase
{
	protected abstract function getMediatorConfig(): string;

	public function installOrUpdateFunction(PackageEvent $event): void
	{
		$config    = Config\Loader::loadFile($this->getMediatorConfig());
		$installer = $this->createInstaller(
			$config->package(),
			$config->keyDirectory(),
			$event
		);

		$installer->install($config->phars());
	}
}
