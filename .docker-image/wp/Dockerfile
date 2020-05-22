# *****************************
# WordPress公式imageにwp-cliを追加
# *****************************
FROM wordpress:latest
# 必要なツール郡をインストール
RUN apt-get update
RUN apt-get -y install wget sudo
# wp-cliをインストール
WORKDIR /
RUN wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x /wp-cli.phar
RUN mv /wp-cli.phar /usr/local/bin/wp
RUN sudo chown -hR www-data /var/www 
WORKDIR /var/www/html