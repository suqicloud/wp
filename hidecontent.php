<?php
/*
Plugin Name: 隐藏内容设置
Plugin URI: https://www.jingxialai.com/4695.html
Description: 隐藏之后需要输入后台设置的验证信息才能查看内容，只要不清除浏览器缓存，验证信息就一直有效.
Version: 1.5
Author: summer
Author URI: https://www.jingxialai.com/
*/

if (!defined('ABSPATH')) {
    exit;
}

// 屏蔽错误输出
//error_reporting(0);

// 编辑经典编辑器快捷键
add_action('admin_init', 'custom_content_locker_shortcode_button');
function custom_content_locker_shortcode_button() {
    if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
        add_filter('mce_external_plugins', 'custom_content_locker_add_shortcode_plugin');
        add_filter('mce_buttons', 'custom_content_locker_register_shortcode_button');
    }
}

// 注册快捷键按钮
function custom_content_locker_register_shortcode_button($buttons) {
    array_push($buttons, 'custom_content_locker_shortcode_button');
    return $buttons;
}

// 调用快捷键编辑框js
function custom_content_locker_add_shortcode_plugin($plugin_array) {
    $plugin_array['custom_content_locker_shortcode_button'] = plugins_url('/content-locker-shortcode-button.js', __FILE__);
    return $plugin_array;
}

// 插件快捷入口
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'custom_content_locker_settings_link');
function custom_content_locker_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=content_locker_settings">设置</a>';
    array_unshift($links, $settings_link);
    return $links;
}  

// 插件设置页面
add_action('admin_menu', 'custom_content_locker_menu');
function custom_content_locker_menu() {
    add_options_page('隐藏内容设置页面', '隐藏内容设置', 'manage_options', 'content_locker_settings', 'custom_content_locker_settings_page');
}

// 设置页面
function custom_content_locker_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('你走错路了，无权访问这个页面.');
    }
    ?>
    <div class="wrap">
        <h2>隐藏内容设置页面</h2>
        <form method="post" action="options.php">
            <?php settings_fields('content_locker_options'); ?>
            <?php do_settings_sections('content_locker_settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// 上传图片
function load_wp_media_files() {
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'load_wp_media_files');


// 插件注册设置
add_action('admin_init', 'custom_content_locker_settings');
function custom_content_locker_settings() {
    register_setting('content_locker_options', 'content_locker_answer', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_content_locker_answer',
        'validate_callback' => 'validate_content_locker_answer'
    )); // 验证信息

    register_setting('content_locker_options', 'content_locker_image'); // 图片

    register_setting('content_locker_options', 'content_locker_e_message', 'sanitize_text_field'); // 注册文字提示

    add_settings_section('content_locker_main', '设置说明', 'content_locker_section_text', 'content_locker_settings');
    add_settings_field('content_locker_answer', '验证答案', 'content_locker_answer_input', 'content_locker_settings', 'content_locker_main');
    add_settings_field('content_locker_image', '广告图片', 'content_locker_image_input', 'content_locker_settings', 'content_locker_main'); // 新增图片
    add_settings_field('content_locker_e_message', '文字提示', 'content_locker_e_message_input', 'content_locker_settings', 'content_locker_main'); // 新增图片
}

// 设置文字提醒
function content_locker_section_text() {
    echo '<p>设置统一验证答案和广告图片，只要不清除浏览器缓存验证信息就一直有效，并且排除了管理员。<br>为了安全只允许设置英文或者数字，不支持中文和符号。</p>';
}

// 设置验证信息答案
function content_locker_answer_input() {
    $answer = get_option('content_locker_answer');
    echo '<input type="text" id="content_locker_answer" name="content_locker_answer" placeholder="验证信息" value="' . esc_attr($answer) . '" pattern="[a-zA-Z0-9]+" title="只能输入英文和数字" />';
}

// 调用媒体库
function content_locker_image_input() {
    $image = get_option('content_locker_image');
    ?>
    <input type="text" id="content_locker_image" name="content_locker_image" placeholder="图片链接" value="<?php echo esc_attr($image); ?>" />
    <input type="button" id="upload_image_button" class="button" value="上传图片" />
    <script>
        jQuery(document).ready(function($){
            $('#upload_image_button').click(function(e) {
                e.preventDefault();
                if (wp.media) {
                    var custom_uploader = wp.media({
                        title: '媒体库',
                        button: {
                            text: '确定'
                        },
                        multiple: false
                    });
                    custom_uploader.on('select', function() {
                        var attachment = custom_uploader.state().get('selection').first().toJSON();
                        $('#content_locker_image').val(attachment.url);
                    });
                    custom_uploader.open();
                } else {
                    alert('请重新上传.');
                }
            });
        });
    </script>
    <?php
}

// 设置 e-message 文字提醒
function content_locker_e_message_input() {
    $e_message = get_option('content_locker_e_message', '请关注微信公众号回复： 某某获取验证码.');
    echo '<input type="text" id="content_locker_e_message" name="content_locker_e_message" placeholder="e-message Text" value="' . esc_attr($e_message) . '" style="width: 400px;" />'; //文字提示板块
}

// 只允许英文或者数字
function sanitize_content_locker_answer($input) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $input);
}

// 验证提醒
function validate_content_locker_answer($input) {
    if (!preg_match('/^[a-zA-Z0-9]+$/', $input)) {
        add_settings_error(
            'content_locker_answer',
            'invalid_content_locker_answer',
            '验证答案只能包含英文字母和数字。',
            'error'
        );
        return get_option('content_locker_answer'); // 恢复之前的内容
    }
    return $input;
}

// 前端输出内容
session_start(); // session会话
function custom_content_locker_shortcode($atts, $content = null) {
    // 获取后台图片
    $saved_answer = get_option('content_locker_answer');
    $image = get_option('content_locker_image');
    $e_message = get_option('content_locker_e_message', '请关注微信公众号回复： 某某获取验证码.'); // 获取后台设置的文字，这里是预设

    // 检查验证信息
    if (isset($_POST['content_locker_submit']) && !empty($_POST['content_locker_user_answer']) && $_POST['content_locker_user_answer'] === $saved_answer) {
        $_SESSION['content_locker_answer'] = $saved_answer; // 获取session
        return do_shortcode($content); // 显示
    } elseif (isset($_POST['content_locker_submit']) && !empty($_POST['content_locker_user_answer']) && $_POST['content_locker_user_answer'] !== $saved_answer) {
        $error_message = '<p class="error-message">验证码错误. 请重新输入.</p>';
    }

    // 检查session
    if (isset($_SESSION['content_locker_answer']) && $_SESSION['content_locker_answer'] === $saved_answer) {
        return do_shortcode($content); // 如果已经存储就显示
    } elseif (current_user_can('manage_options')) {
        return do_shortcode($content); // 如果是管理员直接显示
    } else {
        // 信息内容
        ob_start();
        ?>
        <div class="content-locker-form">
            <?php echo isset($error_message) ? $error_message : ''; ?>
            <form method="post" action="">
                <input type="text" name="content_locker_user_answer" placeholder="请输入验证码" pattern="[a-zA-Z0-9]+" title="只能输入英文和数字" />
                <input type="submit" name="content_locker_submit" value="提交" />
            </form>
            <p class="e-message"><?php echo esc_html($e_message); ?></p>
            <img src="<?php echo esc_url($image); ?>" alt="公众号二维码" class="content-locker-image" />
        </div>
        <style>
            /* Content Locker Styles */
            .content-locker-form {
                background-color: #065f8c;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 10px;
                max-width: 400px;
                margin: 0 auto;
            }

            .content-locker-form input[type="text"],
            .content-locker-form input[type="submit"] {
                display: inline-block;
                vertical-align: middle;
            }

            .content-locker-form input[type="text"] {
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                width: 60%;
                /*margin-bottom: 10px;*/
            }

            .content-locker-form input[type="submit"] {
                background-color: #4caf50;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin-left: 10px;
            }

            .content-locker-form input[type="submit"]:hover {
                background-color: #45a049;
            }

            .content-locker-image {
                max-width: 100%;
                display: block;
                margin-top: 10px;
            }

            .e-message {
                color: #FFF;
                font-weight: bold;
                margin-top: 1px;
            }

            .error-message {
                color: red;
            }
        </style>
        <?php
        return ob_get_clean();
    }
}
add_shortcode('content_locker', 'custom_content_locker_shortcode');
?>
