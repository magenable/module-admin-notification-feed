<?php

declare(strict_types=1);

namespace Magenable\AdminNotificationFeed\Model\Feed;

use Magento\Framework\Escaper;
use Magento\Framework\Module\ModuleListInterface;
use SimpleXMLElement;
use Exception;

class FeedParser
{
    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var FeedConfig
     */
    private FeedConfig $feedConfig;

    /**
     * @var ModuleListInterface
     */
    private ModuleListInterface $moduleList;

    /**
     * @param Escaper $escaper
     * @param FeedConfig $feedConfig
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Escaper $escaper,
        FeedConfig $feedConfig,
        ModuleListInterface $moduleList
    ) {
        $this->escaper = $escaper;
        $this->feedConfig = $feedConfig;
        $this->moduleList = $moduleList;
    }

    /**
     * @param string $feed
     * @return array
     * @throws Exception
     */
    public function parse(string $feed): array
    {
        $savedItems = $this->feedConfig->getSavedItems();
        $xmlFeed = new SimpleXMLElement($feed);
        $feedItems = [];
        foreach ($xmlFeed->channel->item as $feedItem) {
            $feedItemId = (int)$feedItem->id;
            if (in_array($feedItemId, $savedItems, false)) {
                continue;
            }
            $feedItems[] = [
                'id'          => $feedItemId,
                'severity'    => (int)$feedItem->severity,
                'date_added'  => date('Y-m-d H:i:s', strtotime((string)$feedItem->publicationDate)),
                'title'       => $this->escaper->escapeHtml((string)$feedItem->title),
                'description' => $this->escaper->escapeHtml((string)$feedItem->description),
                'url'         => $this->escaper->escapeHtml((string)$feedItem->url),
                'modules'     => $this->escaper->escapeHtml((string)$feedItem->modules)
            ];
        }

        return $this->excludeItemsForNotInstalledModules($feedItems);
    }

    /**
     * @param array $feedItems
     * @return array
     */
    private function excludeItemsForNotInstalledModules(array $feedItems): array
    {
        $allModules = $this->moduleList->getNames();
        return array_filter($feedItems, static function ($item) use ($allModules) {
            if (empty($item['modules'])) {
                return true;
            }
            $forModules = explode(',', $item['modules']);
            foreach ($forModules as $forModule) {
                if (in_array($forModule, $allModules, true)) {
                    return true;
                }
            }
            return false;
        });
    }
}
