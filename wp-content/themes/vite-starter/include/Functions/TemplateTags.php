<?php

namespace Theme\Functions;

class TemplateTags
{
    /**
     * テンプレートパーツ 取得
     * WPの `get_template_part()` を出力せずにhtmlで返す
     * @param $temp_path string テンプレートパス}
     */
    public static function getTemplatePartString(string $temp_path): string
    {
        ob_start();
        $view = get_template_part($temp_path);
        $view = ob_get_contents() ?? '';
        ob_end_clean();
        return $view;
    }
    /**
     * コンポーネントを埋め込み
     * @param string $path
     * @param array $args
     */
    public static function includeComponent(string $path, $args = [])
    {
        $path = 'template-parts/' . $path;
        get_template_part($path, null, $args);
    }
    /**
     * コンポーネントのPropsを取得
     */
    public static function getArg(string $name, mixed $default, mixed $args)
    {
        return isset($args[$name]) ? $args[$name] : $default;
    }
}
