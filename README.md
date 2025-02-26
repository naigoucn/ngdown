# WordPress附件密码下载插件 - NGDown

## 前言

这是一个用于WordPress的附件密码下载插件，支持对接微信公众号、多平台机器人（QQ/TG等）、AI大模型等功能。程序永久免费且开源。

🔗 [GitHub仓库](https://github.com/naigoucn/ngdown)  
📥 [旧版本下载](https://www.123684.com/s/uXJuVv-mN4U3)  
🎨 界面参考：[6KE论坛样式](https://6ke.li/forum-post/1048.html)  
🤖 机器人参考：[微信机器人1](http://github.com/wangvsa/wechat-robot)、[微信机器人2](https://github.com/shiheme/wechat-robot-guoqing)  
🖼️ 图标库：[RemixIcon](https://remixicon.com)

---

## 功能特性

- 🔑 密码验证下载附件
- 🤖 微信公众号自动生成随机密码
- 📰 公众号文章推送
- ⏳ 单篇文章密码30分钟自动刷新
- 🧠 支持AI大模型（OpenAI/通义千问/DeepSeek等）
- 🔌 提供通用API接口
- 🌐 多平台机器人支持（QQ/TG等）
- ⚙️ 后台自定义设置
- 📁 智能识别网盘链接图标
- 🚦 密码下载开关控制

---

## 界面演示

![后台设置](https://ttimg.cn/cdn/naigou_cn/2025/02/20250225111059803.png)
![公众号设置](https://ttimg.cn/cdn/naigou_cn/2025/02/20250225111145932.png)
![AI设置](https://ttimg.cn/cdn/naigou_cn/2025/02/20250225111924815.png)
![API设置](https://ttimg.cn/cdn/naigou_cn/2025/02/20250225112358893.png)

---

## 使用指南

### 文章配置

1. 在文章编辑页面底部找到插件设置模块
2. 填写下载信息（留空则不显示对应内容）
3. 选择显示/隐藏选项

![文章设置示例](https://ttimg.cn/cdn/naigou_cn/2025/02/20250225112506405.png)

---

### 微信公众号对接

#### 配置步骤
1. 获取开发者凭证：
   - 登录[微信公众平台](https://mp.weixin.qq.com)
   - 进入「开发」-「基本配置」
   - 获取AppID和AppSecret

![开发者凭证获取](https://ttimg.cn/cdn/naigou_cn/2025/02/20250209021443491.png)

2. 服务器配置：
<h3 class="wp-block-heading">TG机器人</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>部分框架提供对接其他平台的功能 参考QQ机器人的对接方式</p>
<!-- /wp:paragraph -->
