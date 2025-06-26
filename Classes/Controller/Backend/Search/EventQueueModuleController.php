<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace ApacheSolrForTypo3\Solr\Controller\Backend\Search;


use ApacheSolrForTypo3\Solr\IndexQueue\QueueInterface;
use ApacheSolrForTypo3\Solr\System\Records\Queue\EventQueueItemRepository;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Form\Exception as BackendFormException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Index Queue Module
 *
 * @todo: Support all index queues in actions beside "initializeIndexQueueAction" and
 *        "resetLogErrorsAction"
 */
class EventQueueModuleController extends AbstractModuleController
{
    protected array $enabledIndexQueues;

    protected function initializeAction(): void
    {
        parent::initializeAction();

    }

    public function setIndexQueue(QueueInterface $indexQueue): void
    {
        $this->indexQueue = $indexQueue;
    }

    /**
     * Lists the available indexing configurations
     *
     * @throws BackendFormException
     * @throws DBALException
     */
    public function indexAction(): ResponseInterface
    {

        $itemRepository = $this->getEventQueueItemRepository();
        $queueItems = $itemRepository->getEventQueueItems(1000, false);
        $events = [];
        foreach ($queueItems as $queueItem) {
            $event = unserialize($queueItem['event']);
            $fqcn = get_class($event);
            $events[] = [
                'uid' => $queueItem['uid'],
                'event' => $event,
                'className' => substr($fqcn, strrpos($fqcn, '\\') + 1),
                'error_message' => $queueItem['error_message'],
            ];
        }
        $this->moduleTemplate->assign('eventqueue_events', $events);
        return $this->moduleTemplate->renderResponse('Index');
    }
    /**
     * Return the EventQueueItemRepository
     */
    protected function getEventQueueItemRepository(): EventQueueItemRepository
    {
        return GeneralUtility::makeInstance(EventQueueItemRepository::class);
    }

}
