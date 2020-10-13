<?php

declare(strict_types=1);

/**
 * Copyright Andreas Heigl <andreas@heigl.org>
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

namespace PharIo\SinglePharPluginBase\Exception;

use RuntimeException;

final class NoSemanticVersioning extends RuntimeException
{
	public static function fromversionString(string $version) : self
	{
		return new self(sprintf('The version string "%s" does not follow semantic versioning', $version));
	}
}
