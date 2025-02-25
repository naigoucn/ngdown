<?php

function custom_excerpt_length($length) {
    return 100;
}
add_filter('excerpt_length', 'custom_excerpt_length', 999);

function add_remix_iconfont() {
    // 引入Remixicon的CSS文件
    wp_enqueue_style('remix-iconfont-css', plugins_url('css/remixicon.css', __FILE__), array(), null);
}

add_action('wp_enqueue_scripts', 'add_remix_iconfont');

function wechat_get_excerpt($raw_excerpt) {
    $excerpt = wp_strip_all_tags($raw_excerpt);
    $excerpt = trim(preg_replace("/[\n\r\t ]+/", ' ', $excerpt), ' ');
    $excerpt = mb_substr($excerpt, 0, 50, 'utf8') . '...';
    return $excerpt;
}

function wechat_get_thumb($post, $size) {
    if ($thumbnail_id = get_post_thumbnail_id($post->ID)) {
        $thumb = wp_get_attachment_image_src($thumbnail_id, $size)[0];
    }
    return $thumb ?? '';
}

function http_request($url, $method = 'GET', $params = []) {
    $ch = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
    }

    $result = curl_exec($ch);
    $status = curl_getinfo($ch);
    curl_close($ch);

    return intval($status["http_code"]) == 200 ? $result : $status["http_code"];
}

function custom_button_save_meta_data($post_id) {
    $fields = [
        'custom_button_link',
        'custom_button_description',
        'custom_extract_code',
        'custom_copyright_notice',
        'custom_button_display_option'
    ];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, "_$field", sanitize_text_field($_POST[$field]));
        }
    }

    if (isset($_POST['custom_alternate_links'])) {
        update_post_meta($post_id, '_custom_alternate_links', array_map('sanitize_text_field', $_POST['custom_alternate_links']));
    }
    if (isset($_POST['custom_alternate_codes'])) {
        update_post_meta($post_id, '_custom_alternate_codes', array_map('sanitize_text_field', $_POST['custom_alternate_codes']));
    }
}
add_action('save_post', 'custom_button_save_meta_data');

function generate_password($post_id) {
    $interval = floor(time() / (30 * 60));
    $transient_name = "download_password_{$post_id}_{$interval}";
    if (false === ($password = get_transient($transient_name))) {
        $password = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        set_transient($transient_name, $password, 30 * 60);
    }
    return $password;
}

function validate_password($post_id, $user_input_password) {
    return $user_input_password === generate_password($post_id);
}

function custom_button_validate_password() {
    $user_input_password = isset($_POST['download_password']) ? sanitize_text_field($_POST['download_password']) : '';
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (validate_password($post_id, $user_input_password)) {
        setcookie("download_access_{$post_id}", 'granted', time() + 60 * 30, '/');
        wp_send_json_success(['message' => 'success']);
    } else {
        wp_send_json_error(['message' => 'error']);
    }
}
add_action('wp_ajax_custom_button_validate_password', 'custom_button_validate_password');
add_action('wp_ajax_nopriv_custom_button_validate_password', 'custom_button_validate_password');

function parse_alternate_link($url) {
    $hosts = [
        'pan.baidu.com' => '百度网盘',
        '123684.com|123912.com' => '123网盘',
        'yun.xunlei.com' => '迅雷',
        'aliyundrive.com' => '阿里云盘',
        'pan.quark.cn' => '夸克云盘',
        'lanzou[a-z]\.com' => '蓝奏云盘',
        '189.cn' => '天翼云盘',
        'weiyun.com' => '微云',
        'github.com' => 'Github',
    ];

    foreach ($hosts as $pattern => $label) {
        if (preg_match("#^https?://({$pattern})#", $url, $matches)) {
            return "<a href='" . esc_url($url) . "' class='small-link-button'>{$label}</a>";
        }
    }
    return "<a href='" . esc_url($url) . "' class='small-link-button'>其他链接</a>";
}

function get_icon_from_url($url) {
    $hosts = [
        '189.cn' => 'tianyi.png',
        'weiyun.com' => 'weiyun.png',
        'github.com' => 'github.png',
        'pan.baidu.com' => 'baidupan.png',
        'www\.123684\.com|www\.123912\.com' => '123pan.png',
        'yun.xunlei.com' => 'xunlei.png',
        'aliyundrive.com' => 'alipan.png',
        'pan.quark.cn' => 'kuake.png',
        'lanzou[a-z]\.com' => 'lanzou.png',
    ];

    foreach ($hosts as $pattern => $icon) {
        if (preg_match("#^https?://({$pattern})#", $url, $matches)) {
            return "pan/{$icon}";
        }
    }
    return 'pan/down.png';
}

function get_link_label($url) {
    $hosts = [
        'github.com' => 'Github',
        'weiyun.com' => '微云',
        '189.cn' => '天翼云盘',
        'pan.baidu.com' => '百度网盘',
        'www\.123684\.com|www\.123912\.com' => '123网盘',
        'yun.xunlei.com' => '迅雷',
        'aliyundrive.com' => '阿里云盘',
        'pan.quark.cn' => '夸克云盘',
        'lanzou[a-z]\.com' => '蓝奏云盘',
    ];

    foreach ($hosts as $pattern => $label) {
        if (preg_match("#^https?://({$pattern})#", $url, $matches)) {
            return $label;
        }
    }
    return '下载链接';
}

function add_wechat_settings_menu() {
    add_menu_page(
        '微信机器人设置',
        '微信机器人',
        'manage_options',
        'wechat-settings',
        'wechat_settings_page',
        'dashicons-admin-generic',
        6
    );

    add_submenu_page(
        'wechat-settings',
        'AI模型设置',
        'AI模型',
        'manage_options',
        'ai-model-settings',
        'ai_model_settings_page'
    );

    add_submenu_page(
        'wechat-settings',
        'API Token设置',
        'API Token',
        'manage_options',
        'api-token-settings',
        'api_token_settings_page'
    );
}
add_action('admin_menu', 'add_wechat_settings_menu');

function wechat_settings_page() {
    include_once plugin_dir_path(__FILE__) . 'wechat-settings-page.php';
}

function ai_model_settings_page() {
    ?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php
            settings_fields('ai_model_settings_group');
            do_settings_sections('ai-model-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function api_token_settings_page() {
    ?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php
            settings_fields('api_token_settings_group');
            do_settings_sections('api-token-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function register_wechat_settings() {
    // 微信设置
    register_setting('wechat_settings_group', 'wechat_appid');
    register_setting('wechat_settings_group', 'wechat_secret');

    // AI模型设置
    register_setting('ai_model_settings_group', 'ai_api_url');
    register_setting('ai_model_settings_group', 'ai_api_key');
    register_setting('ai_model_settings_group', 'ai_model_name');

    // API Token设置
    register_setting('api_token_settings_group', 'api_token');

    // 微信设置
    add_settings_section(
        'wechat_settings_section',
        '微信设置',
        'wechat_settings_section_callback',
        'wechat-settings'
    );

    add_settings_field(
        'wechat_appid',
        'App ID',
        'wechat_appid_callback',
        'wechat-settings',
        'wechat_settings_section'
    );

    add_settings_field(
        'wechat_secret',
        'App Secret',
        'wechat_secret_callback',
        'wechat-settings',
        'wechat_settings_section'
    );

    // AI模型设置
    add_settings_section(
        'ai_model_settings_section',
        'AI模型设置',
        'ai_model_settings_section_callback',
        'ai-model-settings'
    );

    add_settings_field(
        'ai_api_url',
        'API URL',
        'ai_api_url_callback',
        'ai-model-settings',
        'ai_model_settings_section'
    );

    add_settings_field(
        'ai_api_key',
        'API 密钥',
        'ai_api_key_callback',
        'ai-model-settings',
        'ai_model_settings_section'
    );

    add_settings_field(
        'ai_model_name',
        '模型名称',
        'ai_model_name_callback',
        'ai-model-settings',
        'ai_model_settings_section'
    );

    // API Token设置
    add_settings_section(
        'api_token_settings_section',
        'API Token设置',
        'api_token_settings_section_callback',
        'api-token-settings'
    );

    add_settings_field(
        'api_token',
        'API Token',
        'api_token_callback',
        'api-token-settings',
        'api_token_settings_section'
    );
}
add_action('admin_init', 'register_wechat_settings');

function wechat_settings_section_callback() {
    echo '<p>在此处配置你的微信公众号设置。 QQ交流群：753880777 </p>';
}

function ai_model_settings_section_callback() {
    echo '<p>在此处配置你的AI模型设置。支持OPENAI 式调用 支持deepseek 阿里 腾讯 kimi 豆包等 <a href="http://www.naigou.cn/ngdown" target="_blank">大模型配置教程</a></p>';
}

function api_token_settings_section_callback() {
    echo '<p>在此处配置你的API Token。</p>';
}

function wechat_appid_callback() {
    echo "<input type='text' name='wechat_appid' value='" . esc_attr(get_option('wechat_appid')) . "' />";
}

function wechat_secret_callback() {
    echo "<input type='text' name='wechat_secret' value='" . esc_attr(get_option('wechat_secret')) . "' />";
}

function ai_api_url_callback() {
    $value = get_option('ai_api_url');
    echo "<select id='ai_api_url_select' onchange='updateApiUrl(this.value)' style='width: 300px; margin-right: 10px;'>";
    echo "<option value='' " . selected($value, '', false) . ">请选择</option>";
    echo "<option value='https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions' " . selected($value, 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions', false) . ">阿里云 DashScope</option>";
    echo "<option value='https://api.hunyuan.cloud.tencent.com/v1/chat/completions' " . selected($value, 'https://api.hunyuan.cloud.tencent.com/v1/chat/completions', false) . ">腾讯云 Hunyuan Turbo</option>";
    echo "<option value='https://api.deepseek.com/chat/completions' " . selected($value, 'https://api.deepseek.com/chat/completions', false) . ">DeepSeek</option>";
    echo "<option value='https://api.moonshot.cn/v1/chat/completions' " . selected($value, 'https://api.moonshot.cn/v1/chat/completions', false) . ">Moonshot</option>";
    echo "</select>";
    echo "<input type='text' id='ai_api_url_input' name='ai_api_url' value='" . esc_attr($value) . "' style='width: 300px;' placeholder='自定义API URL' />";
    echo "<script>
        function updateApiUrl(selectedValue) {
            document.getElementById('ai_api_url_input').value = selectedValue;
        }
    </script>";
}

function ai_api_key_callback() {
    echo "<input type='text' name='ai_api_key' value='" . esc_attr(get_option('ai_api_key')) . "' />";
}

function ai_model_name_callback() {
    $value = get_option('ai_model_name');
    echo "<select id='ai_model_name_select' onchange='updateModelName(this.value)' style='width: 300px; margin-right: 10px;'>";
    echo "<option value='' " . selected($value, '', false) . ">请选择</option>";
    echo "<option value='qwen-max' " . selected($value, 'qwen-max', false) . ">Qwen Max</option>";
    echo "<option value='qwen-max-latest' " . selected($value, 'qwen-max-latest', false) . ">Qwen Max Latest</option>";
    echo "<option value='hunyuan-turbo' " . selected($value, 'hunyuan-turbo', false) . ">Hunyuan Turbo</option>";
    echo "<option value='deepseek-reasoner' " . selected($value, 'deepseek-reasoner', false) . ">DeepSeek Reasoner</option>";
    echo "<option value='deepseek-chat' " . selected($value, 'deepseek-chat', false) . ">DeepSeek Chat</option>";
    echo "<option value='moonshot-v1-8k' " . selected($value, 'moonshot-v1-8k', false) . ">Moonshot v1-8k</option>";
    echo "</select>";
    echo "<input type='text' id='ai_model_name_input' name='ai_model_name' value='" . esc_attr($value) . "' style='width: 300px;' placeholder='自定义模型名称' />";
    echo "<script>
        function updateModelName(selectedValue) {
            document.getElementById('ai_model_name_input').value = selectedValue;
        }
    </script>";
}

function api_token_callback() {
    echo "<input type='text' name='api_token' value='" . esc_attr(get_option('api_token')) . "' />";
}

function handle_add_keyword_reply() {
    if (isset($_POST['add_keyword_reply_nonce']) && wp_verify_nonce($_POST['add_keyword_reply_nonce'], 'add_keyword_reply')) {
        $config_path = WEIXIN_ROBOT_PLUGIN_DIR . '/wechat-config.json';
        if (file_exists($config_path)) {
            $config = json_decode(file_get_contents($config_path), true);
            $config['keywords'] = $config['keywords'] ?? [];
            $new_keyword = sanitize_text_field($_POST['new_keyword']);
            $new_response = sanitize_textarea_field($_POST['new_response']);

            if (!empty($new_keyword) && !empty($new_response)) {
                $config['keywords'][$new_keyword] = $new_response;
                file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                add_settings_error('general', 'settings_updated', __('关键词回复已成功添加。'), 'updated');
            } else {
                add_settings_error('general', 'settings_updated', __('关键词和回复内容不能为空。'), 'error');
            }
        } else {
            add_settings_errors('general', 'settings_updated', __('配置文件不存在。'), 'error');
        }
    }
}
add_action('admin_init', 'handle_add_keyword_reply');

function ng_api_handler() {
    $data = json_decode(file_get_contents('php://input'), true);
    $api_token = isset($data['api_token']) ? sanitize_text_field($data['api_token']) : '';
    $post_id = isset($data['postid']) ? intval($data['postid']) : 0;

    if ($api_token !== get_option('api_token') || $post_id <= 0) {
        wp_send_json_error(['message' => '无效的请求参数'], 400);
        exit;
    }

    $link = get_post_meta($post_id, '_custom_button_link', true);
    $display_option = get_post_meta($post_id, '_custom_button_display_option', true);

    if (!$link || $display_option === 'hide') {
        wp_send_json_error(['message' => '文章没有开启密码下载。'], 404);
        exit;
    }

    $password = generate_password($post_id);
    wp_send_json_success(['message' => "下载密码是: {$password}"], 200);
    exit;
}
add_action('rest_api_init', function () {
    register_rest_route('ngapi/v1', '/get-password', ['methods' => 'POST', 'callback' => 'ng_api_handler']);
});



