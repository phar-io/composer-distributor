<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor\Service;

use PharIo\ComposerDistributor\Url;
use SplFileInfo;
use function feof;
use function fwrite;
use function getenv;
use function stream_context_create;

final class Download
{
    private $url;

    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    public function toLocation(SplFileInfo $downloadLocation) : void
    {
        $context = $this->getStreamContext();
        $source = fopen($this->url->toString(), 'r',false, $context);
        $target = fopen($downloadLocation->getPathname(), 'w');
        while (!feof($source)) {
            fwrite($target, fread($source, 1024));
        }
        fclose($source);
        fclose($target);
    }

    /**
     * @return resource
     */
    private function getStreamContext()
    {
        foreach (['http_proxy', 'HTTP_PROXY', 'https_proxy', 'HTTPS_PROXY'] as $envName) {
            $proxy = getenv($envName);
            if ($proxy !== '') {
                break;
            }
        }

        if ($proxy === '') {
            return stream_context_create([]);
        }

        $context = [
            'http' => [
                'proxy' => $proxy,
                'request_fulluri' => true,
            ]
        ];

        $auth = getenv('HTTP_PROXY_AUTH');
        if ($auth !== '') {
            $context['http']['header'][] = 'Proxy-Authorization: Basic ' . $auth;
        }

        return stream_context_create($context);
    }
}
