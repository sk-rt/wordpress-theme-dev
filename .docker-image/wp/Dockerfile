# *****************************
# WordPress公式imageにwp-cliを追加
# *****************************
FROM wordpress:php8.3-apache
# 必要なツール郡をインストール
RUN apt-get update
RUN apt-get install -y \
     wget \
     sudo \
     vim \
     git \
     zip \
     unzip
# wp-cliをインストール
WORKDIR /
RUN wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x /wp-cli.phar
RUN mv /wp-cli.phar /usr/local/bin/wp
RUN sudo chown -hR www-data /var/www 
# composerをインストール
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html