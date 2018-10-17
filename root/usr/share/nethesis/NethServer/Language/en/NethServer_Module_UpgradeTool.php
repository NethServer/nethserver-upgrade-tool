<?php

$L['UpgradeTool_Description'] = 'Upgrade the system to the next major release'; 
$L['UpgradeTool_Tags'] = 'upgrade version 7 system update next release'; 
$L['UpgradeTool_Title'] = 'Upgrade tool';
$L['Prepare_header'] = 'Prepare for the upgrade to ${0} 7';
$L['Reset_header'] = 'Upgrade to ${0} 7';
$L['ResetInfo1_text'] = 'The upgrade procedure will start upon the next system reboot and the progress will be available from the system console.';
$L['ResetInfo2_text'] = 'It is possible to abort and clean up the upgrade, or to reset the system to the usual state';
$L['DoUpgradeReset_label'] = 'Abort and clean up';
$L['RebootModule_label'] = 'Reboot and upgrade';
$L['UpgradeToolPrepareSuccess_notification'] = 'The upgrade procedure is ready to start';
$L['UpgradeToolResetSuccess_notification'] = 'Clean up completed. The system was reset its the original state';
$L['StartPrepare_label'] = 'Prepare upgrade';

$L['UpgradeTypeLocalAd_text'] = 'The current Samba NT domain must be upgraded to Active Directory';
$L['UpgradeTypeLocalAdNoBridge_text'] = 'Create a new green bridge then come back to this page';
$L['UpgradeTypeLocalAdNoBridge_error'] = 'To proceed with the upgrade a green bridge must be created';
$L['NetworkModule_label'] = 'Configure Network';
$L['AdRealm_label'] = 'DNS domain name';
$L['AdWorkgroup_label'] = 'NetBIOS domain name';
$L['AdIpAddress_label'] = "Domain Controller IP address";
$L['valid_platform,dcipaddr,ipgreenandbridged,3'] = 'Must be different from 127.0.0.1 or any other IP address used by the server';
$L['valid_platform,dcipaddr,ipgreenandbridged,4'] = 'Must be part of a green network';
$L['valid_platform,dcipaddr,ipgreenandbridged,5'] = 'Must be a free IP address';
$L['AdIpAddress_help1'] = 'The chosen IP address must satisfy all of the below conditions:';
$L['AdIpAddress_help2'] = 'the IP address must be in the same subnet range of the selected bridge';
$L['AdIpAddress_help3'] = 'the IP address must be unused currently';
$L['NsdcBridge_label'] = 'Bridge interface';
$L['UpgradeTypeRemoteAd_text'] = 'The current system configuration cannot be upgraded: contact ${0} support';
$L['UpgradeTypeRemoteAd_error'] = 'Cannot upgrade the Active Directory member server role';

$L['Reboot_header'] = 'Reboot and start upgrade';
$L['RebootInfo_text'] = 'Reboot the system now';

$L['DiskSpace_label'] = 'Disk space error';
$L['valid_platform,diskspace,upgradediskspace,3'] = 'Not enough free space in the / (root) partition (${reason})';
$L['valid_platform,diskspace,upgradebootspace,3'] = 'Not enough free space in the /boot partition (${reason})';