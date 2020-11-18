<?php
/**
 * Copyright by the ComposerDistributor-Team
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

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
