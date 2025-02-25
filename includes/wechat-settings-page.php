<div class="wrap">
    <h1>微信机器人设置</h1>

    <!-- 显示现有关键词回复 -->
    <h2>关键词回复</h2>
    <p>关注回复 在 插件目录 wechat-config.json 文件内 自行修改</p>
    <table class="widefat fixed striped posts">
        <thead>
            <tr>
                <th scope="col">关键词</th>
                <th scope="col">回复内容</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $config_path = WEIXIN_ROBOT_PLUGIN_DIR . '/wechat-config.json';
            if (file_exists($config_path)) {
                $config = json_decode(file_get_contents($config_path), true);
                if (isset($config['keywords'])) {
                    foreach ($config['keywords'] as $keyword => $response) {
                        echo "<tr><td>{$keyword}</td><td>{$response}</td></tr>";
                    }
                }
            }
            ?>
        </tbody>
    </table>

    <!-- 表单来添加新的关键词回复 -->
    <h2>新增关键词回复</h2>
    <form method="post" action="">
        <?php wp_nonce_field('add_keyword_reply', 'add_keyword_reply_nonce'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="new_keyword">关键词</label></th>
                <td><input type="text" id="new_keyword" name="new_keyword" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="new_response">回复内容</label></th>
                <td><textarea id="new_response" name="new_response" rows="5" cols="50" required></textarea></td>
            </tr>
        </table>
        <?php submit_button('添加关键词回复'); ?>
    </form>

    <!-- 其他设置 -->
    <form method="post" action="options.php">
        <?php
        settings_fields('wechat_settings_group');
        do_settings_sections('wechat-settings');
        submit_button('保存微信设置');
        ?>
    </form>
</div>