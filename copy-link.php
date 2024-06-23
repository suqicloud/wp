<?php
/*
Plugin Name: 自动复制链接
Description: 鼠标点击就自动复制对应的链接到粘贴板，支持HTTP、磁力magnet和电驴ed2k链接.
Plugin URI: https://www.jingxialai.com/4703.html
Version: 1.1
Author: Summer
Author URI: https://www.jingxialai.com/
License: GPL License
*/

// 注册插件
add_shortcode('link_copy', 'link_copy_shortcode');

// 添加按钮
add_action('admin_init', 'link_copy_add_editor_button');

// 复制执行过程
function link_copy_script() {
    // Output the JavaScript directly
    ?>
    <script>
    function copyToClipboard(element, url) {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = url;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);

        return true; // 执行复制
    }

    // 复制成功了就改变文字
    function updateButtonText(element, label) {
        element.innerHTML = '已成功复制';
        element.classList.add('copied');

        setTimeout(function() {
            element.innerHTML = label;
            element.classList.remove('copied');
        }, 2000);
    }

    // 链接复制按钮的点击处理程序
    function handleButtonClick(event, url, label) {
        event.preventDefault(); // 防止默认链接直接跳转了
        if (copyToClipboard(event.target, url)) {
            updateButtonText(event.target, label);
        }
    }
    </script>
    <?php
}
add_action('wp_footer', 'link_copy_script');

// 显示复制文本
function link_copy_shortcode($atts, $content = null) {
    $atts = shortcode_atts(
        array(
            'url' => '',
            'label' => '复制',
        ),
        $atts,
        'link_copy'
    );

    if (empty($atts['url'])) {
        return '';
    }

    $label = esc_attr($atts['label']);
    $url = $atts['url'];

    // 对HTTP、magnet、ed2k的支持
    if (strpos($url, 'http') === 0 || strpos($url, 'magnet:') === 0 || strpos($url, 'ed2k:') === 0) {
        ob_start();
        ?>
        <style>
        .link-copy-button {
            text-decoration: none !important;
            color: blue;
        }
        .link-copy-button.copied {
            color: green; /* 成功之后的颜色 */
        }
        </style>
        <div class="link-copy-container">
            <a href="<?php echo esc_url($url); ?>" class="link-copy-button" onclick="handleButtonClick(event, '<?php echo esc_js($url); ?>', '<?php echo esc_js($label); ?>');"><?php echo $label; ?></a>
        </div>
        <?php
        return ob_get_clean();
    } else {
        return ''; // 如果是不支持的URL格式，则返回空字符串，不显示
    }
}

// 经典编辑器快捷键
function link_copy_add_editor_button() {
    add_filter('mce_buttons', 'link_copy_register_editor_button');
    add_filter('mce_external_plugins', 'link_copy_add_tinymce_plugin');
}

function link_copy_register_editor_button($buttons) {
    array_push($buttons, 'link_copy');
    return $buttons;
}

function link_copy_add_tinymce_plugin($plugin_array) {
    $plugin_array['link_copy'] = plugin_dir_url(__FILE__) . 'link-copy-button.js';
    return $plugin_array;
}
