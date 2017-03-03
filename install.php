<?php

// set default config values
if (!$this->hasConfig('title_delimeter')) {
    $this->setConfig('title_delimeter', '-');
}

if (!$this->hasConfig('url_start')) {
    $this->setConfig('url_start', '/');
}

if (!$this->hasConfig('url_ending')) {
    $this->setConfig('url_ending', '.html');
}

if (!$this->hasConfig('css_dir')) {
    $this->setConfig('css_dir', '/resources/css/');
}

if (!$this->hasConfig('js_dir')) {
    $this->setConfig('js_dir', '/resources/js/');
}

if (!$this->hasConfig('image_dir')) {
    $this->setConfig('image_dir', '/resources/images/');
}

if (!$this->hasConfig('favicon_dir')) {
    $this->setConfig('favicon_dir', '/resources/favicons/');
}

if (!$this->hasConfig('offline_404_mode')) {
    $this->setConfig('offline_404_mode', 1);
}

if (!$this->hasConfig('developer_project_sync')) {
    $this->setConfig('developer_project_sync', 1);
}

if (!$this->hasConfig('smart_redirects')) {
    $this->setConfig('smart_redirects', 1);
}

if (!$this->hasConfig('xcore_styles')) {
    $this->setConfig('xcore_styles', 1);
}

if (!$this->hasConfig('show_multiupload_pages')) {
    $this->setConfig('show_multiupload_pages', 1);
}
