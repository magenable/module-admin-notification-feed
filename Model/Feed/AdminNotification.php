<?php

declare(strict_types=1);

namespace Magenable\AdminNotificationFeed\Model\Feed;

use Magento\AdminNotification\Model\InboxFactory;

class AdminNotification
{
    private const NECESSARY_KEYS = [
        'severity', 'date_added', 'title', 'description', 'url'
    ];

    /**
     * @var InboxFactory
     */
    private InboxFactory $inboxFactory;

    /**
     * @param InboxFactory $inboxFactory
     */
    public function __construct(
        InboxFactory $inboxFactory
    ) {
        $this->inboxFactory = $inboxFactory;
    }

    /**
     * @param array $items
     * @return bool
     */
    public function addItems(array $items): bool
    {
        foreach ($items as $key => $item) {
            $items[$key] = $this->excludeNotNecessaryKeysFromItem($item);
        }

        $inbox = $this->inboxFactory->create();
        $inbox->parse($items);

        return true;
    }

    /**
     * @param array $item
     * @return array
     */
    private function excludeNotNecessaryKeysFromItem(array $item): array
    {
        return array_intersect_key($item, array_flip(self::NECESSARY_KEYS));
    }
}
