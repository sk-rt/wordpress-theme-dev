<?php

namespace EditorSetting\classes;

class ClassicEditor
{
    private static $instance = null;

    private function __construct()
    {

        add_filter('quicktags_settings', [$this, 'cutomizeHtmlButtons'], 10, 1);
        add_filter('tiny_mce_before_init', [$this, 'updateTinymceSetting'], 10, 1);
        add_action('wp_terms_checklist_args', [$this, 'categoryTermsChecklistNoTop'], 10, 1);
        add_action('after_setup_theme', [$this, 'addEditorStyles'], 10);
        add_filter('embed_handler_html', [$this, 'formatVideoEmbed'], 10, 1);
        add_filter('embed_oembed_html', [$this, 'formatVideoEmbed'], 10, 1);
        add_filter('wp_kses_allowed_html', [$this, 'addAllowedHtml'], 10, 1);
        add_filter('use_default_gallery_style', '__return_false');


        $this->cutomizeTinymceButtons();
        $this->removeRichEditor();
    }
    public static function getInstance(): self
    {
        $class = get_called_class();
        if (!isset(self::$instance)) {
            self::$instance = new $class;
        }

        return self::$instance;
    }
    /**
     * Classic Editorの投稿画面から不要なメタボックスを削除
     */
    public function removeDefaultPostMetaboxes()
    {
        remove_meta_box('postcustom', 'post', 'normal'); // カスタムフィールド
        remove_meta_box('commentstatusdiv', 'post', 'normal'); // ディスカッション
        remove_meta_box('commentsdiv', 'post', 'normal'); // コメント
        remove_meta_box('trackbacksdiv', 'post', 'normal'); // トラックバック
        remove_meta_box('slugdiv', 'post', 'normal'); // スラッグ
    }

    /**
     *  TinyMCE 設定変更
     *  @see https://www.tiny.cloud/docs/configure/content-formatting/
     *  @param   array  $settings  The array of editor settings
     *  @return  array             The modified edit settings
     */
    public function updateTinymceSetting($init_array)
    {
        $init_array['paste_remove_styles'] = true;
        $init_array['paste_remove_spans'] = true;
        $init_array['block_formats'] = __('Paragraph') . '=p; ' . __('Heading 2') . '=h2; ' . __('Heading 3') . '=h3;';
        return $init_array;
    }
    /**
     * TinyMCE ボタンを削除
     * @see http://cly7796.net/wp/cms/delete-button-of-widgwig-at-wordpress/
     */
    public function cutomizeTinymceButtons()
    {
        add_filter('mce_buttons', function ($buttons) {
            return [
                'formatselect',
                'bold',
                'italic',
                'link',
                'unlink',
                'bullist',
                'numlist',
                'hr',
            ];
        }, 10, 1);
        add_filter('mce_buttons_2', '__return_empty_array');
        add_filter('mce_buttons_3', '__return_empty_array');
    }
    /**
     * Classic editorのテキストディタのボタン削除
     */
    public function cutomizeHtmlButtons($qt_init)
    {
        // 削除するボタンを指定
        $remove = [
            // 'strong', // b
            'em', // i
            // 'link',   // link
            'block', // b-quote
            // 'del',    // del
            'ins', // ins
            'img', // img
            'ul', // ul
            'ol', // ol
            'li', // li
            'code', // code
            'more', // more
            // 'close',  // タグを閉じる
            // 'dfw',    // 集中執筆モード
        ];
        // ボタンの一覧を文字列から配列に分割
        $qt_init['buttons'] = explode(',', $qt_init['buttons']);
        // 指定したボタンを削除
        $qt_init['buttons'] = array_diff($qt_init['buttons'], $remove);
        // 配列から文字列に連結
        $qt_init['buttons'] = implode(',', $qt_init['buttons']);
        return $qt_init;
    }
    /**
     * ビジュアルエディタ無効
     */
    public function removeRichEditor()
    {
        $html_only_pos_types = ['page', 'mw-wp-form'];

        /**
         * ビジュアルエディタボタンを削除
         */
        $callback = function () use ($html_only_pos_types) {
            global $typenow;
            if (in_array($typenow, $html_only_pos_types)) {
                add_filter('user_can_richedit', '__return_false');
            }
        };
        add_action('load-post.php', $callback);
        add_action('load-post-new.php', $callback);

        /**
         * 自動整形をしない
         */
        add_filter('the_content', function ($content) use ($html_only_pos_types) {
            global $post;
            $post_type = get_post_type($post->ID);
            if (in_array($post_type, $html_only_pos_types)) {
                remove_filter('the_content', 'wpautop');
                remove_filter('the_excerpt', 'wpautop');
            }
            return $content;
        }, 9);
    }
    /**
     * カテゴリーの順番が変わるの機能を削除
     */
    public function categoryTermsChecklistNoTop($args)
    {
        $args['checked_ontop'] = false;
        return $args;
    }
    /**
     * TODO: これは動作してない
     */
    public function addEditorStyles()
    {
        add_editor_style('admin-assets/editor-style.css');
    }
    /**
     * srcsetのショートコードが展開される様に
     */
    public function addAllowedHtml($tags)
    {
        $tags['img']['srcset'] = true;
        $tags['source']['srcset'] = true;

        return $tags;
    }
}
