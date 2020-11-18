<?php
/**
 * Copyright by the ComposerDistributor-Team
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use PharIo\FileSystem\Directory;
use PharIo\GnuPG\Factory;
use PharIo\ComposerDistributor\SomebodyElsesProblem;
use PharIo\ComposerDistributor\File;
use PharIo\ComposerDistributor\FileList;
use PharIo\ComposerDistributor\KeyDirectory;
use PharIo\ComposerDistributor\PackageVersion;
use PharIo\ComposerDistributor\Url;
use RuntimeException;
use SplFileInfo;
use function chmod;
use function file_exists;
use function mkdir;
use function sprintf;
use function sys_get_temp_dir;
use const DIRECTORY_SEPARATOR;

final class Installer
{
    /** @var \Composer\IO\IOInterface */
    private $io;

    /** @var string  */
    private $name;

    /** @var \PharIo\ComposerDistributor\KeyDirectory|null */
    private $keys;

    /** @var \Composer\Installer\PackageEvent */
    private $event;

    public function __construct(string $name, ?KeyDirectory $keys, IOInterface $io, PackageEvent $event)
    {
        $this->name  = $name;
        $this->keys  = $keys;
        $this->io    = $io;
        $this->event = $event;
    }

    public function install(FileList $fileList) : void
    {
        try {
            $packageVersion = PackageVersion::fromPackageEvent($this->event, $this->name);
        } catch (SomebodyElsesProblem $e) {
            $this->io->write($e->getMessage());
            return;
        }

        $versionReplacer = new VersionConstraintReplacer($packageVersion);

        foreach ($fileList->getList() as $file) {
            $this->io->write(sprintf(
                'downloading Artifact in version %2$s from %1$s',
                $versionReplacer->replace($file->pharUrl()->toString()),
                $packageVersion->fullVersion()
            ));

            $pharLocation = $this->downloadPhar($versionReplacer, $file);

            if (!$file->signatureUrl()) {
                $this->io->write(sprintf(
                    "No digital Signature found! Use this file with care!"
                ));
                continue;
            }

            $signatureLocation = $this->downloadSignature($versionReplacer, $file);
            $this->verifyPharWithSignature($pharLocation, $signatureLocation);
            unlink($signatureLocation->getPathname());
        }
    }

    private function downloadPhar(VersionConstraintReplacer $versionReplacer, File $file): \SplFileInfo
    {
        $binDir       = $this->event->getComposer()->getConfig()->get('bin-dir');
        $download     = new Download(Url::fromString(
            $versionReplacer->replace($file->pharUrl()->toString())
        ));
        $pharLocation = new SplFileInfo(
            $binDir . DIRECTORY_SEPARATOR . $file->pharName()
        );

        if (! file_exists($binDir)) {
            mkdir($binDir, 0777, true);
        }
        $download->toLocation($pharLocation);
        chmod($pharLocation->getRealPath(), 0755);

        return $pharLocation;
    }

    private function downloadSignature(VersionConstraintReplacer $versionReplacer, File $file): \SplFileInfo
    {
        $downloadSignature = new Download(Url::fromString(
            $versionReplacer->replace($file->signatureUrl()->toString())
        ));
        $signatureLocation = new SplFileInfo(sys_get_temp_dir() . '/' . $file->pharName() . '.asc');
        $downloadSignature->toLocation($signatureLocation);

        return $signatureLocation;
    }

    private function verifyPharWithSignature(SplFileInfo $pharLocation, SplFileInfo $signatureLocation): void
    {
        if (null === $this->keys) {
            throw new RuntimeException('No keys to verify the signature');
        }
        $factory = new Factory();
        $verify  = new Verify(
            $this->keys,
            $factory->createGnuPG(new Directory(sys_get_temp_dir()))
        );

        if (!$verify->fileWithSignature($pharLocation, $signatureLocation)) {
            throw new RuntimeException('Signature Verification failed');
        }
    }
}
