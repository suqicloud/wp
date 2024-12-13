<?php
/*
Plugin Name: WP夜间深色模式
Description: 只是php文件，这个库本就是为了其他用途
Version: 1.0
Author: Summer
Author URI: https://www.jingxialai.com/
License: GPL v2 or later 
*/

if (!defined('ABSPATH')) {
    exit;
}

// 添加设置页面样式
add_action('admin_head', 'wp_nightdm_registration_styles');
function wp_nightdm_registration_styles() {
    if (isset($_GET['page']) && $_GET['page'] === 'nightdm') {
        ?>
        <style>
            body {
                color: #333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .nightdm_form {
                max-width: 95%;
                margin: 10px auto;
                background-color: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }

            .nightdm_form .nightdm_checkbox1{
                display: none!important;
            }
            .custom-updated {
                background-color: #009933;
                color: white;
                padding: 5px 10px;
                margin-top: 10px;
                border-radius: 5px;
                text-align: center;
            }

            .custom-error {
                background-color: #f44336;
                color: white;
                padding: 5px 10px;
                margin-top: 10px;
                border-radius: 5px;
                text-align: center;
            }
        </style>
        <?php
    }
}


function nightdm_activation_hook() {
    // 默认的深色模式位置和颜色
    $default_settings = array(
        'nightdm_body_bg_color' => '#152238',
        'nightdm_heading_text_color' => '#e5e0d8',
        'nightdm_para_text_color' => '#e5e0d8',
        'nightdm_link_text_color' => '#E3E3E3',
        'nightdm_link_text_hover_color' => '#dd9933',
        'nightdm_link_bg_color' => '',
        'nightdm_border_color' => '#E3E3E3',
        'nightdm_switch' => '#A52A2A',
        'nightdm_toggle_btn_position' => '右下角',
        'nightdm_responsive_device' => array([]),
        'nightdm_right_top_percentage' => '7',
        'nightdm_left_bottom_percentage' => '3'
    );
    
    foreach ($default_settings as $setting => $value) {
        if (!get_option($setting)) {
            update_option($setting, $value);
        }
    }
}
register_activation_hook(__FILE__, "nightdm_activation_hook");

define('nightdm_VERSION', time());

$nightdm_toggle_positions = array(
    __('右下角', 'nightdm'),
    __('右上方', 'nightdm'),
    __('左下角', 'nightdm'),
);
$nightdm_responsive_devices = array(
    __('Default', 'nightdm'),
    __('手机', 'nightdm'),
    __('平板电脑', 'nightdm'),
    __('桌面电脑', 'nightdm'),
);

class darkModeForWp
{
    private $version;

    function __construct()
    {

        $this->version = time();

        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'load_front_assets'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_assets'));
    }

    function load_admin_assets($hook_suffix)
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('nightdm-color-picker-js', plugins_url('nightdm-color-picker-script.js', __FILE__), array('wp-color-picker'), false, true);
    }

    function load_front_assets()
    {
        $plugin_dir_url = plugins_url('/', __FILE__);
        wp_enqueue_style('nightdm-main-css', $plugin_dir_url . "css/nightdmmain.css", null, $this->version);
        wp_enqueue_script('nightdm-main-js', $plugin_dir_url . "js/nightdmmain.js", null, $this->version, true);
    }

    function load_textdomain()
    {
        load_plugin_textdomain('nightdm', false, plugin_dir_url(__FILE__) . "/languages");
    }
}

new darkModeForWp();
//display output
function nightdm_display_output()
{

?>
<div class="nightdm-theme-btn">
    <div class="toggle">
        <input type="checkbox" id="nightdm-toggle-off" class="nightdm-checkbox">
        <label class="toggle-handle" for="nightdm-toggle-off"></label>
    </div>
</div>

    <?php
    $nightdm_body_bg_color = esc_attr(get_option('nightdm_body_bg_color'));
    $nightdm_heading_text_color = esc_attr(get_option('nightdm_heading_text_color'));
    $nightdm_para_text_color = esc_attr(get_option('nightdm_para_text_color'));
    $nightdm_link_text_color = esc_attr(get_option('nightdm_link_text_color'));
    $nightdm_link_text_hover_color = esc_attr(get_option('nightdm_link_text_hover_color'));
    $nightdm_link_bg_color = esc_attr(get_option('nightdm_link_bg_color'));
    $nightdm_border_color = esc_attr(get_option('nightdm_border_color'));
    $nightdm_switch = esc_attr(get_option('nightdm_switch'));
    $nightdm_toggle_btn_position = esc_attr(get_option("nightdm_toggle_btn_position"));
    $nightdm_devices = get_option("nightdm_responsive_device");
    $nightdm_right_top_percentage = esc_attr(get_option('nightdm_right_top_percentage'));
    $nightdm_left_bottom_percentage = esc_attr(get_option('nightdm_left_bottom_percentage'));

    ?>
    <style>
        .nightdm-dark-mode {
            --nightdm-primary: <?php echo $nightdm_body_bg_color === '' ? "#152238" : esc_attr($nightdm_body_bg_color); ?>;
            --nightdm-heading: <?php echo $nightdm_heading_text_color === '' ? '#e5e0d8
' : esc_attr($nightdm_heading_text_color); ?>;
            --nightdm-para: <?php echo $nightdm_para_text_color === '' ? '#e5e0d8
' : esc_attr($nightdm_para_text_color); ?>;
            --nightdm-link: <?php echo $nightdm_link_text_color === '' ? '#E3E3E3
' : esc_attr($nightdm_link_text_color); ?>;
            --nightdm-link-hover: <?php echo $nightdm_link_text_hover_color === '' ? '#dd9933
' : esc_attr($nightdm_link_text_hover_color); ?>;
            --nightdm-link-bg: <?php echo $nightdm_link_bg_color === '' ? '
' : esc_attr($nightdm_link_bg_color); ?>;
            --nightdm-border-color: <?php echo $nightdm_border_color === '' ? '#E3E3E3
' : esc_attr($nightdm_border_color); ?>;
        }

        .nightdm-theme-btn {
            --nightdm-switch: <?php echo $nightdm_switch === '' ? '#a52a2a
' : esc_attr($nightdm_switch); ?>;
            --top-right: <?php echo $nightdm_toggle_btn_position === "右上方" ? esc_attr($nightdm_right_top_percentage) . '%' : ''; ?>;
            --top-left: <?php echo $nightdm_toggle_btn_position === "左下角" ? esc_attr($nightdm_left_bottom_percentage) . '%' : ''; ?>;
            --mobile-device:
                <?php if (in_array("手机", $nightdm_devices)) {
                    echo __("none", 'nightdm');
                } else {
                    __("block", 'nightdm');
                } ?>;
            --tablet-device:
                <?php if (in_array("平板电脑", $nightdm_devices)) {
                    echo __("none", 'nightdm');
                } else {
                    __("block");
                } ?>;
            --desktop-device:
                <?php if (in_array("桌面电脑", $nightdm_devices)) {
                    echo __("none", 'nightdm');
                } else {
                    __("block", 'nightdm');
                } ?>
        }
    </style>
    <?php
}
add_action('wp_footer', 'nightdm_display_output');


//设置页面
class nightdm_Settings_Page
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'nightdm_create_settings'));
        add_action('admin_init', array($this, 'nightdm_setup_sections'));
        add_action('admin_init', array($this, 'nightdm_setup_fields'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'nightdm_settings_link'));
    }


    public function nightdm_settings_link($links)
    {
        $newlink = sprintf("<a href='%s'>%s</a>", 'options-general.php?page=nightdm', __('设置', 'nightdm'));
        $links[] = $newlink;
        return $links;
    }



    public function nightdm_create_settings()
    {
        $page_title = __('夜间深色模式', 'nightdm');
        $menu_title = __('夜间深色模式', 'nightdm');
        $capability = 'manage_options';
        $slug       = 'nightdm';
        $callback   = array($this, 'nightdm_settings_content');
        add_options_page($page_title, $menu_title, $capability, $slug, $callback);
    }

    public function nightdm_settings_content()
    { ?>
        <div class="wrap">
            <form class="nightdm_form" method="POST" action="options.php">
                <?php
                settings_fields('nightdm');
                do_settings_sections('nightdm');
                submit_button();
                ?>
            </form>
        </div> <?php
            }

            public function nightdm_setup_sections()
            {
                add_settings_section('nightdm_section', '夜间深色模式设置', array(), 'nightdm');
            }

            public function nightdm_setup_fields()
            {
                $fields = array(
                    array(
                        'label'       => __('深色模式颜色', 'nightdm'),
                        'id'          => 'nightdm_body_bg_color',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认颜色: #152238'),
                    ),
                    array(
                        'label'       => __('标题文本颜色', 'nightdm'),
                        'id'          => 'nightdm_heading_text_color',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认标题文本颜色: #e5e0d8'),
                    ),
                    array(
                        'label'       => __('段落文本颜色', 'nightdm'),
                        'id'          => 'nightdm_para_text_color',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认段落文本颜色: #e5e0d8'),
                    ),

                    array(
                        'label'       => __('链接文本颜色', 'nightdm'),
                        'id'          => 'nightdm_link_text_color',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认链接文本颜色: #E3E3E3'),
                    ),
                    array(
                        'label'       => __('链接文本悬停颜色', 'nightdm'),
                        'id'          => 'nightdm_link_text_hover_color',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认链接文本悬停颜色: #dd9933'),
                    ),

                    array(
                        'label'       => __('链接背景颜色', 'nightdm'),
                        'id'          => 'nightdm_link_bg_color',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认链接背景颜色: None'),
                    ),
                    array(
                        'label'       => __('边框颜色', 'nightdm'),
                        'id'          => 'nightdm_border_color',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认边框颜色: #E3E3E3'),
                    ),
                    array(
                        'label'       => __('切换按钮颜色', 'nightdm'),
                        'id'          => 'nightdm_switch',
                        'type'        => 'text',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认切换按钮颜色: #A52A2A'),
                    ),
                    array(
                        'label'       => __('切换按钮位置', 'nightdm'),
                        'id'          => 'nightdm_toggle_btn_position',
                        'type'        => 'select',
                        'section'     => 'nightdm_section',
                    ),
                    array(
                        'label'       => __('右上角位置百分比', 'nightdm'),
                        'id'          => 'nightdm_right_top_percentage',
                        'type'        => 'number',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认位置: 7%'),
                    ),
                    array(
                        'label'       => __('左下角位置百分比', 'nightdm'),
                        'id'          => 'nightdm_left_bottom_percentage',
                        'type'        => 'number',
                        'section'     => 'nightdm_section',
                        'desc'        => __('默认位置: 3%'),
                    ),

                    array(
                        'label'       => __('开启隐藏深色模式', 'nightdm'),
                        'id'          => 'nightdm_responsive_device',
                        'type'        => 'checkbox',
                        'section'     => 'nightdm_section',
                    ),

                );
                foreach ($fields as $field) {
                    add_settings_field($field['id'], $field['label'], array(
                        $this,
                        'nightdm_field_callback'
                    ), 'nightdm', $field['section'], $field);
                    register_setting('nightdm', $field['id']);
                }
            }
            public function nightdm_field_callback($field)
            {
                global $nightdm_toggle_positions;
                global $nightdm_responsive_devices;
                $option = get_option('nightdm_toggle_btn_position');
                $option_d = get_option('nightdm_responsive_device');
                $value = get_option($field['id']);
                switch ($field['type']) {
                    case 'select':
                        printf('<select id="%1$s" name="%2$s">', 'nightdm_toggle_btn_position', 'nightdm_toggle_btn_position');

                        foreach ($nightdm_toggle_positions as $nightdm_toggle_position) {
                            $selected = '';
                            if ($option == $nightdm_toggle_position) {
                                $selected = 'selected';
                            }
                            printf('<option value="%1$s" %2$s>%3$s</option>', $nightdm_toggle_position, $selected, $nightdm_toggle_position);
                        }
                        echo "</select>";



                        isset($field['placeholder']) ? $field['placeholder'] : '';
                        break;

                    case 'checkbox':
                        $nightdm_count = 0;
                        foreach ($nightdm_responsive_devices as $nightdm_responsive_device) {
                            $nightdm_count++;
                            $selected_d = '';

                            if (is_array($option_d) && in_array($nightdm_responsive_device, $option_d)) {
                                $selected_d = 'checked';
                            }
                            if ($nightdm_responsive_device === 'Default') {
                                $selected_d = 'checked';
                            }
                            printf('<input class="nightdm_checkbox' . $nightdm_count . '" type="checkbox" name="nightdm_responsive_device[]" value="%s" %s /> <span class="nightdm_checkbox' . $nightdm_count . '">%s</span> <br/>', $nightdm_responsive_device, $selected_d, $nightdm_responsive_device);
                        }
                        break;

                    case 'number':
                        printf(
                            '<input class="nightdm_setting_form_field" name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s"/>',
                            $field['id'],
                            $field['type'],
                            isset($field['placeholder']) ? $field['placeholder'] : '',
                            $value,
                        );
                        break;
                    default:
                        printf(
                            '<input class="my-color-field nightdm_setting_form_field" name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s"/>',
                            $field['id'],
                            $field['type'],
                            isset($field['placeholder']) ? $field['placeholder'] : '',
                            $value
                        );
                }
                if (isset($field['desc'])) {
                    if ($desc = $field['desc']) {
                        printf('<p class="description">%s </p>', $desc);
                    }
                }
            }
        }
        new nightdm_Settings_Page();

//设置页面结束
