# WordPress Theme Dev

Docker + frontend develop

## Configuration

Edit `sample.env` and save as `.env`

```bash
PROJECT_NAME=my-wordpress # namespace for docker container
LOCAL_PROTOCOL=http # http or https
LOCAL_PORT=8080 # WordPress Port
MYSQL_PORT=3306 # MySQL Port
WP_LOCALE=ja # WordPress Locale *
WP_ADMIN_USER=admin # WordPress Admin user *
WP_ADMIN_PASSWORD=admin # *
WP_ADMIN_EMAIL=admin@example.com # *
WP_THEME_NAME=mytheme # Theme directory *
WP_INSTALL_DIR=/
WP_REQUIED_PLUGINS="classic-editor custom-post-type-permalinks wp-multibyte-patch" # Required plugin *
```

\* Use on [wp-init.sh](./bin/wp-init.sh)

## Setup

### Build docker images & Start

```
docker-compose up -d --build
```

### Install WordPress & requred plugins

```sh
sh bin/wp-init.sh
```

### Npm install

```sh
yarn install
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

### Frontend Development

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
