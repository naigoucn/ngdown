<?php
/*
Plugin Name: 奶狗附件密码下载
Plugin URI: https://www.naigou.cn/
Description: 文章附件下载按钮带密码保护和智能AI回复
Version: 1.0.0
Author: 奶狗
Author URI: https://www.naigou.cn/
License: GPLv2 or later
Text Domain: post-download-button
*/

// 引入对接微信公众号文件
define('WEIXIN_ROBOT_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));
require_once(WEIXIN_ROBOT_PLUGIN_DIR . '/wechat-robot.php');

// 引入逻辑文件
require_once(WEIXIN_ROBOT_PLUGIN_DIR . '/includes/functions.php');

// 注册自定义元框
function custom_button_add_meta_box() {
    add_meta_box(
        'custom_button_meta_box',
        '附件下载按钮',
        'custom_button_meta_box_callback',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'custom_button_add_meta_box');

// 渲染元框内容
function custom_button_meta_box_callback($post) {
    $link = get_post_meta($post->ID, '_custom_button_link', true);
    $button_description = get_post_meta($post->ID, '_custom_button_description', true);
    $extract_code = get_post_meta($post->ID, '_custom_extract_code', true);
    $display_option = get_post_meta($post->ID, '_custom_button_display_option', true);
    $alternate_links = get_post_meta($post->ID, '_custom_alternate_links', true);
    $alternate_codes = get_post_meta($post->ID, '_custom_alternate_codes', true);
    $copyright_notice = get_post_meta($post->ID, '_custom_copyright_notice', true);
    $enable_password_download = get_post_meta($post->ID, '_enable_password_download', true);

    if (!is_array($alternate_links)) {
        $alternate_links = [];
    }
    if (!is_array($alternate_codes)) {
        $alternate_codes = [];
    }

    ?>
    <p>
        <label for="custom_button_link">主要下载链接：</label>
        <input type="text" id="custom_button_link" name="custom_button_link" value="<?php echo esc_attr($link);?>">
    </p>
    <p>
        <label for="custom_button_description">文件名称：</label>
        <span style="margin-right: 10px;"><i class="fas fa-download"></i></span>
        <input type="text" id="custom_button_description" name="custom_button_description" value="<?php echo esc_attr($button_description);?>">
    </p>
    <p>
        <label for="custom_extract_code">提取码：</label>
        <input type="text" id="custom_extract_code" name="custom_extract_code" value="<?php echo esc_attr($extract_code);?>">
    </p>
    <p>
        <label for="custom_copyright_notice">版权说明：</label>
        <input type="text" id="custom_copyright_notice" name="custom_copyright_notice" value="<?php echo esc_attr($copyright_notice);?>">
    </p>
    <p>
        <label for="custom_button_display_option">显示选项：</label>
        <select id="custom_button_display_option" name="custom_button_display_option">
            <option value="show" <?php selected($display_option, 'show');?>>显示</option>
            <option value="hide" <?php selected($display_option, 'hide');?>>隐藏</option>
        </select>
    </p>
    <p>
        <label for="_enable_password_download">启用密码下载：</label>
        <input type="checkbox" id="_enable_password_download" name="_enable_password_download" value="1" <?php checked($enable_password_download, 1); ?>>
    </p>
    <div>
        <h4>备选下载链接 (最多2个)</h4>
        <?php for ($i = 0; $i < 2; $i++): ?>
            <p class="alternate-link-input">
                <label for="custom_alternate_link_<?php echo $i;?>">备选链接 <?php echo $i+1 ?>:</label>
                <input type="text" id="custom_alternate_link_<?php echo $i;?>" name="custom_alternate_links[]" value="<?php echo isset($alternate_links[$i]) ? esc_attr($alternate_links[$i]) : ''; ?>">
                <label for="custom_alternate_code_<?php echo $i;?>">提取码:</label>
                <input type="text" id="custom_alternate_code_<?php echo $i;?>" name="custom_alternate_codes[]" value="<?php echo isset($alternate_codes[$i]) ? esc_attr($alternate_codes[$i]) : ''; ?>">
            </p>
        <?php endfor; ?>
    </div>
    <p style="font-size: 12px; color: #888; text-align: right;">插件版权所有：<a href="https://www.naigou.cn/">奶狗博客</a></p>
    <?php
}

// 保存元框数据
function custom_button_save_meta_box_data($post_id) {
    if (array_key_exists('_custom_button_link', $_POST)) {
        update_post_meta(
            $post_id,
            '_custom_button_link',
            sanitize_text_field($_POST['_custom_button_link'])
        );
    }
    if (array_key_exists('_custom_button_description', $_POST)) {
        update_post_meta(
            $post_id,
            '_custom_button_description',
            sanitize_text_field($_POST['_custom_button_description'])
        );
    }
    if (array_key_exists('_custom_extract_code', $_POST)) {
        update_post_meta(
            $post_id,
            '_custom_extract_code',
            sanitize_text_field($_POST['_custom_extract_code'])
        );
    }
    if (array_key_exists('_custom_copyright_notice', $_POST)) {
        update_post_meta(
            $post_id,
            '_custom_copyright_notice',
            sanitize_text_field($_POST['_custom_copyright_notice'])
        );
    }
    if (array_key_exists('_custom_button_display_option', $_POST)) {
        update_post_meta(
            $post_id,
            '_custom_button_display_option',
            sanitize_text_field($_POST['_custom_button_display_option'])
        );
    }
    if (array_key_exists('_enable_password_download', $_POST)) {
        update_post_meta(
            $post_id,
            '_enable_password_download',
            intval($_POST['_enable_password_download'])
        );
    } else {
        delete_post_meta($post_id, '_enable_password_download');
    }
    if (array_key_exists('custom_alternate_links', $_POST)) {
        update_post_meta(
            $post_id,
            '_custom_alternate_links',
            array_map('sanitize_text_field', $_POST['custom_alternate_links'])
        );
    }
    if (array_key_exists('custom_alternate_codes', $_POST)) {
        update_post_meta(
            $post_id,
            '_custom_alternate_codes',
            array_map('sanitize_text_field', $_POST['custom_alternate_codes'])
        );
    }
}
add_action('save_post', 'custom_button_save_meta_box_data');

// 加载插件的CSS文件
function custom_button_enqueue_styles() {
    wp_enqueue_style('custom-button-style', plugins_url('styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'custom_button_enqueue_styles');

// 显示下载按钮及相关选项
function custom_button_display_button($content) {
    global $post;

    // 检查是否是单篇文章页面
    if (!is_single()) {
        return $content;
    }

    $link = get_post_meta($post->ID, '_custom_button_link', true);
    $button_description = get_post_meta($post->ID, '_custom_button_description', true);
    $extract_code = get_post_meta($post->ID, '_custom_extract_code', true);
    $display_option = get_post_meta($post->ID, '_custom_button_display_option', true);
    $alternate_links = get_post_meta($post->ID, '_custom_alternate_links', true);
    $alternate_codes = get_post_meta($post->ID, '_custom_alternate_codes', true);
    $copyright_notice = get_post_meta($post->ID, '_custom_copyright_notice', true);
    $enable_password_download = get_post_meta($post->ID, '_enable_password_download', true);

    if (!is_array($alternate_links)) {
        $alternate_links = [];
    }
    if (!is_array($alternate_codes)) {
        $alternate_codes = [];
    }

    // 默认开启密码下载
    if ($enable_password_download === '') {
        $enable_password_download = 1;
    }

    if ($display_option === 'hide') {
        return $content;
    }

    if ($link && $button_description) {
        ob_start();
        ?>
        <div style="clear: both; margin-top: 10px; margin-bottom: 10px">
            <h2 class="wp-block-heading">文件下载</h2>
            <div class="pan-content">
                <div class="flex ac jsb">
                    <div class="flex ac">
                        <div class="mr20">
                            <div class="ngfile-download-icon">
                                <img src="<?php echo plugins_url('img/' . get_icon_from_url($link), __FILE__); ?>" alt="Download Icon" style="width:50px; height:50px;">
                            </div>
                        </div>
                        <div class="text-ellipsis-2 file-download-name"><?php echo esc_html($button_description); ?></div>
                    </div>
                    <div class="flex0 ml20">
                        <?php if (!$enable_password_download || isset($_COOKIE['download_access_' . $post->ID])): ?>
                            <a href="<?php echo esc_url($link); ?>" class="but file-download-btn" target="_blank" style="background-color: #4e6ef2; !important; box-shadow: 0 2px 8px #006eff33; color: #fff;"><?php echo esc_html(get_link_label($link)); ?></a>
                        <?php else: ?>
                            <div id="get_download_link_button" 
                                 class="but file-download-btn"  
                                 style="background-color: #4e6ef2; !important; color: #fff;"
                                 onclick="openPopup()">
                                获取下载链接
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (isset($_COOKIE['download_access_' . $post->ID]) || !$enable_password_download): ?>
                    <div class="file-download-info">
                        <?php if (!empty($extract_code)): ?>
                            <div class="file-download-desc">
                                <div class="desc-left">提取码：<?php echo esc_html($extract_code); ?></div>
                                <div class="desc-right">版权说明：<?php echo esc_html($copyright_notice); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="file-download-desc">
                                <div class="desc-left">版权说明：<?php echo esc_html($copyright_notice); ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="small-links-container">
                            <?php foreach ($alternate_links as $index => $alt_link): ?>
                                <?php if (!empty($alt_link)): ?>
                                    <div class="small-link">
                                        <a href="<?php echo esc_url($alt_link); ?>" class="small-link-button" style="color: #fff;" target="_blank">
                                            <?php echo esc_html(get_link_label($alt_link)); ?>
                                        </a>
                                        <?php if (!empty($alternate_codes[$index])): ?>
                                            <span class="download-code" data-clipboard-text="<?php echo esc_attr($alternate_codes[$index]); ?>">提取码：<?php echo esc_html($alternate_codes[$index]); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!$enable_password_download || !isset($_COOKIE['download_access_' . $post->ID])): ?>
                <!-- 弹窗结构 -->
                <div id="myModal" class="modal">
                    <div class="modal-content">
                        <div class="ngtab-container">
                          <button class="ngtab active" data-tab="wechat">
                              <i class="ri-wechat-line"></i>
                          </button>
                          <button class="ngtab" data-tab="qq">
                              <i class="ri-qq-line"></i>
                          </button>
                          <button class="ngtab" data-tab="tg">
                              <i class="ri-telegram-2-line"></i>
                          </button>
                        </div>

                        <div id="wechat_tab_content" class="ngtab-content active" >
                            <div class="qr-container">
                                <img src="<?php echo plugins_url('img/wechat_qr.png', __FILE__); ?>" alt="WeChat QR Code">
                            </div>
                            <p style="font-size: 12px; color: #888;">
                                获取密码请关注公众号回复<br>
                                <strong><span id="copy-id-<?php echo esc_js(json_encode($post->ID)); ?>" style="color: blue; cursor: pointer;" onclick="copyToClipboard(this)">ID<?php echo esc_html($post->ID); ?></span></strong> 
                            </p>
                        </div>
                        <div id="qq_tab_content" class="ngtab-content">
                            <div class="qr-container">
                                <img src="<?php echo plugins_url('img/qq_qr.png', __FILE__); ?>" alt="QQ QR Code">
                            </div>
                            <p style="font-size: 12px; color: #888;">
                                获取密码请扫码加群发送消息<br>
                                <strong><span id="copy-password-<?php echo esc_js(json_encode($post->ID)); ?>" style="color: green; cursor: pointer;" onclick="copyToClipboard(this)"><?php echo esc_html('/文章密码 '); ?><?php echo esc_html($post->ID); ?></span></strong>
                            </p>
                        </div>
                        
                        <div id="tg_tab_content" class="ngtab-content">
                            <div class="qr-container">
                                <img src="<?php echo plugins_url('img/tg_qr.png', __FILE__); ?>" alt="TG QR Code">
                            </div>
                            <p style="font-size: 12px; color: #888;">
                                获取密码请扫码加群发送消息<br>
                                <strong><span id="copy-password-<?php echo esc_js(json_encode($post->ID)); ?>" style="color: pink; cursor: pointer;" onclick="copyToClipboard(this)"><?php echo esc_html('/文章密码 '); ?><?php echo esc_html($post->ID); ?></span></strong>
                            </p>
                        </div>
                        
                        <input type="text" id="download_password_input" style="width: calc(100% - 22px); padding: 5px; margin-top: 5px; border: 1px solid #ccc; border-radius: 3px;" placeholder="密码">
                         <div id="submit_password_button" 
                             style="background-color: #4e6ef2; color: white; padding: 5px; border: none; border-radius: 3px; cursor: pointer; margin-top: 10px; width: calc(100% - 22px); display: inline-block; text-align: center;"
                             onclick="handleSubmit()">
                            提交
                        </div>
                        
                        <div id="password_error_message" style="color: red; margin-top: 10px; display: none;"></div>
                        <div id="copy_success_message" style="color: green; margin-top: 10px; display: none;"></div>
                    </div>
                </div>
                <script>
                    function openPopup() {
                        var modal = document.getElementById("myModal");
                        modal.style.display = "block";
                        document.body.style.overflow = "hidden"; // 禁用滚动
                    }

                    function closePopup() {
                        var modal = document.getElementById("myModal");
                        modal.style.display = "none";
                        document.body.style.overflow = ""; // 启用滚动
                    }

                    function handleSubmit() {
                        var user_input_password = document.getElementById('download_password_input').value;

                        if (user_input_password !== '') {
                            var formData = new FormData();
                            formData.append('action', 'custom_button_validate_password');
                            formData.append('download_password', user_input_password);
                            formData.append('post_id', <?php echo esc_js(json_encode($post->ID)); ?>);

                            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // 设置cookie以便下次访问时不需要再次输入密码
                                    document.cookie = 'download_access_<?php echo esc_js(json_encode($post->ID)); ?>=true; path=/';
                                    // 刷新页面以显示下载按钮和提取码按钮
                                    location.reload();
                                } else {
                                    showErrorMessage('密码错误，请重试。');
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        } else {
                            showErrorMessage('请输入密码。');
                        }
                    }

                    function setupEventListeners() {
                        // Tab切换功能
                        const tabs = document.querySelectorAll('.ngtab');
                        tabs.forEach(tab => {
                            tab.addEventListener('click', function() {
                                const targetTabContentId = this.getAttribute('data-tab') + '_tab_content';
                                
                                tabs.forEach(t => t.classList.remove('active'));
                                document.querySelectorAll('.ngtab-content').forEach(tc => tc.classList.remove('active'));

                                this.classList.add('active');
                                document.getElementById(targetTabContentId).classList.add('active');
                            });
                        });

                        // 关闭弹窗点击外部区域
                        var modal = document.getElementById("myModal");
                        window.onclick = function(event) {
                            if (event.target == modal) {
                                closePopup();
                            }
                        }
                    }

                    function copyToClipboard(element) {
                        const tempInput = document.createElement('textarea');
                        tempInput.value = element.textContent.trim();
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempInput);
                        showSuccessMessage('已复制到剪贴板: ' + element.textContent.trim());
                    }

                    function showSuccessMessage(message) {
                        var successMessage = document.getElementById('copy_success_message');
                        successMessage.textContent = message;
                        successMessage.style.display = 'block';
                        setTimeout(function() {
                            successMessage.style.display = 'none';
                        }, 1000);
                    }

                    function showErrorMessage(message) {
                        var errorMessage = document.getElementById('password_error_message');
                        errorMessage.textContent = message;
                        errorMessage.style.display = 'block';
                        setTimeout(function() {
                            errorMessage.style.display = 'none';
                        }, 1000);
                    }

                    window.onload = function() {
                        setupEventListeners();
                    };

                    // 如果是通过AJAX加载的内容，重新绑定事件监听器
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document).ajaxComplete(function() {
                            setupEventListeners();
                        });
                    }
                </script>
            <?php endif; ?>
        </div>
        <?php
        return $content . ob_get_clean();
    }
    return $content;
}
add_filter('the_content', 'custom_button_display_button');



