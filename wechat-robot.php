<?php
global $plugin_config;

require(WEIXIN_ROBOT_PLUGIN_DIR . '/wechat.php');

// 对接微信公众号
add_action('parse_request', 'wechat_robot_redirect', 4);
function wechat_robot_redirect($wp)
{
    if (isset($_GET['wechat'])) {
        // 获取设置中的AppID和Secret
        $appid = get_option('wechat_appid');
        $secret = get_option('wechat_secret');
        
        $robot = new WechatRobot("wechat", $appid, $secret, true);
        
        $robot->run();
    }
}

class WechatRobot extends Wechat
{
    private $config;

    public function __construct($token, $appid, $appsecret, $debug = false)
    {
        parent::__construct($token, $appid, $appsecret, $debug);
        $config_path = WEIXIN_ROBOT_PLUGIN_DIR . '/wechat-config.json';
        if (file_exists($config_path)) {
            $this->config = json_decode(file_get_contents($config_path), true);
        } else {
            $this->config = [];
        }
    }

    protected function onText()
    {
        global $plugin_config;

        $msg = trim($this->getRequest('content'));

        if (strpos(strtolower($msg), 'id') === 0) {
            $post_id_str = substr($msg, 2);
            $post_id = intval($post_id_str);

            if ($post_id > 0) {
                // 检查文章是否有链接并且设置为显示
                $link = get_post_meta($post_id, '_custom_button_link', true);
                $display_option = get_post_meta($post_id, '_custom_button_display_option', true);

                if (!$link || $display_option === 'hide') {
                    $response = "文章没有开启密码下载。";
                } else {
                    $password = generate_password($post_id);
                    $expiration_time = date('Y-m-d H:i:s', time() + 30 * 60);
                    $response = "下载密码是: {$password}";
                }
            } else {
                $response = "无效的文章ID，请重新输入。";
            }
        } elseif (strtolower($msg) === '最新' || strtolower($msg) === '推送最新文章') {
            $args = array(
                'numberposts' => 1,
                'orderby' => 'post_date',
                'order' => 'DESC'
            );
            $latest_posts = wp_get_recent_posts($args);
            if (!empty($latest_posts)) {
                $latest_post = reset($latest_posts);
                $response = str_replace(
                    ['{latest_post_title}', '{latest_post_url}'],
                    [$latest_post['post_title'], get_permalink($latest_post['ID'])],
                    $this->config['keywords']['最新']
                );
            } else {
                $response = "暂无最新文章。";
            }
        } else {
            // 检查是否匹配预设的关键词回复
            $keywords = isset($this->config['keywords']) ? $this->config['keywords'] : [];

            if (array_key_exists(strtolower($msg), $keywords)) {
                $response = $keywords[strtolower($msg)];
            } else {
                // 调用AI API进行一般回复
                $api_url = get_option('ai_api_url');
                $api_key = get_option('ai_api_key');
                $model_name = get_option('ai_model_name');
                if (!$api_key) {
                    $response = "无法获取API密钥，请联系管理员。";
                } else {
                    $url = $api_url;
                    $headers = array(
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $api_key
                    );
                    $data = json_encode(array(
                        "model" => $model_name,
                        "messages" => array(
                            array(
                                "role" => "user",
                                "content" => $msg
                            )
                        ),
                        "enable_enhancement" => true
                    ));

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $response = "请求失败: " . curl_error($ch);
                    } else {
                        $decoded_result = json_decode($result, true);
                        if (isset($decoded_result['choices'][0]['message']['content'])) {
                            $response = $decoded_result['choices'][0]['message']['content'];
                        } else {
                            $response = "未能获取AI回复，请稍后再试。";
                        }
                    }
                    curl_close($ch);
                }
            }
        }

        $this->responseText($response);
    }

    protected function onSubscribe()
    {
        global $plugin_config;

        $subscribe_message = isset($this->config['subscribe_message']) ? $this->config['subscribe_message'] : '欢迎关注我们的公众号！';
        $this->responseText($subscribe_message);
    }

    // 点击菜单
    protected function onClick() {
    
    }
}