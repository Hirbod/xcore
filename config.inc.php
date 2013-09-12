<?php

// register addon
$REX['ADDON']['rxid']['rexseo42'] = '0';
$REX['ADDON']['name']['rexseo42'] = 'REXSEO42';
$REX['ADDON']['version']['rexseo42'] = '1.2.1';
$REX['ADDON']['author']['rexseo42'] = 'Markus Staab, Wolfgang Huttegger, Dave Holloway, Jan Kristinus, jdlx, RexDude';
$REX['ADDON']['supportpage']['rexseo42'] = 'forum.redaxo.de';
$REX['ADDON']['perm']['rexseo42'] = 'rexseo42[]';

// permissions
$REX['PERM'][] = 'rexseo42[]';
$REX['PERM'][] = 'rexseo42[tools_only]';
$REX['EXTPERM'][] = 'rexseo42[seo_default]';
$REX['EXTPERM'][] = 'rexseo42[seo_extended]';
$REX['EXTPERM'][] = 'rexseo42[url_default]';

// includes
require($REX['INCLUDE_PATH'] . '/addons/rexseo42/classes/class.rexseo42.inc.php');
require($REX['INCLUDE_PATH'] . '/addons/rexseo42/classes/class.rexseo42_utils.inc.php');
require($REX['INCLUDE_PATH'] . '/addons/rexseo42/settings.dyn.inc.php');
require($REX['INCLUDE_PATH'] . '/addons/rexseo42/settings.advanced.inc.php');
require($REX['INCLUDE_PATH'] . '/addons/rexseo42/settings.lang.inc.php');

// fix for iis webserver: set request uri manually if not available
if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

	if (isset($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

// init
if (!$REX['SETUP']) {
	// auto mod rewrite, but not for redaxo system page
	if (!$REX['REDAXO'] || ($REX['REDAXO'] && rex_request('page') != 'specials')) {
		$REX['MOD_REWRITE'] = true;
	}
	
	// init 42
	rex_register_extension('ADDONS_INCLUDED','rexseo42_utils::init', '', REX_EXTENSION_EARLY);
}

if ($REX['REDAXO']) {
	// append lang file
	$I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/rexseo42/lang/');

	// handels ajax request for google pagerank checker in tools section
	if ($REX['ADDON']['rexseo42']['settings']['pagerank_checker'] && rex_request('function') == 'getpagerank') {
		require($REX['INCLUDE_PATH'] . '/addons/rexseo42/classes/class.google_pagerank_checker.inc.php');
		echo GooglePageRankChecker::getRank(rex_request('url'));
		exit;
	}

	// subpages
	if (isset($REX['USER']) && !$REX['USER']->isAdmin() && $REX['USER']->hasPerm('rexseo42[tools_only]')) {
		// add tools page only
		$REX['ADDON']['rexseo42']['SUBPAGES'] = array(
			array('', $I18N->msg('rexseo42_tools'))
		);
	} else {
		// add subpages
		$REX['ADDON']['rexseo42']['SUBPAGES'] = array(
			array('', $I18N->msg('rexseo42_welcome')),
			array('tools', $I18N->msg('rexseo42_tools'))
		);

		if (OOPlugin::isAvailable('rexseo42', 'redirects')) {
			array_push($REX['ADDON']['rexseo42']['SUBPAGES'], array('redirects', $I18N->msg('rexseo42_redirects')));
		}

		array_push($REX['ADDON']['rexseo42']['SUBPAGES'], 
			array('options', $I18N->msg('rexseo42_settings')),
			array('setup', $I18N->msg('rexseo42_setup')),
			array('help', $I18N->msg('rexseo42_help'))
		);
	}

	// add css/js files to page header
	if (rex_request('page') == 'rexseo42') {
		rex_register_extension('PAGE_HEADER', 'rexseo42_utils::appendToPageHeader');
	}

	// check if seopage/urlpage needs to be enabled
	if (!$REX['ADDON']['rexseo42']['settings']['one_page_mode'] || ($REX['ADDON']['rexseo42']['settings']['one_page_mode'] && $REX['ARTICLE_ID'] == $REX['START_ARTICLE_ID'])) {
		if (isset($REX['USER']) && ($REX['USER']->isAdmin())) {
			// admins get everything :)
			rexseo42_utils::enableSEOPage();
			rexseo42_utils::enableURLPage();
		} elseif (isset($REX['USER']) && ($REX['USER']->hasPerm('rexseo42[seo_default]') || $REX['USER']->hasPerm('rexseo42[seo_extended]') || $REX['USER']->hasPerm('editContentOnly[]'))) {
			rexseo42_utils::enableSEOPage();
		} elseif (isset($REX['USER']) && $REX['USER']->hasPerm('rexseo42[url_default]')) {
			rexseo42_utils::enableURLPage();
		}
	}

	// for one page mode link to frontend is always "../"
	if ($REX['ADDON']['rexseo42']['settings']['one_page_mode'] && $REX['ARTICLE_ID'] != $REX['START_ARTICLE_ID']) {
		rex_register_extension('PAGE_CONTENT_MENU', 'rexseo42_utils::modifyFrontendLinkInPageContentMenu');
	}

	// check for missing db fields after db import
	if (!$REX['SETUP']) {
		rex_register_extension('A1_AFTER_DB_IMPORT', 'rexseo42_utils::afterDBImport');
	}

	// if clang is added/deleted show message to the user that he should check his lang settings
	rex_register_extension('CLANG_ADDED', 'rexseo42_utils::showMsgAfterClangModified');
	rex_register_extension('CLANG_DELETED', 'rexseo42_utils::showMsgAfterClangModified');
}

