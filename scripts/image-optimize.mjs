/**
 * 画像一括変換スクリプト
 * 参考： https://qiita.com/bananacoffee/items/d7a4b5cb4afff7efd162
 */
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
import sharp from 'sharp';
import { globSync } from 'node:fs';
import path from 'path';
import { mkdir, cp, writeFile, readFile } from 'node:fs/promises';
import { optimize, loadConfig } from 'svgo';
import dotenv from 'dotenv';
dotenv.config();

// 変換対象拡張子とエンコーダーの設定
const GET_ENCODER_FROM_EXTENSION = {
  jpg: 'jpg',
  jpeg: 'jpg',
  png: 'png',
};
const theme = process.env.WP_THEME_NAME;

// 変換オプション（参考： https://sharp.pixelplumbing.com/api-output）
const ENCODER_OPTIONS = {
  png: {
    compressionLevel: 9,
    adaptiveFiltering: true,
    progressive: true,
  },
  jpg: {
    quality: 80,
  },
  webp: {
    png: {
      lossless: true,
    },
    jpg: {
      quality: 90,
    },
  },
};

const __dirname = dirname(fileURLToPath(import.meta.url));
const IMAGE_DIR = resolve(__dirname, '../', `wp-content/themes/${theme}/images`);
const OUTPUT_DIR = resolve(__dirname, '../', `dist/themes/${theme}/images`);
const svgoConfig = await loadConfig(); // svgo.config.jsから設定を取得

// ソースディレクトリからファイル一覧を取得
// let imageFileList = [];
const imageFileList = globSync(IMAGE_DIR + '/**/*.*');

// 変数初期化
const ts_start = Date.now();
let ts_worker_start = Date.now();
let ts_worker_end;
let targetFileNum = imageFileList.length;
let encodedFileNum = 1;

await Promise.all(
  imageFileList.map(async (_imagePath) => {
    const imagePath = _imagePath.replace(IMAGE_DIR, '');
    const fileExtension = path.extname(imagePath).substring(1).toLowerCase();
    // ソースパスと出力パスを取得
    const sourcePath = path.join(IMAGE_DIR, imagePath);
    const destinationPath = path.join(OUTPUT_DIR, imagePath);
    await mkdir(path.dirname(destinationPath), { recursive: true });

    // 拡張子からエンコーダーを取得
    const encoder = GET_ENCODER_FROM_EXTENSION[fileExtension];
    // SVGかどうか
    const isSvg = fileExtension === 'svg';

    // 変数の初期化
    let action = '';
    let isCopy = !encoder && !isSvg;
    let encodeOptions = {};
    let binaryData = '';

    if (encoder !== '') {
      // エンコーダーの設定
      encodeOptions[encoder] = ENCODER_OPTIONS[encoder];
      if (Object.keys(encodeOptions).length === 0) {
        isCopy = true;
      }
    }

    if (isCopy) {
      // エンコード対象外
      await cp(sourcePath, destinationPath);
      //   await fse.copy(sourcePath, destinationPath);
      action = 'copied';
    } else if (isSvg) {
      // SVGの処理
      binaryData = await readFile(sourcePath);
      binaryData = optimize(binaryData, svgoConfig);
      binaryData = binaryData.data;
      await writeFile(destinationPath, binaryData);
      action += 'optimized';
    } else {
      // 最適化を行う
      // encoder と encodeOptions を指定して最適化
      await sharp(sourcePath).toFormat(encoder, ENCODER_OPTIONS[encoder]).toFile(destinationPath);
      action += 'optimized';
    }

    // 変換結果表示
    ts_worker_end = Date.now();
    console.info(
      '[',
      encodedFileNum++,
      '/',
      targetFileNum,
      ']',
      imagePath,
      'is',
      action,
      '(',
      ts_worker_end - ts_worker_start,
      'ms',
      ')',
    );
    ts_worker_start = ts_worker_end;
  }),
);

// 結果表示
console.info('done!', '(', 'total:', ts_worker_end - ts_start, 'ms', ')');
