#!/bin/bash
# **************************************
# Wordpressのセットアップ
# **************************************
cd `dirname $0`
# .envを変数として読み込み
set -o allexport
[[ -f ../.env ]] && source ../.env
set +o allexport

# WP インストール
echo "-------------------------------------- \n以下でインストールしますか？" 
echo " > SiteUrl: $LOCAL_PROTOCOL://localhost:$LOCAL_PORT\n > User: $WP_ADMIN_USER\n > Password: $WP_ADMIN_PASSWORD"
read -p "[y/n]: " INS_CORE
if [ "$INS_CORE" = 'yes' ] || [ "$INS_CORE" = 'YES' ] || [ "$INS_CORE" = 'y' ] || [ "$INS_CORE" = 'Y' ] ; then
    if ! $(docker exec -it $PROJECT_NAME-wordpress env sudo -u www-data wp core is-installed); then
        docker exec -it $PROJECT_NAME-wordpress env \
            sudo -u www-data wp core install \
                --path="/var/www/html$WP_INSTALL_DIR" \
                --url="$LOCAL_PROTOCOL://localhost:$LOCAL_PORT$WP_INSTALL_DIR/" \
                --title="$PROJECT_NAME" \
                --admin_user=$WP_ADMIN_USER \
                --admin_password=$WP_ADMIN_PASSWORD \
                --admin_email=$WP_ADMIN_EMAIL
         echo "$LOCAL_PROTOCOL://localhost:$LOCAL_PORT$WP_INSTALL_DIR/\nインストール完了"
    else 
        echo "既にインストール済みです\n"
    fi
else
  echo "[x] キャンセルしました\n"
fi

# WP 言語のインストール
echo "-------------------------------------- \n言語ファイルをインストールしますか？" 
echo " > Lang: $WP_LOCALE"
read -p "[y/n]: " INS_LANG
if [ "$INS_LANG" = 'yes' ] || [ "$INS_LANG" = 'YES' ] || [ "$INS_LANG" = 'y' ] || [ "$INS_LANG" = 'Y' ] ; then
    docker exec -it $PROJECT_NAME-wordpress env \
        sudo -u www-data wp language core install $WP_LOCALE
    docker exec -it $PROJECT_NAME-wordpress env \
        sudo -u www-data wp language core activate $WP_LOCALE
    echo "言語ファイルをインストールしました\n"
else
  echo "[x] キャンセルしました\n"
fi

# 必須プラグインインストール & アクティブ
echo "-------------------------------------- \n必須プラグインをインストールしますか？" 
echo " > Plugins:$WP_REQUIED_PLUGINS"
read -p "[y/n]: " INS_PLUGINS
if [ "$INS_PLUGINS" = 'yes' ] || [ "$INS_PLUGINS" = 'YES' ] || [ "$INS_PLUGINS" = 'y' ] || [ "$INS_PLUGINS" = 'Y' ] ; then
    docker exec -it $PROJECT_NAME-wordpress env \
        sudo -u www-data wp plugin install $WP_REQUIED_PLUGINS --activate
    echo "必須プラグインをインストールしました\n"
else
  echo "[x] キャンセルしました\n"
fi


# テーマ アクティブ
echo "-------------------------------------- \nテーマをアクティブにしますか？" 
echo "> Theme:$WP_THEME_NAME"
read -p "[y/n]: " INS_THEME
if [ $INS_THEME = 'yes' ] || [ $INS_THEME = 'YES' ] || [ $INS_THEME = 'y' ] ||  [ $INS_THEME = 'Y' ] ; then
    docker exec -it $PROJECT_NAME-wordpress env \
        sudo -u www-data wp theme activate $WP_THEME_NAME
    echo "テーマ'$WP_THEME_NAME'をアクティブにしました"
else
  echo "[x] キャンセルしました\n"
fi

# WP OPTION UPDATE
declare -a WP_OPTIONS=(
    "timezone_string=Asia/Tokyo" 
    "date_format=Y-m-d" 
    "time_format=H:i"
    "permalink_structure=/blog/%post_id%"
    "thumbnail_size_w=300"
    "thumbnail_size_h=300"
    "medium_size_w=640"
    "medium_size_h=640"
    "large_size_w=1280"
    "large_size_h=1280"
    "medium_large_size_w=0"
    "show_avatars=0"
    "default_pingback_flag=0"
    "default_ping_status=closed"
    "default_comment_status=closed"
)
# # WP OPTION UPDATE
echo "-------------------------------------- \nWP OPTION をアップデートしますか？"
i=0
for e in ${WP_OPTIONS[@]}; do
    echo " > ${e}"
    let i++
done
read -p "[y/n]: " UPDATE_OPTIONS
if [ "$UPDATE_OPTIONS" = 'yes' ] || [ "$UPDATE_OPTIONS" = 'YES' ] || [ "$UPDATE_OPTIONS" = 'y' ] ||  [ "$UPDATE_OPTIONS" = 'Y' ] ; then
    i=0
    for OPTION in ${WP_OPTIONS[@]}; do
    set -- `echo $OPTION | tr '=' ' '`
        echo $OPTION
        docker exec -it $PROJECT_NAME-wordpress \
            sudo -u www-data wp option update $1 "$2"
    done
else 
    echo "[x] キャンセルしました\n"
fi
