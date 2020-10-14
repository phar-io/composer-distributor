<?php

declare(strict_types=1);

/**
 * Copyright Andreas Heigl <andreas@heigl.org>
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

namespace PharIo\SinglePharPluginBase\Service;

use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use PharIo\FileSystem\Directory;
use PharIo\GnuPG\Factory;
use PharIo\SinglePharPluginBase\Exception\SomebodyElsesProblem;
use PharIo\SinglePharPluginBase\File;
use PharIo\SinglePharPluginBase\FileList;
use PharIo\SinglePharPluginBase\KeyDirectory;
use PharIo\SinglePharPluginBase\PackageVersion;
use PharIo\SinglePharPluginBase\Url;
use RuntimeException;
use SplFileInfo;
use function getcwd;
use function sprintf;
use function sys_get_temp_dir;

final class Installer
{
	private $io;

	private $name;

	private $keys;

	private $event;

	public function __construct(string $name, KeyDirectory $keys, IOInterface $io, PackageEvent $event)
	{
		$this->name = $name;
		$this->io = $io;
		$this->keys = $keys;
		$this->event = $event;
	}

	public function install(FileList $filelist) : void
	{
		try {
			$packageVersion = PackageVersion::fromPackageEvent($this->event, $this->name);
		} catch (SomebodyElsesProblem $e) {
			$this->io->write($e->getMessage());
			return;
		}
		$versionReplacer = new VersionConstraintReplacer($packageVersion);

		$factory = new Factory();

		$verify = new Verify($this->keys, $factory->createGnuPG(new Directory(
			sys_get_temp_dir()
		)));

		/** @var File $file */
		foreach ($filelist as $file) {
			$this->io->write(sprintf(
				'downloading Artifact in version %2$s from %1$s',
				$versionReplacer->replace($file->pharUrl()->toString()),
				$packageVersion->fullVersion()
			));
			$download = new Download(Url::fromString(
				$versionReplacer->replace($file->pharUrl()->toString())
			));
			$pharLocation = new SplFileInfo(sys_get_temp_dir() . '/' . $file->pharName());
			$download->toLocation($pharLocation);

			if (!$file->signatureUrl()) {
				$this->io->write(sprintf(
					"No digital Signature found! Use this file with care!"
				));
				continue;
			}

			$downloadSignature = new Download(Url::fromString(
				$versionReplacer->replace($file->signatureUrl()->toString())
			));
			$signatureLocation = new SplFileInfo(sys_get_temp_dir() . '/' . $file->pharName() . '.asc');
			$downloadSignature->toLocation($signatureLocation);

			if (!$verify->fileWithSignature($pharLocation, $signatureLocation)) {
				throw new RuntimeException('Signature Verification failed');
			}
		}
	}
}
