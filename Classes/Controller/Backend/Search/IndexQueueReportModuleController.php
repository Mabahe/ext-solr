<?php
namespace ApacheSolrForTypo3\Solr\Controller\Backend\Search;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2015 Ingo Renner <ingo@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use ApacheSolrForTypo3\Solr\Util;
use ApacheSolrForTypo3\Solr\Backend\IndexingConfigurationSelectorField;
use ApacheSolrForTypo3\Solr\Domain\Index\IndexService;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Index Queue Module
 *
 * @author Ingo Renner <ingo@typo3.org>
 */
class IndexQueueReportModuleController extends AbstractModuleController
{

    /**
     * Module name, used to identify a module f.e. in URL parameters.
     *
     * @var string
     */
    protected $moduleName = 'IndexQueueReport';

    /**
     * Module title, shows up in the module menu.
     *
     * @var string
     */
    protected $moduleTitle = 'Index Queue Report';

    /**
     * @var Queue
     */
    protected $indexQueue;

    /**
     * Initializes the controller before invoking an action method.
     */
    protected function initializeAction()
    {
        parent::initializeAction();
        $this->indexQueue = GeneralUtility::makeInstance(Queue::class);
    }

    /**
     * Lists the available indexing configurations
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->selectedSite === null) {
            $this->view->assign('can_not_proceed', true);
            return;
        }

        $solrConfiguration = Util::getSolrConfigurationFromPageId($this->selectedSite->getRootPageId());
        $indexingConfigurationNames = $solrConfiguration->getEnabledIndexQueueConfigurationNames();
        $initializationStatus = [];

        foreach ($indexingConfigurationNames as $indexingConfigurationName) {
            $initializationStatus[$indexingConfigurationName] = $this->indexQueue->getInitializationStatus(
                $this->selectedSite,
                $indexingConfigurationName
            );
        }

        $this->view->assign('indexqueue_initializationStatus', $initializationStatus);

    }


    /**
     * Initializes the Index Queue for selected indexing configurations
     *
     * @return void
     */
    public function fixIndexQueueAction()
    {
        if ($this->selectedSite === null) {
            return false;
        }

        $indexingConfigurationName = $this->request->getArgument('configurationName');

        $this->indexQueue->synchroniseIndexingConfiguration(
            $this->selectedSite,
            $indexingConfigurationName
        );

        $this->forward('index');
    }

}
