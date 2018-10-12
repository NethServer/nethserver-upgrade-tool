<?php

namespace NethServer\Module\UpgradeTool;

/*
 * Copyright (C) 2017 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

 use Nethgui\System\PlatformInterface as Validate;

 /**
  * @author Davide Principi <davide.principi@nethesis.it>
  */
class Reset extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{

    public function initialize()
    {
        parent::initialize();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['ProductName'] = $this->getPlatform()->getDatabase('configuration')->getProp('sysconfig', 'ProductName');
        $view['RebootModule'] = array($view->getModuleUrl('../Reboot'), $view->translate('RebootModule_label'));
        if($this->getRequest()->hasParameter('prepareSuccess')) {
            $this->notifications->notice($view->translate('UpgradeToolPrepareSuccess_notification'));
            $view->getCommandList()->show();
        } elseif($this->getRequest()->isMutation()) {
            $view->getCommandList()->sendQuery($view->getModuleUrl('../Prepare?resetSuccess'));
        }
    }

    public function process()
    {
        parent::process();
        if($this->getRequest()->isMutation()) {
            $this->getPlatform()->signalEvent('nethserver-upgrade-tool-reset');
        }
    }

    public function nextPath()
    {

        return FALSE;
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

    public function setSystemTasks(\Nethgui\Model\SystemTasks $t)
    {
        $this->systemTasks = $t;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
            'SystemTasks' => array($this, 'setSystemTasks'),
        );
    }
}
