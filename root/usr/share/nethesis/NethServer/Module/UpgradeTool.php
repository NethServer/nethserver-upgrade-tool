<?php
namespace NethServer\Module;
/*
 * Copyright (C) 2018 Nethesis Srl
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * NethServer major distro upgrade Tool UI
 *
 * This module was copied from ns7 SssdConfig.php
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class UpgradeTool extends \Nethgui\Controller\CompositeController
{
    protected $firstModuleIdentifier;
    
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(
            'languageCatalog' => array('NethServer_Module_UpgradeTool'),
            'category' => 'Configuration')
        );
    }

    public function initialize()
    {
        parent::initialize();
        $this->loadChildrenDirectory();
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $provider = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');

        $firstModuleIdentifier = 'Prepare';
        if(file_exists('/var/lib/system-upgrade/package.list')) {
            $firstModuleIdentifier = 'Reset';
        }
        $this->firstModuleIdentifier = $firstModuleIdentifier;

        // Sort children so that if the Provider prop is "none", it starts the Wizard:
        $this->sortChildren(function ($a, $b) use ($firstModuleIdentifier) {
            if($a->getIdentifier() === $firstModuleIdentifier) {
                $c = -1;
            } elseif($b->getIdentifier() === $firstModuleIdentifier) {
                $c = 1;
            } else {
                $c = 0;
            }
            return $c;
        });

        parent::bind($request);
        if (is_null($this->currentAction)) {
            $action = $this->getAction($firstModuleIdentifier);
            $action->bind($request->spawnRequest($firstModuleIdentifier));
        }
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if (is_null($this->currentAction)) {
            $action = $this->getAction($this->firstModuleIdentifier);
            if ($action instanceof \Nethgui\Controller\RequestHandlerInterface) {
                $action->validate($report);
            }
        }
        parent::validate($report);
    }

}
