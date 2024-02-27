# WordPress Theme Dev

ローカルのWordPress実行環境にDocker/Docker Compose、  
フロントエンドのビルド環境にnode/npmを使用します。

## 必須要件
- Docker Compose
- node.js ^20

## セットアップ

### 1. `.env`の作成.
`.env.example` を `.env` として別名保存してください。

### 2. ローカルサーバー・WP環境の構築
```sh
docker compose build
```

### 3. node moduleのインストール
```sh
npm install
```

### 4. WordPressのインストール、初期設定  

.envの設定を元にWordPressのインストールと、言語のダウンロード、テーマの設定、オプションの更新を行います。
    
```sh
npm run setup:wp-theme
```


---

## ローカルサーバー

### 開発用サーバー（ホットリロード）

CSS/JSの修正にはこちらを使用してください。  
http://localhost:8282 に開発サーバー、別途Viteのホットリロードサーバーがが立ち上がります。

```sh
npm run dev 
```

### 開発用プレビュー

フロント(CSS/JS)のビルドと、プレビュー用Dockerが立ち上がります。  
CSS/JSはビルド済のものを参照します。  
URLは上記と同じ http://localhost:8282 になります。

```sh
npm run preview
```


### Dockerコンテナのストップ

```sh
docker compose stop
```


## 本番ビルド

./dist/themes/ に、テーマが書き出されます。  


```sh
npm run dist
```
