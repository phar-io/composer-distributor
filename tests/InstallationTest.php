<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributorTest;

use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\GenericRule;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Rule;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Exception;
use PharIo\ComposerDistributor\ConfiguredMediator;
use PHPUnit\Framework\TestCase;
use function unlink;

class InstallationTest extends TestCase
{
	public function testInstallationWithoutSignatureWorks(): void
	{
		$class = new class() extends ConfiguredMediator {

			protected function getMediatorConfig() : string
			{
				return __DIR__ . '/_assets/installWithoutSignature.json';
			}
		};

		$composer     = self::createMock(Composer::class);
		$ioInterface  = self::createMock(IOInterface::class);
		$packageEvent = self::createMock(PackageEvent::class);
		$package      = self::createMock(Package::class);
		$config       = self::createMock(Config::class);
		$operation    = self::createMock(InstallOperation::class);
		$config->method('get')->with('bin-dir')->willReturn(__DIR__ . '/_assets');
		$composer->method('getPackage')->willReturn($package);
		$composer->method('getConfig')->willReturn($config);
		$package->method('getName')->willReturn('foo/bar');
		$package->method('getFullPrettyVersion')->willReturn('0.5.0');
		$packageEvent->method('getOperation')->willReturn($operation);
		$packageEvent->method('getComposer')->willReturn($composer);
		$operation->method('getPackage')->willReturn($package);

		$class->activate($composer, $ioInterface);
		$class->installOrUpdateFunction($packageEvent);

		self::assertFileExists(__DIR__ . '/_assets/foo');

		unlink(__DIR__ . '/_assets/foo');
	}

	public function testInstallationWithSignatureWorks(): void
	{
		$class = new class() extends ConfiguredMediator {

			protected function getMediatorConfig() : string
			{
				return __DIR__ . '/_assets/installWithSignature.json';
			}
		};

		$composer     = self::createMock(Composer::class);
		$ioInterface  = self::createMock(IOInterface::class);
		$packageEvent = self::createMock(PackageEvent::class);
		$package      = self::createMock(Package::class);
		$config       = self::createMock(Config::class);
		$operation    = self::createMock(InstallOperation::class);
		$config->method('get')->with('bin-dir')->willReturn(__DIR__ . '/_assets');
		$composer->method('getPackage')->willReturn($package);
		$composer->method('getConfig')->willReturn($config);
		$package->method('getName')->willReturn('foo/bar');
		$package->method('getFullPrettyVersion')->willReturn('0.5.0');
		$packageEvent->method('getOperation')->willReturn($operation);
		$packageEvent->method('getComposer')->willReturn($composer);
		$operation->method('getPackage')->willReturn($package);

		$class->activate($composer, $ioInterface);
		$class->installOrUpdateFunction($packageEvent);

		self::assertFileExists(__DIR__ . '/_assets/foo');

		unlink(__DIR__ . '/_assets/foo');
	}

	public function testInstallationWithFaultySignatureFails(): void
	{
		$class = new class() extends ConfiguredMediator {

			protected function getMediatorConfig() : string
			{
				return __DIR__ . '/_assets/installWithFaultySignature.json';
			}
		};

		$composer     = self::createMock(Composer::class);
		$ioInterface  = self::createMock(IOInterface::class);
		$packageEvent = self::createMock(PackageEvent::class);
		$package      = self::createMock(Package::class);
		$config       = self::createMock(Config::class);
		$operation    = self::createMock(InstallOperation::class);
		$config->method('get')->with('bin-dir')->willReturn(__DIR__ . '/_assets');
		$composer->method('getPackage')->willReturn($package);
		$composer->method('getConfig')->willReturn($config);
		$package->method('getName')->willReturn('foo/bar');
		$package->method('getFullPrettyVersion')->willReturn('0.5.0');
		$packageEvent->method('getOperation')->willReturn($operation);
		$packageEvent->method('getComposer')->willReturn($composer);
		$operation->method('getPackage')->willReturn($package);

		self::expectException(Exception::class);

		$class->activate($composer, $ioInterface);
		$class->installOrUpdateFunction($packageEvent);

		unlink(__DIR__ . '/_assets/foo');
	}
}
