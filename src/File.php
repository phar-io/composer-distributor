<?php
/**
 * Copyright by the ComposerDistributor-Team
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

final class File
{
	private $name;

	private $pharLocation;

	private $signatureLocation;

	public function __construct(string $name, Url $pharLocation, Url $signatureLocation)
	{
		$this->name = $name;
		$this->pharLocation = $pharLocation;
		$this->signatureLocation = $signatureLocation;
	}

	public function pharName() : string
	{
		return $this->name;
	}

	public function pharUrl() : Url
	{
		return $this->pharLocation;
	}

	public function signatureUrl() : Url
	{
		return $this->signatureLocation;
	}
}
