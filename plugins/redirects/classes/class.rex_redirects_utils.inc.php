<?php
class rex_redirects_utils {
	public static function createDynFile($file) {
		$fileHandle = fopen($file, 'w');

		fwrite($fileHandle, "<?php\r\n");
		fwrite($fileHandle, "// --- DYN\r\n");
		fwrite($fileHandle, "// --- /DYN\r\n");

		fclose($fileHandle);
	}

	public static function getRedirectsFile() {
		global $REX;

		if (isset($REX['WEBSITE_MANAGER']) && $REX['WEBSITE_MANAGER']->getCurrentWebsiteId() != 1) {
			$file = 'redirects' . $REX['WEBSITE_MANAGER']->getCurrentWebsiteId() . '.inc.php';
		} else {
			$file = 'redirects.inc.php';
		}

		return $REX['INCLUDE_PATH'] . '/addons/seo42/generated/' . $file;
	}

	public static function updateRedirectsFile() {
		global $REX;

		$redirectsContent = '';
		$redirectsFile = self::getRedirectsFile();

		if (!file_exists($redirectsFile)) {
			self::createDynFile($redirectsFile);
		}

		// file content
		$redirectsContent .= '$REX[\'SEO42_REDIRECTS\'] = array(' . PHP_EOL;

		$sql = rex_sql::factory();
		//$sql->debugsql = true;
		$sql->setQuery('SELECT * FROM ' . $REX['TABLE_PREFIX'] . 'redirects');

		for ($i = 0; $i < $sql->getRows(); $i++) {
			$redirectsContent .= "\t" . '"' . $sql->getValue('source_url') . '" => "' . $sql->getValue('target_url') . '"';
		
			if ($i < $sql->getRows() - 1) {
				$redirectsContent .= ', ' . PHP_EOL;
			}

			$sql->next();
		}

		$redirectsContent .= PHP_EOL . ');' . PHP_EOL;

	  	rex_replace_dynamic_contents($redirectsFile, $redirectsContent);
	}

	public static function redirect() {
		global $REX;

		$redirectsFile = self::getRedirectsFile();

		if (file_exists($redirectsFile)) {
			include($redirectsFile);

			if (isset($REX['SEO42_REDIRECTS']) && count($REX['SEO42_REDIRECTS']) > 0 && array_key_exists($_SERVER['REQUEST_URI'], $REX['SEO42_REDIRECTS'])) {
				$targetUrl = $REX['SEO42_REDIRECTS'][$_SERVER['REQUEST_URI']];
			
				if (strpos($targetUrl, 'http') === false) {
					$location = 'http://' . $_SERVER['SERVER_NAME']  . $targetUrl;
				} else {
					$location = $targetUrl;
				}
		
				header ('HTTP/1.1 301 Moved Permanently');
			 	header ('Location: ' . $location);

				exit;
			}
		}

	}
}
