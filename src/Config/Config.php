<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Config;

use PharIo\ComposerDistributor\FileList;

class Config
{
	/** @var string */
	private $package;

	/** @var string */
	private $keyDirectory;

	/** @var \PharIo\ComposerDistributor\FileList */
	private $phars;

	public function __construct(string $package, string $keyDir, FileList $phars)
	{
		$this->package      = $package;
		$this->keyDirectory = $keyDir;
		$this->phars        = $phars;
	}

	public function package(): string
	{
		return $this->package;
	}

	public function keyDirectory(): string
	{
		return $this->keyDirectory;
	}

	public function phars(): FileList
	{
		return $this->phars;
	}
}
