<?php
/**
Plugin Name: WPUpYun(又拍云云存储插件)
Plugin URI: https://www.lezaiyun.com/1099.html
Description: WordPress同步附件内容远程至又拍云云存储中，实现网站数据与静态资源分离，提高网站加载速度。公众号： 老蒋朋友圈
Version: 4.0
Author: 老蒋和他的小伙伴
Author URI: https://www.lezaiyun.com
Requires PHP: 7.4
*/

require_once 'actions.php';

$current_wp_version = get_bloginfo('version');

# 插件 activation 函数当一个插件在 WordPress 中”activated(启用)”时被触发。
register_activation_hook(__FILE__, 'wpupyun_set_options');
register_deactivation_hook(__FILE__, 'wpupyun_restore_options');  # 禁用时触发钩子
add_action('upgrader_process_complete', 'wpupyun_upgrade_options', 10, 2);

# 自动重命名
add_filter( 'sanitize_file_name', 'wpupyun_sanitize_file_name', 10, 1 );

# 避免上传插件/主题被同步到对象存储
if ( isset( $_SERVER['REQUEST_URI'] ) && substr_count( $_SERVER['REQUEST_URI'], '/update.php' ) <= 0 ) {
    add_filter('wp_handle_upload', 'wpupyun_upload_attachments');
    if ( (float)$current_wp_version < 5.3 ){
        add_filter( 'wp_update_attachment_metadata', 'wpupyun_upload_and_thumbs' );
    } else {
        add_filter( 'wp_generate_attachment_metadata', 'wpupyun_upload_and_thumbs' );
        add_filter( 'wp_save_image_editor_file', 'wpupyun_save_image_editor_file' );
    }
}

# 检测不重复的文件名
add_filter('wp_unique_filename', 'wpupyun_unique_filename');

# 删除文件时触发删除远端文件，该删除会默认删除缩略图
add_action('delete_attachment', 'wpupyun_delete_remote_attachment');

# 添加插件设置菜单
add_action('admin_menu', 'wpupyun_add_setting_page');
add_filter('plugin_action_links', 'wpupyun_plugin_action_links', 10, 2);

// add_filter( 'big_image_size_threshold', '__return_false' );


function wpupyun_save_image_editor_file($override){
    add_filter( 'wp_update_attachment_metadata', 'wpupyun_image_editor_file_save' );
    return $override;
}

function wpupyun_image_editor_file_save( $metadata ){
	$wpupyun_options = get_option('wpupyun_options');
	if ( ! is_array( $wpupyun_options ) ) {
		return $metadata;
	}
	$no_local = ! empty( $wpupyun_options['no_local_file'] );
	$wp_uploads = wp_upload_dir();

	if ( isset( $metadata['file'] ) ) {
		$attachment_key = '/' . $metadata['file'];
		$attachment_local_path = ( $wp_uploads['basedir'] ?? '' ) . $attachment_key;
		wpupyun_file_upload( $attachment_key, $attachment_local_path, $no_local );
	}
	if ( isset( $metadata['sizes'] ) && count( $metadata['sizes'] ) > 0 ) {
		foreach ( $metadata['sizes'] as $val ) {
			$attachment_thumbs_key = '/' . dirname( $metadata['file'] ) . '/' . $val['file'];
			$attachment_thumbs_local_path = ( $wp_uploads['basedir'] ?? '' ) . $attachment_thumbs_key;
			wpupyun_file_upload( $attachment_thumbs_key, $attachment_thumbs_local_path, $no_local );
		}
	}
    remove_filter( 'wp_update_attachment_metadata', 'wpupyun_image_editor_file_save' );
    return $metadata;
}
