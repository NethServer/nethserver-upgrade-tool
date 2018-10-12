<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('ProductName')->setAttribute('template', $T('Prepare_header'));

$isSubmitDisabled = 0;

if($view['UpgradeType'] == 'local-ad' && $view['NsdcBridgeDatasource']) {
    echo sprintf('<p class="information">%s</p>', $T('UpgradeTypeLocalAd_text'));
    echo $view->textInput('AdRealm');
    echo $view->textInput('AdWorkgroup', $view::STATE_DISABLED);
    $AdIpAddressId = $view->getUniqueId('AdIpAddress');
    $labelOpenTag = "<label for='$AdIpAddressId'>";
    $help = '<div class="dcalert notification bg-yellow">
      <p>' . $labelOpenTag . '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> ' . htmlspecialchars($T('AdIpAddress_help1')) . '</label></p>
      <ul>
        <li>' . $labelOpenTag . htmlspecialchars($T('AdIpAddress_help2')) . '</label></li>
        <li>' . $labelOpenTag . htmlspecialchars($T('AdIpAddress_help3')) . '</label></li>
      </ul>
    </div>';

    echo $view->selector('NsdcBridge',$view::SELECTOR_DROPDOWN);
    echo $view->textInput('AdIpAddress');
    echo $help;

} if($view['UpgradeType'] == 'local-ad' && ! $view['NsdcBridgeDatasource']) {
    $isSubmitDisabled = $view::STATE_DISABLED;
    echo $view->columns()
        ->insert($view->literal(sprintf('<p class="information">%s</p>', $T('UpgradeTypeLocalAdNoBridge_text'))))
        ->insert($view->button('NetworkModule', $view::BUTTON_LINK))
    ;
} elseif($view['UpgradeType'] == 'remote-ad') {
    $isSubmitDisabled = $view::STATE_DISABLED;
    echo $view->literal(sprintf('<p class="information">%s</p>', $T('UpgradeTypeRemoteAd_text', array($view['ProductName']))));
}

$view->includeCss("
#UpgradeTool_Prepare .information {
    font-size: 1.2em;
    margin-bottom: 1em;
}

.dcalert {
    color: #000;
    background-color: #F4D622;
    border: 1px solid #F4D622;
    border-radius: 2px;
    padding: 15px;
    margin: 10px;
    position: relative;
}
.dcalert:before {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 20px;
  width: 0;
  border-bottom: 18px solid #F4D622;
  border-left: 18px solid transparent;
  border-right: 18px solid transparent;
}
.notification.bg-yellow {color: #000; background-color: #FFB600; border-color: #F4D622 }
.notification.bg-yellow a {color: #000}
.dcalert ul {
    list-style-type: disc;
    margin-left: 25px;
}
");



echo $view->buttonList()
    ->insert($view->button('StartPrepare', $view::BUTTON_SUBMIT | $isSubmitDisabled))
;
