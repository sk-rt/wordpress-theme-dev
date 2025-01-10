const ALLOWED_BLOCKS = [
  'core/paragraph',
  'core/image',
  'core/heading',
  'core/list',
  'core/list-item',
  'core/embed',
  'core/separator',
  'core/button',
  'core/buttons',
];
const ALLOWED_EMBEDS = ['youtube'];
const ALLOWED_FORMATS = ['core/bold', 'core/link', 'core/unknown', 'core/strikethrough'];
if (window.wp) {
  hideBlockTypes();
  removePanels();
  removeFormatTypes();
}
/**
 * 不要なブロック/ブロックスタイルを削除する
 */
function hideBlockTypes() {
  // NOTE: DOMContentLoadedでは動作しない
  window.addEventListener('load', () => {
    // 非表示だと画像ブロックから変換できてしまうので、カバー画像は完全削除
    window.wp.blocks.unregisterBlockType('core/cover');
  });

  window.wp.hooks.addFilter(
    'blocks.registerBlockType',
    'EditorSetting/hide-block-types',
    (settings, name) => {
      if (!name.startsWith('core/')) {
        return settings;
      }

      if (name === 'core/embed') {
        settings.variations = settings.variations.filter((variation) => {
          return ALLOWED_EMBEDS.includes(variation.name);
        });
      }
      if (ALLOWED_BLOCKS.includes(name)) {
        if (settings.styles) {
          // ブロックスタイルは全て削除
          settings.styles = [];
        }

        return settings;
      }
      if (!settings.supports) {
        return settings;
      }
      // allowedBlocks以外は全て非表示に
      settings.supports['inserter'] = false;
      return settings;
    },
  );
}
/**
 * 不要なフォーマットタイプを削除する
 */
function removeFormatTypes() {
  window.wp.domReady(() => {
    const allFormats = window.wp.data.select('core/rich-text').getFormatTypes();
    if (allFormats.length === 0) {
      return;
    }

    const unregistarFormats = allFormats.filter((format) => {
      return !ALLOWED_FORMATS.includes(format.name);
    });
    unregistarFormats.map((format) => {
      window.wp.richText.unregisterFormatType(format.name);
    });
  });
}

/**
 * パネルの削除
 */
function removePanels() {
  window.wp.domReady(() => {
    window.wp.data.dispatch('core/edit-post').removeEditorPanel('featured-image'); // Featured Image
  });
}
