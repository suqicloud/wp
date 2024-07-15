<?php
/*
Plugin Name: 游客权限设置
Description: 全站、菜单、分类、分类文章链接是否仅对登录用户可见。
Plugin URI: https://www.jingxialai.com/4529.html
Version: 1.2
Author: summer
Author URI: https://www.jingxialai.com/
License: GPL License
*/
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_head', 'wp_rvp_custom_styles');
function wp_rvp_custom_styles() {
    if (isset($_GET['page']) && $_GET['page'] === 'wp_rvp_menu_permission_settings') {
        ?>
        <style>
            body {
                color: #333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .wp_rvp_wrap {
                 max-width: 95%;
                margin: 20px auto;
                background-color: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .wp_rvp_table {
                width: 50%;
                border-collapse: collapse;
                border-spacing: 0;
            }
            .wp_rvp_table th,
            .wp_rvp_table td {
                padding: 8px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .wp_rvp_table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .wp-rvp-redirect-url-input {
                width: 350px;
            }
        </style>
        <?php
    }
}

// 添加菜单权限设置到后台菜单
add_action('admin_menu', 'wp_rvp_menu_permission_settings');
function wp_rvp_menu_permission_settings() {
    add_menu_page('游客权限设置', '游客权限设置', 'manage_options', 'wp_rvp_menu_permission_settings', 'wp_rvp_menu_permission_settings_page');
}

// 设置链接回调函数
function wp_rvp_mp_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wp_rvp_menu_permission_settings">设置</a>';
    array_unshift($links, $settings_link);
    return $links;
}
// 设置入口
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_rvp_mp_settings_link');


// 添加选项到菜单编辑页面
add_action('wp_nav_menu_item_custom_fields', 'wp_rvp_custom_menu_item_fields', 10, 4);
function wp_rvp_custom_menu_item_fields($id, $item, $depth, $args) {
    // 获取当前菜单项的选项值
    $is_logged_in = get_post_meta($id, '_menu_item_logged_in', true);
    ?>
    <p class="wp_rvp_field-visibility description description-wide">
        <label for="edit-menu-item-logged-in-<?php echo $id; ?>">
            <input type="checkbox" id="edit-menu-item-logged-in-<?php echo $id; ?>" name="menu-item-logged-in[<?php echo $id; ?>]" value="1" <?php checked($is_logged_in, '1'); ?> />
            仅对登录用户可见
        </label>
    </p>
    <?php
}

// 保存菜单项的选项值
add_action('wp_update_nav_menu_item', 'wp_rvp_save_custom_menu_item_fields', 10, 3);
function wp_rvp_save_custom_menu_item_fields($menu_id, $menu_item_db_id, $args) {
    // 检查是否设置了权限
    if (isset($_POST['menu-item-logged-in'][$menu_item_db_id])) {
        update_post_meta($menu_item_db_id, '_menu_item_logged_in', '1');
    } else {
        delete_post_meta($menu_item_db_id, '_menu_item_logged_in');
    }
}

// 根据菜单项的选项值决定是否对游客隐藏菜单项
add_filter('wp_nav_menu_objects', 'wp_rvp_filter_menu_for_logged_in_users', 10, 2);
function wp_rvp_filter_menu_for_logged_in_users($items, $args) {
    if (is_user_logged_in()) {
        return $items; // 如果用户已登录，则显示所有菜单项
    }
    
    // 如果用户未登录，则检查每个菜单项的权限设置
    foreach ($items as $key => $item) {
        $is_logged_in = get_post_meta($item->ID, '_menu_item_logged_in', true);
        if ($is_logged_in == '1') {
            unset($items[$key]); // 如果菜单项设置为仅对登录用户可见，则从菜单中移除
        }
    }
    
    return $items;
}


// 设置页面内容
function wp_rvp_menu_permission_settings_page() {
    // 检查用户权限
    if (!current_user_can('manage_options')) {
        wp_die('你没有权限访问这个页面！');
    }

    // 获取已保存的设置
    $menu_permission_category = get_option('menu_permission_category');
    $menu_permission_redirect_url = get_option('menu_permission_redirect_url');
    $enable_redirect = get_option('enable_redirect');
    $enable_text_hint = get_option('enable_text_hint');

    // 获取全站登录权限设置
    $site_permission_enable = get_option('site_permission_enable');

    // 获取所有分类
    $categories = get_categories(array(
        'hide_empty' => 0, // 显示所有分类，包括没有文章的分类
    ));

    // 处理表单提交
    if (isset($_POST['submit'])) {
        // 保存设置
        update_option('menu_permission_category', $_POST['menu_permission_category']);
        update_option('menu_permission_redirect_url', $_POST['menu_permission_redirect_url']);
        update_option('enable_redirect', isset($_POST['enable_redirect']) ? 1 : 0);
        update_option('enable_text_hint', isset($_POST['enable_text_hint']) ? 1 : 0);
        
        // 保存全站登录权限设置
        update_option('site_permission_enable', isset($_POST['site_permission_enable']) ? 1 : 0);
        
        
        echo '<div class="updated"><p>设置已保存</p></div>';
        // 重新获取设置以便页面显示
        $menu_permission_category = $_POST['menu_permission_category'];
        $menu_permission_redirect_url = $_POST['menu_permission_redirect_url'];
        $enable_redirect = isset($_POST['enable_redirect']) ? 1 : 0;
        $enable_text_hint = isset($_POST['enable_text_hint']) ? 1 : 0;

        // 保存全站登录权限设置
        $site_permission_enable = isset($_POST['site_permission_enable']) ? 1 : 0;
        
    }

    ?>

    <div class="wp_rvp_wrap">
        <h2>权限设置</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
    <th scope="row">全站仅登录可见</th>
    <td><input type="checkbox" name="site_permission_enable" <?php checked($site_permission_enable, 1); ?> /></td>
</tr>           
                <tr valign="top">
                    <th scope="row">游客访问跳转链接</th>
                    <td><input type="text" name="menu_permission_redirect_url" class="wp-rvp-redirect-url-input" value="<?php echo esc_attr($menu_permission_redirect_url); ?>" /></td>
                </tr>

                 <tr valign="top">
                    <th scope="row">禁止游客访问的文章分类ID</th>
                    <td><input type="text" name="menu_permission_category" value="<?php echo esc_attr($menu_permission_category); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">分类文章一起跳转</th>
                    <td><input type="checkbox" name="enable_redirect" <?php checked($enable_redirect, 1); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">分类文章文本提示</th>
                    <td><input type="checkbox" name="enable_text_hint" <?php checked($enable_text_hint, 1); ?> /></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="submit" class="button-primary" value="保存设置" /></p>
        </form>
        <p>说明：<br>1、多个分类用英文逗号(半角符号)隔开.<br>
        2、分类文章链接跳转：游客无法访问分类链接以及这个分类下的所有文章链接.<br>
    3、分类文章文本提示：游客无法访问分类链接，但是可以访问分类下的文章，只能看见标题，内容提示先登录，不影响SEO.<br>
    <font color="red">4、跳转链接设置：跳转网站内部链接，只填写域名后面的就行，比如： /contact.html</font><br>
	<font color="blue">5、全站仅登录可见：跳转网站内部链接，只支持跳转登录页面，比如： /wp-login.php</font><br>
	6、跳转外部链接，就写完整，比如： https://www.baidu.com<br>
    7、菜单权限设置就在菜单管理处.<br>
    8、如果你的主题调用、改动之类的比较多，可能就会不兼容.
    </p>
        <h2>所有分类及其 ID</h2>
        <table class="wp_rvp_table">
            <thead>
                <tr>
                    <th>分类名称</th>
                    <th>分类 ID</th>
                    <th>是否受限</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category) {
                    $is_limited = wp_rvp_is_category_limited($category->term_id, $menu_permission_category);
                    ?>
                    <tr>
                        <td><?php echo $category->name; ?></td>
                        <td><?php echo $category->term_id; ?></td>
                        <td><?php echo $is_limited ? '是' : ''; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        // js检查复选框
        document.addEventListener('DOMContentLoaded', function() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        checkboxes.forEach(function(otherCheckbox) {
                            if (otherCheckbox !== checkbox) {
                                otherCheckbox.checked = false;
                            }
                        });
                    }
                });
            });
        });
    </script>
    <?php
}

// 根据分类ID检查分类是否受限
function wp_rvp_is_category_limited($category_id, $limited_categories) {
    $limited_categories_array = explode(',', $limited_categories);
    return in_array($category_id, $limited_categories_array);
}

// 根据设置的文章分类权限和跳转链接，进行权限判断和跳转
add_action('template_redirect', 'wp_rvp_redirect_for_menu_permissions');
function wp_rvp_redirect_for_menu_permissions() {



    // 检查是否为分类或单篇文章页面
    if (is_category() || is_single()) {
        $menu_permission_category = get_option('menu_permission_category');
        $menu_permission_redirect_url = get_option('menu_permission_redirect_url');
        $enable_redirect = get_option('enable_redirect');
        $enable_text_hint = get_option('enable_text_hint');
        // 如果设置不完整，则不进行权限判断和跳转
        if (!$menu_permission_category || !$menu_permission_redirect_url) {
            return;
        }

        // 获取当前文章的分类列表
        $current_categories = get_the_category();
        // 获取当前文章所在的所有分类的 ID
        $current_category_ids = array();
        foreach ($current_categories as $category) {
            $current_category_ids[] = $category->term_id;
            // 获取当前分类及其子分类的所有 ID
            $child_categories = get_categories(array('parent' => $category->term_id, 'hide_empty' => false));
            foreach ($child_categories as $child_category) {
                $current_category_ids[] = $child_category->term_id;
            }
        }

        // 获取设置的受限分类列表
        $categories = explode(',', $menu_permission_category);

        // 检查当前文章所在的分类是否在受限制的分类列表中
        if (array_intersect($current_category_ids, $categories)) {
            // 如果启用了分类文章链接跳转且当前用户未登录，则重定向到指定链接
            if ($enable_redirect && !is_user_logged_in()) {
                wp_redirect($menu_permission_redirect_url);
                exit;
            }

            // 如果启用了分类文章链接文本提示，则仅显示文章标题
            if ($enable_text_hint && !is_user_logged_in() && is_single()) {
                add_filter('the_content', 'wp_rvp_show_login_hint');
            }
            if (!is_user_logged_in() && is_category()) {
                $menu_permission_redirect_url = get_option('menu_permission_redirect_url');
                wp_redirect($menu_permission_redirect_url);
                exit;
            }
        }
    }
}

// 添加“请先登录”的提示文本到文章内容
function wp_rvp_show_login_hint($content) {
    return '<p>请先登录</p>';

}

// 根据设置的全站登录权限进行权限判断和跳转
add_action('template_redirect', 'wp_rvp_redirect_for_site_permissions');
function wp_rvp_redirect_for_site_permissions() {

    // 获取全站登录权限设置
    $site_permission_enable = get_option('site_permission_enable');
    $menu_permission_redirect_url = get_option('menu_permission_redirect_url');

    // 如果设置为全站登录可见且用户未登录，则重定向到登录页面
     if ($site_permission_enable && !is_user_logged_in() && !is_admin()) {

     // 保存当前访问页面的 URL，以便登录后重定向回来
        $redirect_to = esc_url($_SERVER['REQUEST_URI']);
        // 构造登录页面 URL，并在其中包含重定向参数
        $login_url = wp_login_url($redirect_to);
        wp_redirect($menu_permission_redirect_url);
        exit;
        
}
}


// 注册插件卸载钩子
register_uninstall_hook(__FILE__, 'wp_rvp_uninstall');

// 卸载插件时执行的回调函数
function wp_rvp_uninstall() {
    // 删除数据库中保存的设置
    delete_option('menu_permission_category');
    delete_option('menu_permission_redirect_url');
    delete_option('site_permission_enable');
    delete_option('enable_redirect');
    delete_option('enable_text_hint');
    // 这里可以添加其他需要删除的数据库内容
}
