<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributorTest\Service;

use Exception;
use GnuPG;
use PharIo\ComposerDistributor\KeyDirectory;
use PharIo\ComposerDistributor\Service\KeyError;
use PharIo\ComposerDistributor\Service\Verify;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplFileInfo;

class VerifyTest extends TestCase
{
    public function setUp() : void
    {
        require_once __DIR__ . '/../GnuPG.php';
        parent::setUp();
    }

    /**
     * @covers \PharIo\ComposerDistributor\Service\Verify::__construct
     */
    public function testThatConstructorThrowsWithoutKeys(): void
    {
        $keys = new KeyDirectory(new SplFileInfo(
            $this->setupEmptyKeyDir()
        ));

        $gpg = self::getMockBuilder(GnuPG::class)
            ->disableOriginalConstructor()
            ->getMock();
        $gpg->method('import')->willReturn(['imported' => 0]);

        self::expectException(KeyError::class);
        self::expectExceptionMessage('Could not import required GPG key!');

        new Verify($keys, $gpg);
    }

    /**
     * @covers \PharIo\ComposerDistributor\Service\Verify::__construct
     */
    public function testThatConstructorWorksWithSingleKey(): void
    {
        $keys = new KeyDirectory(new SplFileInfo(
            __DIR__ . '/../../_assets/keys/singleKeyDirectory'
        ));

        $gpg = self::getMockBuilder(GnuPG::class)
            ->disableOriginalConstructor()
            ->getMock();

        $gpg->method('import')->willReturn(['imported' => 1]);

        self::assertInstanceOf(Verify::class, new Verify($keys, $gpg));
    }

    /**
     * @covers \PharIo\ComposerDistributor\Service\Verify::__construct
     */
    public function testThatConstructorWorksWithAlreadyImportedKey(): void
    {
        $keys = new KeyDirectory(new SplFileInfo(
            __DIR__ . '/../../_assets/keys/singleKeyDirectory'
        ));

        $gpg = self::createMock(GnuPG::class);

        $gpg->method('import')->willReturn([
            'imported'    => 0,
            'fingerprint' => 'a'
        ]);

        self::assertInstanceOf(Verify::class, new Verify($keys, $gpg));
    }

    /**
     * @covers \PharIo\ComposerDistributor\Service\Verify::fileWithSignature
     */
    public function testThatCorrectVerificationWillNotThrowAnException(): void
    {
        $keys = new KeyDirectory(new SplFileInfo(
            __DIR__ . '/../../_assets/keys/singleKeyDirectory'
        ));

        $gpg = self::createMock(GnuPG::class);
        $gpg->method('import')->will(self::returnValue(['imported' => 1]));
        $gpg->method('verify')->willReturn([['summary' => 0]]);

        $verify = new Verify($keys, $gpg);

        try {
            self::assertTrue($verify->fileWithSignature(
                new SplFileInfo(__DIR__ . '/../../_assets/keys/singleKeyDirectory/junitdiff.key'),
                new SplFileInfo(__DIR__ . '/../../_assets/keys/singleKeyDirectory/junitdiff.key')
            ));
        } catch (Exception $e) {
            self::assertTrue(false);
        }
    }

    private function setupEmptyKeyDir(): string
    {
        $emptyKeyDir = sys_get_temp_dir() . '/cd-empty-key-dir';

        if (!is_dir($emptyKeyDir)) {
            mkdir($emptyKeyDir);
        }
        return $emptyKeyDir;
    }
}
