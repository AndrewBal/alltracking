<?php

$GLOBALS['wrap'] = NULL;
$GLOBALS['device'] = NULL;
$GLOBALS['language'] = NULL;

define('LOCALE', config('app.locale'));
define('DEFAULT_LOCALE', config('app.default_locale'));
define('USE_MULTI_LANGUAGE', config('seo.use.multi_language'));
define('USE_COMPRESS', config('seo.use.compress'));
define('USE_BLOCK_SCANNING', config('seo.use.block_scanning'));
define('USE_LAST_MODIFIED', config('seo.use.last_modified'));
define('REMEMBER_LIFETIME', 3600);
