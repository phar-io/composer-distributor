<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use GnuPG;
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

    /** @var \GnuPG|null */
    private $gpg;

    public function __construct(string $name, IOInterface $io, PackageEvent $event)
    {
        $this->name  = $name;
        $this->io    = $io;
        $this->event = $event;
    }

    public function install(FileList $fileList, ?KeyDirectory $keys, ?GnuPG $gnuPG) : void
    {
        try {
            $packageVersion = PackageVersion::fromPackageEvent($this->event, $this->name);
        } catch (SomebodyElsesProblem $e) {
            $this->io->write($e->getMessage());
            return;
        }
        $this->keys      = $keys;
        $this->gpg       = $gnuPG;
        $versionReplacer = new VersionConstraintReplacer($packageVersion);
        $binDir          = $this->event->getComposer()->getConfig()->get('bin-dir');

        if (!file_exists($binDir)) {
            mkdir($binDir, 0777, true);
        }

        foreach ($fileList->getList() as $file) {
            $this->io->write(sprintf(
                '  - Downloading artifact from %1$s',
                $versionReplacer->replace($file->pharUrl()->toString())
            ));

            $downloadLocation = $this->downloadAndVerify($versionReplacer, $file);
            $installLocation  = new SplFileInfo($binDir . DIRECTORY_SEPARATOR . $file->pharName());

            rename($downloadLocation->getPathname(), $installLocation->getPathname());
            chmod($installLocation->getPathname(), 0755);
        }
    }

    private function downloadAndVerify(VersionConstraintReplacer $versionReplacer, File $file): SplFileInfo
    {
        $pharLocation = $this->downloadPhar($versionReplacer, $file);

        if (!$file->signatureUrl()) {
            $this->io->write('  - <comment>No digital signature found! Use this file with care!</comment>');
            return $pharLocation;
        }

        if ($this->gpg === null) {
            $this->io->write('  - <comment>No GnuPG found to verify signature! Use this file with care!</comment>');
            return $pharLocation;
        }

        $signatureLocation = $this->downloadSignature($versionReplacer, $file);
        $this->verifyPharWithSignature($pharLocation, $signatureLocation);
        $this->io->write('  - PHAR signature successfully verified');
        unlink($signatureLocation->getPathname());

        return $pharLocation;
    }

    private function downloadPhar(VersionConstraintReplacer $versionReplacer, File $file): SplFileInfo
    {
        $download     = new Download(Url::fromString($versionReplacer->replace($file->pharUrl()->toString())));
        $pharLocation = new SplFileInfo(sys_get_temp_dir() . '/' . $file->pharName());

        $download->toLocation($pharLocation);

        return $pharLocation;
    }

    private function downloadSignature(VersionConstraintReplacer $versionReplacer, File $file): SplFileInfo
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
        if ($this->keys === null) {
            throw new RuntimeException('No keys to verify the signature');
        }

        $verify  = new Verify($this->keys, $this->gpg);

        if (!$verify->fileWithSignature($pharLocation, $signatureLocation)) {
            throw new RuntimeException('Signature Verification failed');
        }
    }
}
