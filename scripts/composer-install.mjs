/**
 * WordPress Setup
 */

import { execDockerWp } from './lib/exec.mjs';

import dotenv from 'dotenv';
dotenv.config();

export const installComposer = () => {
  const themeDir = `/var/www/html${process.env.WP_INSTALL_DIR}/wp-content/themes/${process.env.WP_THEME_NAME}`;
  execDockerWp(`composer install --working-dir=${themeDir}`);
  execDockerWp(`composer dump-autoload --working-dir=${themeDir}`);
};
installComposer();
