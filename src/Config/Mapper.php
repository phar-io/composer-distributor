<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use PharIo\ComposerDistributor\File;
use PharIo\ComposerDistributor\FileList;
use PharIo\ComposerDistributor\Url;

class Mapper
{
	/**
	 * Expected format:
	 *
	 * [
	 *   'packageName'  => 'phar-io/phive',
	 *   'keyDirectory' => 'keys',
	 *   'phars'        => [
	 *     [
	 *       'name'      => 'phive',
	 *       'file'      => 'https://github.com/phar-io/phive/releases/%version%/phive.phar',
	 *       'signature' => 'https://github.com/phar-io/phive/releases/%version%/phive.phar.asc'
	 *     ]
	 *   ]
	 * ]
	 *
	 */
	public function createConfig(array $configData): Config
	{
		$this->validateBaseConfig($configData);
		$this->validatePharsConfig($configData);

		return new Config($configData['package'], $configData['keyDir'], $this->createPhars($configData['phars']));
	}

	private function createPhars(array $pharsData): FileList
	{
		$phars = [];
		foreach ($pharsData as $phar) {
			$phars[] = new File(
				$phar['name'],
				Url::fromString($phar['file']),
				Url::fromString($phar['signature'])
			);
		}
		return new FileList(...$phars);
	}

	private function validateBaseConfig(array $configData): void
	{
		if (!isset($configData['packageName']) || !is_string($configData['packageName'])) {
			throw new \RuntimeException('Config value for  \'packageName\' is missing');
		}
	}

	private function validatePharsConfig(array $configData): void
	{
		if (!isset($configData['phars']) || !is_array($configData['phars']) || count($configData['phars']) < 1) {
			throw new \RuntimeException('Invalid \'phars\' configuration');
		}
		foreach ($configData['phars'] as $phar) {
			$this->validatePharConfig($phar);
		}
	}

	private function validatePharConfig(array $pharData): void
	{
		if (!isset($pharData['name']) || !is_string($pharData['name'])) {
			throw new \RuntimeException('Invalid phar config  \'name\' is missing');
		}
		if (!isset($pharData['file']) || !is_string($pharData['file'])) {
			throw new \RuntimeException('Invalid phar config  \'file\' is missing');
		}
		if (!isset($pharData['signature']) || !is_string($pharData['signature'])) {
			throw new \RuntimeException('Invalid phar config  \'signature\' is missing');
		}
	}
}
