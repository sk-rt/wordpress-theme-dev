<?php

/**
 * Plugin Name: Setup Theme Plugin
 * Description: 初期設定
 * Version: 0.1
 * Author:
 * License: GPL2
 *
 * @package setup-theme
 */

namespace SetUpThemePlugin;

if (!defined('ABSPATH')) {
	exit;
}
function main()
{
	addRequiterdCategories();
	addRequiredPages();
	updateWpOptions();
}
register_activation_hook(__FILE__, 'SetUpThemePlugin\main');

add_action('admin_notices', function () {
	echo '<div class="notice notice-success is-dismissible">
	<p>テーマの初期設定が完了し、プラグインを無効化しました。</p>
	</div>';
});
add_action('admin_init', function () {
	deactivate_plugins(plugin_basename(__FILE__));
});

function updateWpOptions()
{
	\update_option('date_format', 'Y.m.d');
	\update_option('time_format', 'H:i');
	\update_option('permalink_structure', '/blog/%post_id%/');
	\update_option('thumbnail_size_w', 320);
	\update_option('thumbnail_size_h', 320);
	\update_option('medium_size_w', 720);
	\update_option('medium_size_h', 720);
	\update_option('medium_large_size_w', 960);
	\update_option('medium_large_size_h', 960);
	\update_option('large_size_w', 1280);
	\update_option('large_size_h', 1280);
	\update_option('show_avatars', 0);
	\update_option('default_pingback_flag', 0);
	\update_option('default_ping_status', 'closed');
	\update_option('default_comment_status', 'closed');
}
/**
 * update Categories
 */
function addRequiterdCategories()
{
	$default_category_id = \get_option('default_category');
	if ($default_category_id) {
		$category = \get_category($default_category_id);
		if ($category) {
			\wp_insert_category(
				[
					'cat_ID' => $category->term_id,
					'taxonomy' => 'category',
					'cat_name' => 'News',
					'category_nicename' => 'news',
				]
			);
		}
	}

	$categories = [
		[
			'cat_name' => 'Blog',
			'category_nicename' => 'blog',
			'category_description' => '',
		],
	];
	addTerms($categories, 'category');
}
/**
 * 必須固定ページの追加
 * page_on_front,page_for_postsのアップデート
 */
function addRequiredPages()
{
	$top = \get_page_by_path('top');
	if (!$top) {
		wp_insert_post(array(
			'post_name' => 'top',
			'post_title' => 'Top',
			'post_content' => '',
			'post_type' => 'page',
			'post_status' => 'publish',
		));
	}
	$news = \get_page_by_path('news');
	if (!$news) {
		wp_insert_post(array(
			'post_name' => 'news',
			'post_title' => 'News',
			'post_content' => '',
			'post_type' => 'page',
			'post_status' => 'publish',
		));
	}
	$about = \get_page_by_path('about');
	if (!$about) {
		wp_insert_post(array(
			'post_name' => 'about',
			'post_title' => 'About',
			'post_content' => '<p>Hello, WordPress</p>',
			'post_type' => 'page',
			'post_status' => 'publish',
		));
	}
	$top = \get_page_by_path('top');
	$news = \get_page_by_path('news');
	\update_option('show_on_front', 'page');
	\update_option('page_on_front', $top->ID);
	\update_option('page_for_posts', $news->ID);
}

function addTerms(array $term_array, string $taxonomy)
{
	if (!$taxonomy || !$term_array) {
		return;
	}
	foreach ($term_array as $term) {
		$is_term = \term_exists($term['category_nicename'], $taxonomy);
		if ($is_term === 0 || $is_term === null) {
			$term += array(
				'taxonomy' => $taxonomy,
			);
			\wp_insert_category($term, false);
		}
	}
}
