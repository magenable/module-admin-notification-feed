<?php

declare(strict_types=1);

namespace Magenable\AdminNotificationFeed\Model\Feed;

use Magento\Framework\Exception\LocalizedException;
use Exception;

class News
{
    /**
     * Path of news feed
     */
    public const URL = 'https://feed.magenable.com.au/feed/extensions.xml';

    /**
     * @var FeedProvider
     */
    private FeedProvider $feedProvider;

    /**
     * @var FeedParser
     */
    private FeedParser $feedParser;

    /**
     * @var FeedConfig
     */
    private FeedConfig $feedConfig;

    /**
     * @var AdminNotification
     */
    private AdminNotification $adminNotification;

    /**
     * @param FeedProvider $feedProvider
     * @param FeedParser $feedParser
     * @param FeedConfig $feedConfig
     * @param AdminNotification $adminNotification
     */
    public function __construct(
        FeedProvider $feedProvider,
        FeedParser $feedParser,
        FeedConfig $feedConfig,
        AdminNotification $adminNotification
    ) {
        $this->feedProvider = $feedProvider;
        $this->feedParser = $feedParser;
        $this->feedConfig = $feedConfig;
        $this->adminNotification = $adminNotification;
    }

    /**
     * @throws LocalizedException
     * @throws Exception
     */
    public function check(): ?bool
    {
        try {
            if (!$this->feedConfig->checkLastUpdateTime()) {
                return null;
            }
            if (!$feedContent = $this->feedProvider->getFeedContent(self::URL, $this->feedConfig->getLastUpdateTime())
            ) {
                $this->feedConfig->updateConfig();
                return null;
            }
            if (!$feedItems = $this->feedParser->parse($feedContent)) {
                $this->feedConfig->updateConfig();
                return null;
            }
            $this->adminNotification->addItems($feedItems);
            $this->feedConfig->saveLastAddedItems($feedItems);
            $this->feedConfig->updateConfig();
        } catch (Exception $e) {
            $this->feedConfig->updateConfig();
            throw $e;
        }

        return true;
    }
}
