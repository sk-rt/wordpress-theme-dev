<?php
/*
Plugin Name: エディター設定
Plugin URI:
Description: Classic / Block エディターの設定
Version: 1.0
Author: Ryuta Sakai
Author URI:
 */

namespace EditorSetting;

if (!defined('ABSPATH')) {
    exit;
}
require_once __DIR__ . '/classes/ClassicEditor.php';
require_once __DIR__ . '/classes/BlockEditor.php';
require_once __DIR__ . '/classes/DisabledBlockEditor.php';

add_action('after_setup_theme', 'EditorSetting\main', 10);

function main()
{
    define('EDITOR_SETTING_PLUGIN_URL', plugins_url('', __FILE__), false);
    if (!defined('EDITOR_SETTING_USE_BLOCK_EDITOR')) {
        define('EDITOR_SETTING_USE_BLOCK_EDITOR', false); // ブロックエディターを使用するか bool or [post_type]
    }
    classes\ClassicEditor::getInstance();
    if (EDITOR_SETTING_USE_BLOCK_EDITOR) {
        classes\BlockEditor::getInstance();
    }
    $arrowed_post_types = is_array(EDITOR_SETTING_USE_BLOCK_EDITOR) ? EDITOR_SETTING_USE_BLOCK_EDITOR : [];
    classes\DisabledBlockEditor::getInstance($arrowed_post_types);
}
