<?php

namespace Theme\Init;

require_once 'vendor/autoload.php';

// ブロックエディターを使用するか。使用する場合は投稿タイプを指定する。
// define('EDITOR_SETTING_USE_BLOCK_EDITOR', ['post']);
define('EDITOR_SETTING_USE_BLOCK_EDITOR', false);

\Theme\Settings\GlobalSettings::init();
// Controllers
\Theme\Controllers\CommonController::init();
\Theme\Controllers\AssetsController::init();
\Theme\Controllers\MetaController::init();
// Custom Post
\Theme\PostTypes\Post::init();
\Theme\PostTypes\Product::init();
// Admin
\Theme\Admin\AdminMenu::init();
