<?php
/**
 * Copyright Andreas Heigl <andreas@heigl.org>
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

namespace PharIo\SinglePharPluginBase;

use function current;
use function key;
use function next;
use function reset;

trait IteratorImplementation
{
	public abstract function &getList() : array;

	public function current()
	{
		return current($this->getList());
	}

	public function next()
	{
		next($this->getList());
	}

	public function key()
	{
		return key($this->getList());
	}

	public function valid()
	{
		return false === key($this->getList());
	}

	public function rewind()
	{
		return reset($this->getList());
	}
}
