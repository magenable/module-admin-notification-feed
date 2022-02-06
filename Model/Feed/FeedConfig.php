<?php

declare(strict_types=1);

namespace Magenable\AdminNotificationFeed\Model\Feed;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magenable\AdminNotificationFeed\Helper\Data;

class FeedConfig
{
    private const INTERVAL = 60 * 60 * 4; // no more than once every 4 hours
    private const CONFIG_FEED_SAVED_ITEMS = 'magenable_admin_notification_feed/feed/saved_items';
    private const CONFIG_FEED_LAST_UPDATE = 'magenable_admin_notification_feed/feed/last_update';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private ReinitableConfigInterface $reinitableConfig;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param ReinitableConfigInterface $reinitableConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->serializer = $serializer;
    }

    /**
     * @return bool
     */
    public function checkLastUpdateTime(): bool
    {
        return ($this->getLastUpdateTime() + self::INTERVAL) <= time();
    }

    /**
     * @return int|null
     */
    public function getLastUpdateTime(): ?int
    {
        $lastUpdateTime = $this->scopeConfig->getValue(self::CONFIG_FEED_LAST_UPDATE);
        return $lastUpdateTime ? (int)$lastUpdateTime : null;
    }

    public function getSavedItems(): array
    {
        $items = $this->scopeConfig->getValue(self::CONFIG_FEED_SAVED_ITEMS);
        return $items ? $this->serializer->unserialize($items) : [];
    }

    /**
     * @param array $items
     */
    public function saveLastAddedItems(array $items): void
    {
        $savedItems = $this->getSavedItems();
        foreach ($items as $item) {
            if (!in_array($item['id'], $savedItems, false)) {
                $savedItems[] = $item['id'];
            }
        }

        $this->configWriter->save(
            self::CONFIG_FEED_SAVED_ITEMS,
            $this->serializer->serialize($savedItems)
        );
    }

    public function updateConfig(): void
    {
        $this->configWriter->save(self::CONFIG_FEED_LAST_UPDATE, time());
        $this->reinitableConfig->reinit();
    }
}
