# WordPress Docker
`mysql:5.7` + `wordpress` + `wp-cli`
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
## セットアップ（初回のみ）
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
```

## コンテナ起動
```sh
docker-compose up -d
```

## 停止
```sh
docker-compose stop
```


## MySQLのダンプ
```sh
sh bin/db-backup.sh
```
## MySQLのインポート
```sh
sh bin/db-import.sh
```
## wp-cli
＊`${PROJECT_NAME}`は.envの`PROJECT_NAME`
```sh
# wordpressコンテナに入る
docker exec -it ${PROJECT_NAME}-wordpress /bin/bash
# www-dataユーザーでwpコマンド実行
sudo -u www-data wp --info
```
## SQL
＊`${PROJECT_NAME}`は.envの`PROJECT_NAME`
```sh
# mysqlコンテナに入りrootでログイン
docker exec -it ${PROJECT_NAME}-mysql /usr/bin/mysql -u root -p
Enter Password: root
# mysqlコマンド実行
mysql> show databases;
```