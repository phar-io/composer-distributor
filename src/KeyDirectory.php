<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use DirectoryIterator;
use Iterator;
use SplFileInfo;

final class KeyDirectory implements Iterator
{
    use IteratorImplementation;

    /** @var SplFileInfo[] */
    private $keys;

    public function __construct(SplFileInfo $publicKeyFolder)
    {
        $this->keys = [];


        if (!$publicKeyFolder->isDir()) {
            $this->keys[] = $publicKeyFolder;
            return;
        }

        foreach (new DirectoryIterator($publicKeyFolder->getPathname()) as $item) {
            if (!$item->isFile()) {
                continue;
            }

            $this->keys[] = new SplFileInfo($item->getPathname());
        }
    }

    public function &getList() : array
    {
        return $this->keys;
    }
}
