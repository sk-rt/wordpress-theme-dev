/**
 * WordPress Setup
 */


import { execSync } from 'node:child_process';
import dotenv from 'dotenv';
dotenv.config();

const wpOptions = {
  blogname: 'WP Dev',
  blogdescription: 'WordPress Theme Dev scaffold',
  timezone_string: 'Asia/Tokyo',
  date_format: 'Y-m-d',
  time_format: 'H:i',
  permalink_structure: '/blog/%post_id%/',
  thumbnail_size_w: '320',
  thumbnail_size_h: '320',
  medium_size_w: '800',
  medium_size_h: '800',
  large_size_w: '1280',
  large_size_h: '1280',
  medium_large_size_w: '0',
  show_avatars: '0',
  default_pingback_flag: '0',
  default_ping_status: 'closed',
  default_comment_status: 'closed',
}

const execWp = (command = '') => {
  try {
    const result = execSync(`docker exec ${process.env.PROJECT_NAME}-wordpress ${command}`);
    console.log(result.toString());
    return result;
  } catch (error) {
    console.log(`[ERROR]`, error.toString());
  }
};
const execWpCli = (command = '') => {
  try {
    return execWp(`env sudo --preserve-env -u www-data wp ${command}`);
  } catch (error) {
    console.log(`[ERROR]`, error.toString());
  }
};
{
  // install
  const result = execWpCli(`core install \
  --path="/var/www/html${process.env.WP_INSTALL_DIR}" \
  --url="http://localhost:${process.env.WP_PORT}${process.env.WP_INSTALL_DIR}/" \
  --title="${process.env.PROJECT_NAME}" \
  --admin_user=${process.env.WP_ADMIN_USER} \
  --admin_password=${process.env.WP_ADMIN_PASSWORD} \
  --admin_email=${process.env.WP_ADMIN_EMAIL}`)
  console.log(result);
}
{
  // install language
  const result = execWpCli(`language core install ${process.env.WP_LOCALE} --activate`)
  console.log(result);
}
{
  // activate theme 
  const result = execWpCli(`theme activate ${process.env.WP_THEME_NAME}`)
  console.log(result);
}
{
  // update options
  Object.keys(wpOptions).map((key) => {
    const result = execWpCli(`option update ${key} ${wpOptions[key]}`)
    console.log(result);
  });
}
