#  <center> 旋动密信
纵然寻觅，情深难觅
  <br>
[**旋刻官网**](https://xuankr.com)&nbsp;&nbsp;｜&nbsp;&nbsp;[**演示地址**](https://voodong.cn)&nbsp;&nbsp;｜&nbsp;&nbsp;[**帮助文档**](https://docs.voodong.cn)</center>
  
  ---
  
## 介绍
旋动是一个简单的匿名信件收发系统，让用户能够发送匿名信件，支持阅后即焚功能。采用简洁的界面设计和强大的加密机制，确保信息仅被预期收件人查看，且可设置查看后自动销毁。

  ---
  
## 安装教程

```
运行环境：
- PHP 7.0+（推荐7.4+）
- MySQL 5.6+（或MariaDB 10.0+）
- Web服务器（Apache/Nginx）
```
<br>
1. 下载压缩包并上传到站点根目录。<br>
2. 访问http://example.com/install.php<br>
3. 执行安装，然后返回首页<br><br>
安装后请注意删除install.php文件。

---


## 核心功能
- 阅后即焚 ：发送的消息可设置为查看一次后自动永久删除。
- 匿名发信 ：全局匿名发送信件。
- 轻松查询 ：支持按收信人查找信件。
- 简易安装 ：提供三步安装向导，快速部署密信系统。
- 响应式设计 ：适配各种设备屏幕，提供一致的用户体验。

---

## 亮点介绍
- 隐私保护 ：阅后即焚和加密机制确保信息不被泄露。
- 简洁易用 ：用户界面直观，无需复杂操作。
- 安全可靠 ：采用预处理语句防止SQL注入，使用htmlspecialchars防止XSS攻击。
- 灵活配置 ：支持自定义站点标题、描述和URL。
- 自动部署 ：安装向导自动创建数据库表和配置文件。

---

## 项目结构

```
/xuandong
├── assets/
│   └── style.css
│   └── tailwind.config.js
├── config.php       # 数据库配置文件
├── install.php      # 安装向导
├── new-message.php  # 发送消息页面
├── strong-encryption.php  # 加密消息查看页面
└── view-message.php # 消息查询页面
```
---

## 更多信息
如有任何问题，请与我们联系：pd@xuankr.com<br><br>旋动产品团队未对产品进行任何商业化，集体为爱发电。如果您喜欢本产品，可在个人资金条件允许的情况下向我们打赏一些赏金，非常感谢！<br>支付宝：mail@xuankr.com
