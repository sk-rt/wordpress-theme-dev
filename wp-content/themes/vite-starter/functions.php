<?php

namespace Theme\Init;

// ブロックエディターを使用するか。使用する場合は投稿タイプを指定する。
define('EDITOR_SETTING_USE_BLOCK_EDITOR', ['post']);
// TODO: use autoloader
require_once __DIR__ . '/include/settings/GlobalSetting.php';
require_once __DIR__ . '/include/controllers/CommonController.php';
require_once __DIR__ . '/include/controllers/AssetsController.php';
require_once __DIR__ . '/include/controllers/MetaController.php';
require_once __DIR__ . '/include/postTypes/Post.php';
require_once __DIR__ . '/include/postTypes/Product.php';
require_once __DIR__ . '/include/functions/TemplateTags.php';
require_once __DIR__ . '/include/functions/Logger.php';
require_once __DIR__ . '/include/admin/AdminMenu.php';
require_once __DIR__ . '/include/customFields/Loader.php';

\Theme\Settings\GlobalSettings::init();
\Theme\Controllers\CommonController::init();
\Theme\Controllers\AssetsController::init();
\Theme\Controllers\MetaController::init();
\Theme\PostTypes\Post::init();
\Theme\PostTypes\Product::init();
\Theme\Admin\AdminMenu::init();
\Theme\CustomFields\Loader::init();
