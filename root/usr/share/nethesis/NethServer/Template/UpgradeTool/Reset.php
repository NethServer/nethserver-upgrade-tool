<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('ProductName')->setAttribute('template', $T('Reset_header'));

echo sprintf('<p class="information">%s</p>', htmlspecialchars($T('ResetInfo1_text')));
echo sprintf('<p class="information">%s</p>', htmlspecialchars($T('ResetInfo2_text')));

echo $view->buttonList()
    ->insert($view->button('DoUpgradeReset', $view::BUTTON_SUBMIT))
    ->insert($view->button('RebootModule', $view::BUTTON_LINK))
;
    

$view->includeCss('
#UpgradeTool_Reset .information {
    font-size: 1.2em;
    max-width: 505px;
    margin-bottom: 1em;
}
');