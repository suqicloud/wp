<?php
/*
Plugin Name: 自动下载外链图片并替换
Version: 1.2
*/

// 添加发布文章的钩子
add_action('save_post', 'auto_download_replace_images');

// 调度异步处理图片下载和替换任务
function schedule_auto_download_replace_images($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
        return;

    // 检查文章是否有外链图片
    $post_content = get_post_field('post_content', $post_id);
    preg_match_all('/<img.*?src=["\'](https?:\/\/.*?)["\'].*?>/i', $post_content, $matches);
    if (empty($matches[1]))
        return;

    // 安排异步任务
    wp_schedule_single_event(time(), 'auto_download_replace_images_event', array($post_id));
}

// 异步处理图片下载和替换任务
add_action('auto_download_replace_images_event', 'auto_download_replace_images_async');

function auto_download_replace_images_async($post_id) {
    // 获取文章内容
    $post_content = get_post_field('post_content', $post_id);

    // 使用正则表达式匹配文章内容中的外链图片
    preg_match_all('/<img.*?src=["\'](https?:\/\/.*?)["\'].*?>/i', $post_content, $matches);

    // 遍历匹配的图片链接
    foreach ($matches[1] as $image_url) {
        // 验证图片链接的来源并且下载到本地
        if (wp_http_validate_url($image_url)) {
            $upload = auto_download_image($image_url);

            // 如果下载成功，则替换文章内容中的图片链接
            if ($upload) {
                $post_content = str_replace($image_url, $upload['url'], $post_content);
            }
        }
    }

    // 更新文章内容
    wp_update_post(array(
        'ID'           => $post_id,
        'post_content' => $post_content,
    ));
}

// 负责图片下载的具体执行
function auto_download_image($image_url) {
    // 检查是否已经下载过这张图片，如果下载过则直接返回
    $attachment_id = attachment_url_to_postid($image_url);
    if ($attachment_id) {
        $attachment_url = wp_get_attachment_url($attachment_id);
        return array(
            'id'  => $attachment_id,
            'url' => $attachment_url,
        );
    }

    // 生成本地文件名
    $file_name = basename($image_url);

    // 使用WordPress HTTP API下载到本地
    $response = wp_safe_remote_get($image_url);
    if (!is_wp_error($response) && $response['response']['code'] === 200) {
        // 生成唯一的文件名
        $new_file_name = wp_unique_filename(UPLOADS_DIR, $file_name);

        // 保存文件到本地上传目录
        $upload = wp_upload_bits($new_file_name, null, $response['body']);

        // 如果上传成功，则将图片添加到媒体库
        if (!$upload['error']) {
            $file_path = $upload['file'];
            $attachment = array(
                'post_mime_type' => wp_check_filetype($file_name)['type'],
                'post_title'     => sanitize_file_name($file_name),
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            // 添加到媒体库
            $attachment_id = wp_insert_attachment($attachment, $file_path);

            // 更新媒体库
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // 返回新的图片链接信息
            $attachment_url = wp_get_attachment_url($attachment_id);
            return array(
                'id'  => $attachment_id,
                'url' => $attachment_url,
            );
        }
    }

    return false;
}
?>
