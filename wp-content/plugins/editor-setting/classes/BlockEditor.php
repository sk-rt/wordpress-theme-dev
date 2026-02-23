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
    /**
     * テーマJSONの上書き
     * @see https://make.wordpress.org/core/2024/06/19/theme-json-version-3/
     */
    public function themeJson(WP_Theme_JSON_Data $theme_json)
    {
        $data = [
            'version'  => 3,
            'settings' => [
                'border' => [
                    'radius' => false,
                    'color' => false,
                    'style' => false,
                    'width' => false
                ],
                'color' => [
                    'background' => false,
                    'custom' => false,
                    'customDuotone' => false,
                    'customGradient' => false,
                    'defaultGradients' => false,
                    'defaultPalette' => false,
                    'duotone' => null,
                    'gradients' => null,
                    'link' => false,
                    'palette' => [],
                    'text' => false
                ],
                'spacing' => [
                    'blockGap' => null,
                    'defaultSpacingSizes' => false,
                    'margin' => false,
                    'padding' => false,
                    'spacingSizes' => [],
                    'units' => []
                ],
                'layout' => [
                    'contentSize' => '740px',
                    'wideSize' => null
                ],
                'typography' => [
                    'defaultFontSizes' => false,
                    'customFontSize' => false,
                    'fontStyle' => false,
                    'fontWeight' => false,
                    'lineHeight' => false,
                    'letterSpacing' => false,
                    'textDecoration' => false,
                    'textTransform' => false,
                    'dropCap' => false,
                    'fontFamilies' => [],
                    'fontSizes' => []
                ],
                'blocks' => [
                    'core/button' => [
                        'border' => [
                            'radius' => false,
                            'width' => false
                        ]
                    ],
                    'core/image' => [
                        'color' => [
                            'duotone' => null
                        ]
                    ],
                ]
            ],

        ];

        return $theme_json->update_with($data);
    }
}
