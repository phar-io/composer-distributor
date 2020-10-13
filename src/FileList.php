<?php

declare(strict_types=1);

/**
 * Copyright Andreas Heigl <andreas@heigl.org>
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

namespace PharIo\SinglePharPluginBase;

use Iterator;

final class FileList implements Iterator
{
	use IteratorImplementation;

	private $list;

	public function __construct(File ...$files)
	{
		$this->list = $files;
	}

	public function &getList() : array
	{
		return $this->list;
	}
}
