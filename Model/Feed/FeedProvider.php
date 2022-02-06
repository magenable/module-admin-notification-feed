<?php

declare(strict_types=1);

namespace Magenable\AdminNotificationFeed\Model\Feed;

use Magento\Framework\HTTP\Adapter\CurlFactory;
use Zend_Http_Client;

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
    public function getFeedContent(string $url, int $modifiedSince = null): ?string
    {
        $curl = $this->curlFactory->create();
        $curl->addOption(CURLOPT_ACCEPT_ENCODING, 'gzip');
        $headers = [];
        if ($modifiedSince) {
            $headers[] = 'If-Modified-Since: ' . gmdate('D, d M Y H:i:s T', $modifiedSince);
        }
        $curl->write(Zend_Http_Client::GET, $url, '1.1', $headers);
        $response = $curl->read();
        $curl->close();

        if (empty($response)) {
            return null;
        }
        $response = preg_split('/^\r?$/m', $response, 2);
        if (preg_match("/(?i)(\W|^)(404 file not found)(\W|$)/i", $response[0])) {
            return null;
        }
        if (preg_match("@(?i)(\W|^)(HTTP/1.1 304)(\W|$)@", $response[0], $notModifiedFile)) {
            return null;
        }

        return trim($response[1]);
    }
}
