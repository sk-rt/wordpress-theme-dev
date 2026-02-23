# post Content Sync

静的HTMLファイルからWordPress投稿へコンテンツを同期するプラグイン

このプラグインは、Git管理されたHTMLファイルをWordPressのデータベースに同期します。多言語コンテンツ管理とエディターによる管理画面からのコンテンツ編集を可能にします。

## インストール

### 1. Composer依存関係のインストール

```bash
cd wp-content/plugins/post-content-sync
composer install
```

### 2. プラグインの有効化

WordPress管理画面から「Post Content Sync」プラグインを有効化します。

## 設定

デフォルト設定は `config/default.php` に定義されています。

設定をカスタマイズする場合は、`functions.php` などでフィルターを使用します：

```php
add_filter('post-content-sync/config', function ($config) {
    $config['base_directory'] = get_template_directory() . '/custom-posts';
    $config['prefix'] = 'custom';
    return $config;
});
```

## カスタムメタデータハンドラー

プラグインはカスタムメタデータを処理するためのフィルターフックを提供します。これにより、HTMLファイル内の特殊なメタデータ（例：多言語プラグインのロケール設定）を処理できます。

### フィルターフックの使用

`functions.php` でフィルターを登録します：

```php
add_filter('post-content-sync/metadata_handlers', function ($handlers) {
    // カスタムハンドラーを追加
    $handlers['custom:key'] = function ($value, $postId) {
        // メタデータを処理
        update_post_meta($postId, '_custom_key', $value);
    };

    return $handlers;
});
```

### HTMLファイルでの使用

HTMLファイル内のメタタグで、WordPressの投稿データを指定します。

#### メタタグのスキーマ

メタタグは `{prefix}:{カテゴリ}:{キー}` の形式で命名します（デフォルトprefix: `psc`）：

- **`psc:post:{key}`** - WordPress投稿の標準フィールド
  - `post_type`: 投稿タイプ（必須）
  - `post_title`: タイトル
  - `post_status`: 公開ステータス（`publish`, `draft`, `pending`, `private`）
  - `post_date`: 公開日時
  - など

- **`psc:identify:{key}`** - 既存投稿を特定するための識別子（いずれか1つ必須）
  - `id`: 投稿ID（最優先）
  - `slug`: スラッグ
  - `path`: ページパス（階層構造に対応）

- **`psc:postmeta:{key}`** - カスタムフィールド
  - 任意のカスタムフィールドを指定可能

- **`psc:content`** - コンテンツ本文を含む要素のID属性
  - この要素内のHTMLが投稿本文として保存されます

#### 使用例

```html
<!DOCTYPE html>
<html>
  <head>
    <meta name="psc:post:post_type" content="page" />
    <meta name="psc:identify:slug" content="about" />
    <meta name="psc:post:post_title" content="私たちについて" />
    <meta name="psc:post:post_status" content="publish" />
    <meta name="psc:postmeta:custom_field" content="カスタム値" />
  </head>
  <body>
    <div id="psc:content">
      <h1>私たちについて</h1>
      <p>コンテンツ...</p>
    </div>
  </body>
</html>
```

## 使用方法

### WP-CLIコマンド

```bash
# ファイルをスキャン（現在の実装）
wp content-sync sync


```

### 管理画面

**ツール > Content Sync** から設定とファイル一覧を確認できます。

## ディレクトリ構造

```
post-content-sync/
├── post-content-sync.php    # メインファイル
├── composer.json              # Composer設定
├── config/
│   └── default.php            # デフォルト設定
├── src/
│   ├── Plugin.php             # メインプラグインクラス
│   ├── FileScanner.php        # ファイルスキャナー
│   ├── Admin/
│   │   ├── AdminPage.php      # 管理画面
│   │   └── AjaxHandler.php    # Ajax処理
│   └── CLI/
│       └── SyncCommand.php    # WP-CLIコマンド
└── vendor/                    # Composer依存関係
```

## HTMLファイルの配置

デフォルトでは以下のディレクトリにHTMLファイルを配置します：

```
themes/your-theme/_static-posts/pages/
├── company.html
└── about.html
```

## 開発

### コーディング規約

- PSR-2準拠
- PSR-4オートローディング

### コードチェック

```bash
# コーディングスタイルチェック
composer cs

# 自動修正
composer cs-fix

# 静的解析
composer analyse
```

## ライセンス

MIT License

## 作成者

Your Name
