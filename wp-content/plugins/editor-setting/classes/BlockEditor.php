<?php

namespace EditorSetting\classes;

use \WP_Theme_JSON_Data;

class BlockEditor
{
    private static $instance = null;

    private function __construct()
    {

        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);
        add_filter('block_editor_settings_all', [$this, 'filterBlockEditorSettings'], 10, 1);
        add_filter('wp_theme_json_data_theme', [$this, 'themeJson'], 10, 1);
        add_action('init', [$this, 'unregisterCorePatterns'], 10);
    }
    public static function getInstance(): self
    {
        $class = get_called_class();
        if (!isset(self::$instance)) {
            self::$instance = new $class;
        }

        return self::$instance;
    }
    public function enqueueEditorAssets()
    {
        wp_enqueue_script(
            'unregister-block',
            EDITOR_SETTING_PLUGIN_URL . '/assets/custom-block-editor.js',
            ['wp-blocks', 'wp-dom-ready', 'wp-edit-post']
        );
    }
    /**
     * WPコアのパターンの削除
     */
    public function unregisterCorePatterns()
    {
        remove_theme_support('core-block-patterns');
    }

    /**
     * ブロックエディターの設定の上書き
     * @param array $editor_settings
     * @param array $editor_context
     * @return array
     */
    public function filterBlockEditorSettings($editor_settings)
    {
        // align full / align wide を無効化
        $editor_settings['supportsLayout'] = false;
        // 画像編集の無効化
        $editor_settings['imageEditing'] = false;
        // Openverse を無効化
        $editor_settings['enableOpenverseMediaCategory'] = false;
        return $editor_settings;
    }
    public function themeJson(WP_Theme_JSON_Data $theme_json)
    {
        $data = [
            'version'  => 2,
            'settings' => [
                'color' => [
                    'background' => false,
                    'custom' => false,
                    'customDuotone' => false,
                    'customGradient' => false,
                    'duotone' => null,
                    'gradients' => [],
                    'link' => false,
                    'palette' => [],
                    'text' => false
                ],
                'spacing' => [
                    'customMargin' => false,
                    'customPadding' => false,
                    'units' => []
                ],
                'layout' => [
                    'contentSize' => '740px',
                    'wideSize' => false
                ],
                'typography' => [
                    'customFontSize' => false,
                    'customFontStyle' => false,
                    'customFontWeight' => false,
                    'customLineHeight' => false,
                    'customTextDecorations' => false,
                    'customTextTransforms' => false,
                    'dropCap' => false,
                    'fontFamilies' => [],
                    'fontSizes' => [],
                    'fontStyle' => false,
                    'fontWeight' => false,
                    'letterSpacing' => false,
                    'textDecoration' => false,
                    'textTransform' => false
                ]
            ],
        ];

        return $theme_json->update_with($data);
    }
}
