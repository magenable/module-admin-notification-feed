<?php

declare(strict_types=1);

namespace Magenable\AdminNotificationFeed\Model\Feed;

use Magento\Framework\HTTP\Adapter\CurlFactory;
use Laminas\Http\Request;

class FeedProvider
{
    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        CurlFactory $curlFactory
    ) {
        $this->curlFactory = $curlFactory;
    }

    /**
     * @param string $url
     * @param int|null $modifiedSince
     * @return string|null
     */
    public function getFeedContent(string $url, ?int $modifiedSince = null): ?string
    {
        $curl = $this->curlFactory->create();
        $curl->addOption(CURLOPT_ACCEPT_ENCODING, 'gzip');
        $curl->addOption(
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );
        $curl->addOption(CURLOPT_FOLLOWLOCATION, true);
        $curl->addOption(CURLOPT_MAXREDIRS, 5);

        $headers = [
            'Accept: application/rss+xml, application/atom+xml, application/xml, text/xml, */*;q=0.9',
            'Accept-Language: en-US,en;q=0.9',
        ];
        if ($modifiedSince) {
            $headers[] = 'If-Modified-Since: ' . gmdate('D, d M Y H:i:s T', $modifiedSince);
        }
        $curl->write(Request::METHOD_GET, $url, '1.1', $headers);
        $response = $curl->read();
        $curl->close();

        if (empty($response)) {
            return null;
        }

        $parts = preg_split('/^\r?$/m', $response, 2);
        $header = $parts[0] ?? '';
        $body = $parts[1] ?? null;

        if (!preg_match('#^HTTP/\d(?:\.\d)?\s+2\d\d\b#im', $header)) {
            return null;
        }

        if ($body === null || trim($body) === '') {
            return null;
        }

        return trim($body);
    }
}
