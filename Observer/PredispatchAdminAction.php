<?php

declare(strict_types=1);

namespace Magenable\AdminNotificationFeed\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Backend\Model\Auth\Session;
use Magenable\AdminNotificationFeed\Model\Feed\News;
use Psr\Log\LoggerInterface;
use Exception;

class PredispatchAdminAction implements ObserverInterface
{
    /**
     * @var Session
     */
    private $backendSession;

    /**
     * @var News
     */
    private $news;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Session $backendAuthSession,
        News $news,
        LoggerInterface $logger
    ) {
        $this->backendSession = $backendAuthSession;
        $this->news = $news;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        if ($this->backendSession->isLoggedIn()) {
            try {
                $this->news->check();
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
