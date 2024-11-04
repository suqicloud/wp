<?php
/*
Plugin Name: 关键词违禁过滤
Description: 一个简单的关键词过滤屏蔽插件，文章标题、内容出现违禁词就用星号自动替换。
Plugin URI: https://www.jingxialai.com/4677.html
Version: 1.0
Author: summer
License: GPL License
Author URI: https://www.jingxialai.com/
*/

if (!defined('ABSPATH')) {
    exit;
}

// 设置链接回调函数
function wpkeywords_settings_link($links) {
    $settings_link = '<a href="admin.php?page=keyword_blocker">设置</a>';
    array_unshift($links, $settings_link);
    return $links;
}
// 设置入口
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpkeywords_settings_link');

class KeywordBlocker {
    private $option_name = 'blocked_keywords';
    private $blocked_keywords = array();

    public function __construct() {
        $this->blocked_keywords = get_option($this->option_name, array());

        if (!is_array($this->blocked_keywords)) {
            $this->blocked_keywords = explode("\n", str_replace("\r", "", $this->blocked_keywords));
        }

        add_filter('the_content', array($this, 'filter_content')); //文章内容
        add_filter('the_title', array($this, 'filter_content')); //文章标题
        add_filter('comment_text', array($this, 'filter_content')); //评论
        add_filter('asgarosforum_filter_post_content', array($this, 'filter_content')); // asgaros论坛
        //你可以在这里自己添加过滤器钩子就行

        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'settings_init'));
        }
    }

    public function filter_content($content) {
        foreach ($this->blocked_keywords as $keyword) {
            $content = str_replace($keyword, '****', $content);
        }
        return $content;
    }

    public function add_admin_menu() {
        add_options_page('违禁词设置', '违禁词设置', 'manage_options', 'keyword_blocker', array($this, 'options_page'));
    }

    public function settings_init() {
        register_setting('pluginPage', $this->option_name);

        add_settings_section(
            'keyword_blocker_pluginPage_section',
            __('设置过滤违禁词', 'wordpress'),
            null,
            'pluginPage'
        );

        add_settings_field(
            'keywords',
            __('违禁词', 'wordpress'),
            array($this, 'keywords_render'),
            'pluginPage',
            'keyword_blocker_pluginPage_section'
        );
    }

    public function keywords_render() {
        echo '<textarea name="' . $this->option_name . '" rows="10" cols="50">' . implode("\n", $this->blocked_keywords) . '</textarea>';
    }

    public function options_page() {
        echo '<form action="options.php" method="post">';
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        echo '<p style="color: red;">支持标题、内容、评论的违禁词替换，asgaros论坛只支持内容。多个违禁词，就一行一个。</p>';
        echo '</form>';
    }
}

new KeywordBlocker();
?>
