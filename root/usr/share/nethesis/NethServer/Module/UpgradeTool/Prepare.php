<?php

namespace NethServer\Module\UpgradeTool;

/*
 * Copyright (C) 2018 Nethesis S.r.l.
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
class Prepare extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{

    public function initialize()
    {
        parent::initialize();        
        $realmValidator = $this->createValidator(Validate::HOSTNAME_FQDN);
        $ipAddressValidator = $this->createValidator(Validate::IP)->platform('dcipaddr');

        $this->declareParameter('AdRealm', $realmValidator, array('configuration', 'upgrade-tool', 'SssdRealm'));
        $this->declareParameter('AdWorkgroup', FALSE, array('configuration', 'smb', 'Workgroup'));
        $this->declareParameter('AdIpAddress', $ipAddressValidator, array('configuration', 'upgrade-tool', 'NsdcIpAddress'));
        $this->declareParameter('NsdcBridge', $this->createValidator()->notEmpty(), array('configuration', 'upgrade-tool', 'NsdcBridge'));

        $this->declareParameter('UpgradeType', FALSE, array($this, 'readUpgradeType'));
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        if($this->getRequest()->isMutation()) {
            $v = $this->createValidator()->platform('diskspace');
            if($v->evaluate('')) {
                $report->addValidationErrorMessage($this, 'DiskSpace', 'DiskSpaceError');
            }
        }
    }

    private function getNsdcBridgeDatasource()
    {
        static $interfaces;
        if (isset($interfaces)) {
            return $interfaces;
        }
        foreach($this->getPlatform()->getDatabase('networks')->getAll() as $key => $record) {
            if ( $record['type'] != 'bridge') {
                continue;
            }
            if ( $record['role'] != 'green') {
                continue;
            }
            $interfaces[] = array($key, $key);
        }
        return $interfaces;
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        
    }
    
    public function readUpgradeType()
    {
        $smb = file_exists('/usr/sbin/smbd') ? $this->getPlatform()->getDatabase('configuration')->getKey('smb') : NULL;
        $slapd = file_exists('/usr/sbin/slapd') ? $this->getPlatform()->getDatabase('configuration')->getKey('slapd') : NULL;

        if(isset($smb) && $smb['status'] == 'enabled' && $smb['ServerRole'] == 'ADS') {
            return 'remote-ad';
        } elseif(isset($smb, $slapd) && $smb['status'] == 'enabled' && ($smb['ServerRole'] == 'PDC' || $smb['ServerRole'] == 'WS')) {
            return 'local-ad';
        } elseif(isset($slapd) && $slapd['status'] == 'enabled') {
            return 'local-ldap';
        }

        return '';
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['ProductName'] = $this->getPlatform()->getDatabase('configuration')->getProp('sysconfig', 'ProductName');
        if( ! $view['AdRealm']) {
            $view['AdRealm'] = 'ad.' . $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        }
        if($view['UpgradeType'] == 'local-ad') {
            $view['NsdcBridgeDatasource'] = $this->getNsdcBridgeDatasource();
            if(!$view['NsdcBridgeDatasource']) {
                $view['NetworkModule'] = array($view->getModuleUrl('../Prepare?configureNetwork'), $view->translate('NetworkModule_label'));
                $this->notifications->warning($view->translate('UpgradeTypeLocalAdNoBridge_error'));
            }
        } elseif($view['UpgradeType'] == 'remote-ad') {
            $view['WorkgroupModule'] = array($view->getModuleUrl('../Prepare?configureWorkgroup'), $view->translate('WorkgroupModule_label'));
            $this->notifications->error($view->translate('UpgradeTypeRemoteAd_error'));
        }
        if($this->getRequest()->isValidated()) {
            $view->getCommandList()->show();
            if($this->getRequest()->isMutation()) {
                $this->getPlatform()->setDetachedProcessCondition('success', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('/UpgradeTool/Reset?prepareSuccess'),
                        'freeze' => TRUE,
                )));
                $this->getPlatform()->setDetachedProcessCondition('failure', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('../Prepare?prepareFailure&taskId={taskId}'),
                        'freeze' => TRUE,
                )));
            }
        }
        if ($this->getRequest()->hasParameter('prepareFailure')) {
            $taskStatus = $this->systemTasks->getTaskStatus($this->getRequest()->getParameter('taskId'));
            $data = \Nethgui\Module\Tracker::findFailures($taskStatus);
            $this->notifications->trackerError($data);
        } elseif($this->getRequest()->hasParameter('resetSuccess')) {
            $this->notifications->notice($view->translate('UpgradeToolResetSuccess_notification'));
            $view->getCommandList()->show();
        } elseif($this->getRequest()->hasParameter('configureNetwork')) {
            $view->getCommandList('/Main')->sendQuery($view->getModuleUrl('/NetworkAdapter'));
        }
    }

    public function process() 
    {
        parent::process();
        if($this->getRequest()->isMutation()) {
            $this->getPlatform()->signalEvent('nethserver-upgrade-tool-prepare &');
        }
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
