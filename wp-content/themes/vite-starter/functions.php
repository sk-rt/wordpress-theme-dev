<?php

namespace Theme\Init;

// ブロックエディターを使用するか。使用する場合は投稿タイプを指定する。
// define('EDITOR_SETTING_USE_BLOCK_EDITOR', ['post']);
define('EDITOR_SETTING_USE_BLOCK_EDITOR', false);
// TODO: use autoloader
require_once __DIR__ . '/include/Settings/GlobalSetting.php';
require_once __DIR__ . '/include/Controllers/CommonController.php';
require_once __DIR__ . '/include/Controllers/AssetsController.php';
require_once __DIR__ . '/include/Controllers/MetaController.php';
require_once __DIR__ . '/include/PostTypes/Post.php';
require_once __DIR__ . '/include/PostTypes/Product.php';
require_once __DIR__ . '/include/Functions/TemplateTags.php';
require_once __DIR__ . '/include/Functions/Logger.php';
require_once __DIR__ . '/include/Admin/AdminMenu.php';

\Theme\Settings\GlobalSettings::init();
\Theme\Controllers\CommonController::init();
\Theme\Controllers\AssetsController::init();
\Theme\Controllers\MetaController::init();
\Theme\PostTypes\Post::init();
\Theme\PostTypes\Product::init();
\Theme\Admin\AdminMenu::init();
