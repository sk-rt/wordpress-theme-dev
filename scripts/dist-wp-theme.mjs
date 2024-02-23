import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
import { rmSync } from 'node:fs';
import { cp } from 'node:fs/promises';
import { globSync } from 'glob';
import dotenv from 'dotenv';
dotenv.config();

const __dirname = dirname(fileURLToPath(import.meta.url));
const distDir = resolve(__dirname, '../', `dist`);
const publicDir = resolve(__dirname, '../', `public`);
const theme = process.env.WP_THEME_NAME;

const distFiles = globSync(`${publicDir}/themes/${theme}/**/*`, {
  nodir: true,
  ignore: [
    `${publicDir}/themes/${theme}/**/*.{log,map,gitkeep,gitignore}`,
    `${publicDir}/themes/${theme}/{css,js,images}/*`,
  ],
});

try {
  rmSync(distDir, { force: true, recursive: true });
  await Promise.all(
    distFiles.map((file) => {
      return cp(file, file.replace(publicDir, distDir));
    }),
  );
} catch (error) {
  // eslint-disable-next-line no-console
  console.error(error);
}
