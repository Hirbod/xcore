<?php

class seo42_utils {
	public static function appendToPageHeader($params) {
		global $REX;

		$insert = '<!-- BEGIN seo42 -->' . PHP_EOL;
		$insert .= '<link rel="stylesheet" type="text/css" href="../' . $REX['MEDIA_ADDON_DIR'] . '/seo42/seo42.css" />' . PHP_EOL;
		$insert .= '<!-- END seo42 -->';
	
		return $params['subject'] . PHP_EOL . $insert;
	}

	public static function init($params) {
		global $REX;

		// init globals
		seo42::init();

		if ($REX['MOD_REWRITE']) {
			// includes
			require_once($REX['INCLUDE_PATH'] . '/addons/seo42/classes/class.rexseo_rewrite.inc.php');

			if ($REX['REDAXO']) { // this is only necessary for backend
				$extensionPoints = array(
					'CAT_ADDED',     'CAT_UPDATED',     'CAT_DELETED',
					'ART_ADDED',     'ART_UPDATED',     'ART_DELETED',        'ART_META_FORM_SECTION',
					'ART_TO_CAT',    'CAT_TO_ART',      'ART_TO_STARTPAGE',
					'CLANG_ADDED',   'CLANG_UPDATED',   'CLANG_DELETED',
					'ALL_GENERATED'
				);

				// generate pathlist on each extension point
				foreach($extensionPoints as $extensionPoint) {
					rex_register_extension($extensionPoint, 'rexseo_generate_pathlist');
				}
			}

			// init rewriter 
			$rewriter = new RexseoRewrite($REX['ADDON']['seo42']['settings']['levenshtein'], $REX['ADDON']['seo42']['settings']['rewrite_params']);
			$rewriter->resolve();

			// rewrite ep 
			rex_register_extension('URL_REWRITE', array ($rewriter, 'rewrite'));
		}

		// init current article
		seo42::initArticle($REX['ARTICLE_ID']);

		// set noindex header for robots if current article has noindex flag
		if (!$REX['REDAXO'] && seo42::isArticleValid() && seo42::hasNoIndexFlag()) {
			header('X-Robots-Tag: noindex, noarchive');
		}

		// controller
		include($REX['INCLUDE_PATH'] . '/addons/seo42/controller.inc.php');

		// rexseo post init
		rex_register_extension_point('REXSEO_INCLUDED');
	}

	public static function afterDBImport($params) {
		global $REX, $I18N;

		$sqlStatement = 'SELECT seo_title, seo_description, seo_keywords, seo_custom_url, seo_canonical_url, seo_noindex, seo_ignore_prefix FROM ' . $REX['TABLE_PREFIX'] . 'article';
		$sql = rex_sql::factory();
		$sql->setQuery($sqlStatement);

		// check for db fields
		if ($sql->getRows() == 0) {
			require($REX['INCLUDE_PATH'] . '/addons/seo42/install.inc.php');
			echo rex_info($I18N->msg('seo42_dbfields_readded', $REX['ADDON']['name']['seo42']));
			echo rex_info($I18N->msg('seo42_dbfields_readded_check_setup', $REX['ADDON']['name']['seo42']));
		}
	}

	public static function showMsgAfterClangModified($params) {
		global $I18N, $REX;

		echo rex_info($I18N->msg('seo42_check_lang_msg', $REX['ADDON']['name']['seo42']));
	}

	public static function addSEOPageToPageContentMenu($params) {
		global $I18N;
			
		$class = "";

		if ($params['mode']  == 'seo') {
			$class = 'class="rex-active"';
		}

		$seoLink = '<a '.$class.' href="index.php?page=content&amp;article_id=' . $params['article_id'] . '&amp;mode=seo&amp;clang=' . $params['clang'] . '&amp;ctype=' . rex_request('ctype') . '">' . $I18N->msg('seo42_seopage_linktext') . '</a>';
		array_splice($params['subject'], '-2', '-2', $seoLink);

		return $params['subject'];
	}

	public static function addSEOPageToPageContentOutput($params) {
		global $REX, $I18N;

		if ($params['mode']  == 'seo') {
			include($REX['INCLUDE_PATH'] . '/addons/seo42/pages/seopage.inc.php');
		}
	}

	public static function addURLPageToPageContentMenu($params) {
		global $I18N;
			
		$class = "";

		if ($params['mode']  == 'url') {
			$class = 'class="rex-active"';
		}

		$seoLink = '<a ' . $class . ' href="index.php?page=content&amp;article_id=' . $params['article_id'] . '&amp;mode=url&amp;clang=' . $params['clang'] . '&amp;ctype=' . rex_request('ctype') . '">' . $I18N->msg('seo42_urlpage_linktext') . '</a>';
		array_splice($params['subject'], '-2', '-2', $seoLink);

		return $params['subject'];
	}

	public static function addURLPageToPageContentOutput($params) {
		global $REX, $I18N;

		if ($params['mode']  == 'url') {
			include($REX['INCLUDE_PATH'] . '/addons/seo42/pages/urlpage.inc.php');
		}
	}

	public static function enableSEOPage() {
		global $REX;

		if ($REX['ADDON']['seo42']['settings']['seopage']) {
			rex_register_extension('PAGE_CONTENT_MENU', 'seo42_utils::addSEOPageToPageContentMenu');
			rex_register_extension('PAGE_CONTENT_OUTPUT', 'seo42_utils::addSEOPageToPageContentOutput');
		}
	}

	public static function enableURLPage() {
		global $REX;

		if ($REX['ADDON']['seo42']['settings']['urlpage']) {
			rex_register_extension('PAGE_CONTENT_MENU', 'seo42_utils::addURLPageToPageContentMenu');
			rex_register_extension('PAGE_CONTENT_OUTPUT', 'seo42_utils::addURLPageToPageContentOutput');
		}
	}

	public static function modifyFrontendLinkInPageContentMenu($params) {
		$lastElement = count($params['subject']) - 1;
		$params['subject'][$lastElement] = preg_replace("/(?<=href=(\"|'))[^\"']+(?=(\"|'))/", '../', $params['subject'][$lastElement]);

		return $params['subject'];
	}

	public static function sanitizeString($string) {
		return trim(preg_replace("/\s\s+/", " ", $string));
	}

	public static function sanitizeUrl($url) {
		return preg_replace('@^https?://|/.*|[^\w.-]@', '', $url);
	}

	// untested
	public static function getServerUrl() {
		$url = $_SERVER['REQUEST_URI'];
		$parts = explode('/',$url);
		$serverUrl = 'http://' . $_SERVER['SERVER_NAME'];

		for ($i = 0; $i < count($parts) - 2; $i++) {
			$serverUrl .= $parts[$i] . "/";
		}

		return $serverUrl;
	}

	public static function detectSubDir() {
		if ($_SERVER['PHP_SELF'] == '/redaxo/index.php') {
			return false;
		} else {
			return true;
		}
	}

	public static function print_r_pretty($arr, $first = true, $tab=0) {
		$output = "";
		$tabsign = ($tab) ? str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$tab) : '';

		if ($first) $output .= "<pre>";

		foreach($arr as $key => $val)
		{
		    switch (gettype($val))
		    {
		        case "array":
		            $output .= $tabsign."[".htmlspecialchars($key)."] => <br>".$tabsign."(<br>";
		            $tab++;
		            $output .= self::print_r_pretty($val,false,$tab);
		            $tab--;
		            $output .= $tabsign.")<br>";
		        break;
		        case "boolean":
		            $output .= $tabsign."[".htmlspecialchars($key)."] => ".($val?"true":"false")."<br>";
		        break;
		        case "integer":
		            $output .= $tabsign."[".htmlspecialchars($key)."] => ".htmlspecialchars($val)."<br>";
		        break;
		        case "double":
		            $output .= $tabsign."[".htmlspecialchars($key)."] => ".htmlspecialchars($val)."<br>";
		        break;
		        case "string":
		            $output .= $tabsign."[".htmlspecialchars($key)."] => ".((stristr($key,'passw')) ? str_repeat('*', strlen($val)) : htmlspecialchars($val))."<br>";
		        break;
		        default:
		            $output .= $tabsign."[".htmlspecialchars($key)."] => ".htmlspecialchars(gettype($val))."<br>";
		        break;
		    }
		}

		if ($first) $output .= "</pre>";

		return $output;
	}

	public static function autoInstallPlugins($addon, $plugins) {
		// take from xform install routine :)
		$msg = '';

		// GET ALL ADDONS & PLUGINS
		$all_addons = rex_read_addons_folder();
		$all_plugins = array();

		foreach ($all_addons as $_addon) {
			$all_plugins[$_addon] = rex_read_plugins_folder($_addon);
		}

		// DO AUTOINSTALL
		$pluginManager = new rex_pluginManager($all_plugins, $addon);

		foreach ($plugins as $pluginname) {
			if (in_array($pluginname, $all_plugins[$addon])) { // check if plugin that should be auto install really exists in addon folder
				// INSTALL PLUGIN
				if (($instErr = $pluginManager->install($pluginname)) !== true) {
					$msg = $instErr;
				}

				// ACTIVATE PLUGIN
				if ($msg == '' && ($actErr = $pluginManager->activate($pluginname)) !== true) {
					$msg = $actErr;
				}

				if ($msg != '') {
					break;
				}
			}
		}

		return $msg;
	}
}