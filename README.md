# WordPress Theme Dev

Docker + frontend develop

## 環境設定

`.env`ファイルを編集

```bash
PROJECT_NAME=my-wordpress # コンテナ ネームスペース
LOCAL_PROTOCOL=http # http or https
LOCAL_PORT=8080 # WordPressの動作するポート
MYSQL_PORT=3306 # mySqlのポート
WP_LOCALE=ja # 言語設定
WP_ADMIN_USER=admin # WordPress管理者アカウント
WP_ADMIN_PASSWORD=admin
WP_ADMIN_EMAIL=admin@example.com
WP_THEME_NAME=mytheme # テーマディレクトリ名
WP_INSTALL_DIR=/ # インストールディレクトリ
WP_REQUIED_PLUGINS="classic-editor custom-post-type-permalinks wp-multibyte-patch" # 必須プラグイン(スペース区切り)
```

## Setup（初回のみ）

### Build docker images & Start

```
docker-compose up -d --build
```

### Install WordPress & requred plugin

```sh
sh bin/wp-init.sh
```

### Npm install

```sh
yarn
# or
npm i
```

## Develop

### Start Container

```sh
docker-compose up -d
```

### Stop Container

```sh
docker-compose stop
```

### Frontend Dev

```sh
yarn dev
```

### Frontend Production build

```sh
yarn dist
```

## Utils

### Backup databese

```sh
sh bin/db-backup.sh
```

### Import databese

```sh
sh bin/db-import.sh
```

### WP-CLI

```sh
docker exec -it ${PROJECT_NAME}-wordpress /bin/bash
sudo -u www-data wp --info
```

### SQL

```sh
docker exec -it ${PROJECT_NAME}-mysql /usr/bin/mysql -u root -p
Enter Password: root
mysql> show databases;
```
