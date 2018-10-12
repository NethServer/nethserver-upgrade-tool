<?php

/* @var $view \Nethgui\Renderer\Xhtml */

$view->requireFlag($view::INSET_DIALOG);

echo $view->header()->setAttribute('template', $T('Reboot_header'));

echo sprintf('<p class="information">%s</p>', htmlspecialchars($T('RebootInfo_text')));

echo $view->buttonList($view::BUTTON_CANCEL)
    ->insert($view->button('RebootModule', $view::BUTTON_SUBMIT));