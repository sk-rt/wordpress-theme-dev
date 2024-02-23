import { defineConfig, loadEnv } from 'vite';
import liveReload from 'vite-plugin-live-reload';
import createSvgSpritePlugin from 'vite-plugin-svg-sprite';

import autoprefixer from 'autoprefixer';
import path from 'node:path';
import { rmSync } from 'node:fs';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const themePath = `themes/${env.WP_THEME_NAME}`;
  const base = mode === 'development' ? './' : path.join('/wp-content', themePath); 

  const outDir =
    mode === 'development' ? path.join('./wp-content', themePath) : path.join('./dist', themePath);
  if (process.env.NODE_ENV === 'production' && mode === 'development') {
    rmSync(path.join(outDir, 'js/'), { force: true, recursive: true });
    rmSync(path.join(outDir, 'css/'), { force: true, recursive: true });
  }

  return {
    plugins: [
      liveReload(`${__dirname}/wp-content/${themePath}/**/*.php`),
      createSvgSpritePlugin({
        symbolId: 'icon-[name]',
      }),
    ],
    root: '',
    base: base,
    publicDir: false,
    build: {
      outDir: outDir,
      emptyOutDir: false,
      manifest: true,
      rollupOptions: {
        input: {
          main: path.resolve(__dirname + '/src/js/main.ts'),
        },
        output: {
          entryFileNames: `js/[name]-[hash].js`,
          chunkFileNames: `js/[name]-[hash].js`,
          assetFileNames: ({ name }) => {
            if (/\.( gif|jpeg|jpg|png|svg|webp| )$/.test(name ?? '')) {
              return 'images/[name].[ext]';
            }
            if (/\.css$/.test(name ?? '')) {
              return 'css/[name]-[hash].[ext]';
            }
            return '[name].[ext]';
          },
        },
      },
      minify: true,
      write: true,
    },
    server: {
      host: 'localhost',
      cors: true,
      strictPort: true,
      port: parseInt(env.VITE_DEV_PORT),
      hmr: {
        host: 'localhost',
      },
      open: `http://localhost:${env.WP_PORT}`,
    },
    css: {
      devSourcemap: mode === 'development',
      postcss: {
        plugins: [autoprefixer],
      },
    },
  };
});
