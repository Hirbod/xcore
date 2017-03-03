<div id="rexx-setup">
<?php

$content = '<h2>' . rex_i18n::rawMsg('xcore_setup_msg1') . '</h2>';
$content .= '<p>' . rex_i18n::rawMsg('xcore_setup_msg1_desc') . '</p>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::rawMsg('xcore_setup_step1'));
$fragment->setVar('body', $content, false);

echo $fragment->parse('core/page/section.php');



$rexxContent = file_get_contents($this->getPath('install/_htaccess'));
$content = '<h2>' . rex_i18n::rawMsg('xcore_setup_msg2') . '</h2>';
$content .= '<p>' . rex_i18n::rawMsg('xcore_setup_msg2_desc') . '</p>';
$content .= '<code><pre>' . highlight_string($rexxContent, true)  . '</pre></code>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::rawMsg('xcore_setup_step2'));
$fragment->setVar('body', $content, false);

echo $fragment->parse('core/page/section.php');



$rexxContent = file_get_contents($this->getPath('install/template.php'));
$content = '<h2>' . rex_i18n::rawMsg('xcore_setup_msg3') . '</h2>';
$content .= '<p>' . rex_i18n::rawMsg('xcore_setup_msg3_desc') . '</p>';
$content .= '<code><pre>' . highlight_string($rexxContent, true)  . '</pre></code>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::rawMsg('xcore_setup_step3'));
$fragment->setVar('body', $content, false);

echo $fragment->parse('core/page/section.php');
?>
</div>