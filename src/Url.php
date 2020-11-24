<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use RuntimeException;
use function parse_url;

final class Url
{
    /** @var string */
    private $scheme;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var string */
    private $path;

    /** @var string */
    private $query;

    /** @var string */
    private $fragment;

    private function __construct(
        string $scheme,
        string $host,
        int $port,
        string $user,
        string $password,
        string $path,
        string $query,
        string $fragment
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    public static function fromString(string $url) : self
    {
        $parts = parse_url($url);
        if (false === $parts) {
            throw new RuntimeException('Invalid URL provided');
        }

        if (!isset($parts['scheme']) || $parts['scheme'] === '') {
            throw new RuntimeException('No schema provided');
        }

        if (!isset($parts['host']) || $parts['host'] === '') {
            throw new RuntimeException('No host provided');
        }

        if (!isset($parts['port']) || $parts['port'] === '') {
            $parts['port'] = 0;
        }

        if (!isset($parts['user'])) {
            $parts['user'] = '';
        }

        if (!isset($parts['pass'])) {
            $parts['pass'] = '';
        }

        if (!isset($parts['path']) || $parts['path'] === '') {
            $parts['path'] = '/';
        }

        if (!isset($parts['query'])) {
            $parts['query'] = '';
        }

        if (!isset($parts['fragment'])) {
            $parts['fragment'] = '';
        }

        return new self(
            (string)$parts['scheme'],
            (string)$parts['host'],
            (int)$parts['port'],
            (string)$parts['user'],
            (string)$parts['pass'],
            (string)$parts['path'],
            (string)$parts['query'],
            (string)$parts['fragment']
        );
    }

    public function toString() : string
    {
        $url = $this->scheme . '://';
        $access = [];
        if ($this->user !== '') {
            $access[] = $this->user;
        }
        if ($this->password !== '') {
            $access[] = $this->password;
        }
        $access = implode(':', $access);
        if ($access !== '') {
            $url .= $access . '@';
        }

        $url .= $this->host;
        if (0 !== $this->port) {
            $url .= ':' . (string)$this->port;
        }

        $url .= $this->path;

        if ($this->query !== '') {
            $url .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    public function __toString() : string
    {
        return $this->toString();
    }
}
