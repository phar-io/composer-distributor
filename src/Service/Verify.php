<?php
/**
 * Copyright by the ComposerDistributor-Team
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use GnuPG;
use PharIo\ComposerDistributor\KeyDirectory;
use SplFileInfo;
use function array_filter;
use function count;
use function file_get_contents;
use function sprintf;

final class Verify
{
	/** @var Gnupg */
	private $gpg;

	public function __construct(KeyDirectory $keys, GnuPG $gpg)
	{
		$this->gpg = $gpg;
		$result = [];

		foreach ($keys->getList() as $key) {
			$result[] = $this->gpg->import(
				file_get_contents($key->getPathname())
			);
		}

		$array = array_filter($result, function ($item) {
			return ($item['imported'] !== 0 || isset($item['fingerprint']));
		});

		if (0 >= count($array)) {
			// when imported is 0 but fingerprint is available the key are already imported/exist
			throw new \RuntimeException('Could not import needed GPG key!');
		}
	}

	public function fileWithSignature(SplFileInfo $file, SplFileInfo $signature) : bool
	{
		$result = $this->gpg->verify(
			file_get_contents($file->getPathname()),
			file_get_contents($signature->getPathname()),
		);

		switch (true) {
			case false === $result:
			case $result[0]['summary'] !== 0:
				throw new \RuntimeException(sprintf(
					'Verification between "%s" and "%s" failed!',
					$signature->getFilename(),
					$file->getFilename()
				));
				break;
			default:
		}

		return true;
	}
}
