#!/bin/bash
# *********************************************************
# mysqlのインポート
# *********************************************************
cd `dirname $0`
# .envを変数として読み込み
set -o allexport
[[ -f ../.env ]] && source ../.env
set +o allexport
echo "インポートするファイル名を入力してください: \n/db-backup/***" 
read FILE
if [ -z ${FILE} ]; then
    echo "終了"
else 
    cat ../db-backup/${FILE} | docker exec -i ${PROJECT_NAME}-mysql \
        mysql --defaults-extra-file=/etc/mysql.conf wordpress
    echo "完了" 
fi
