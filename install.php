<?php
// XUANDONG TECH 2025
// 这是旋动密信安装程序文件，如果二次开发，可在本处修改部分内容。

// 初始化变量
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = ['text' => '', 'type' => ''];
$db_config = [];
$site_config = [];
$current_domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 步骤1：数据库配置处理
    if ($step == 1) {
        $db_config = [
            'servername' => $_POST['servername'] ?? 'localhost',
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'dbname' => $_POST['dbname'] ?? ''
        ];

        // 验证必填字段
        if(empty($db_config['username']) || empty($db_config['dbname'])) {
            $message = ['text' => '数据库用户名和数据库名不能为空', 'type' => 'error'];
        } else {
            // 测试数据库连接
            $conn = @new mysqli($db_config['servername'], $db_config['username'], $db_config['password']);
            if ($conn->connect_error) {
                $message = ['text' => '数据库连接失败: ' . $conn->connect_error, 'type' => 'error'];
            } else {
                // 创建数据库和表
                $conn->query("CREATE DATABASE IF NOT EXISTS `{$db_config['dbname']}`");
                $conn->select_db($db_config['dbname']);

                // 创建messages表
                $sql = "CREATE TABLE IF NOT EXISTS messages (
                    message_id CHAR(5) PRIMARY KEY,
                    recipient VARCHAR(255) NULL,
                    content TEXT NOT NULL,
                    burn_after_reading TINYINT(1) NOT NULL DEFAULT 0,
                    encryption_status TINYINT(1) DEFAULT 0 COMMENT '0:未加密, 1:已加密',
                    read_status TINYINT(1) DEFAULT 0 COMMENT '0:未读, 1:已读',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    is_read TINYINT(1) DEFAULT 0
                )";
                $conn->query($sql);

                // 创建vdinformation表
                $sql = "CREATE TABLE IF NOT EXISTS vdinformation (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    site_title VARCHAR(255) NOT NULL,
                    site_description TEXT,
                    site_url VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $conn->query($sql);
                $conn->close();

                // 生成config.php文件
                $config_content = <<<PHP
<?php
// 数据库配置
\$servername = "{$db_config['servername']}";
\$username = "{$db_config['username']}";
\$password = "{$db_config['password']}";
\$dbname = "{$db_config['dbname']}";

// 创建连接
\$conn = new mysqli(\$servername, \$username, \$password, \$dbname);

// 检查连接
if (\$conn->connect_error) {
    die("数据库连接失败: " . \$conn->connect_error);
}

// 获取站点配置
\$site_config = [];
\$sql = "SELECT * FROM vdinformation LIMIT 1";
\$result = \$conn->query(\$sql);
if (\$result && \$result->num_rows > 0) {
    \$site_config = \$result->fetch_assoc();
}

\$site_title = isset(\$site_config['site_title']) ? \$site_config['site_title'] : '旋动密信';
\$site_description = isset(\$site_config['site_description']) ? \$site_config['site_description'] : '加密信件系统';
\$site_url = isset(\$site_config['site_url']) ? \$site_config['site_url'] : '';
?>
PHP;

                // 写入配置文件
                if (file_put_contents('config.php', $config_content) === false) {
                    $message = ['text' => '无法写入配置文件，请检查文件权限', 'type' => 'error'];
                } else {
                    header("Location: install.php?step=2");
                    exit;
                }
            }
        }
    }
    // 步骤2：站点配置处理
    elseif ($step == 2) {
        // 将原变量名$site_config改为$new_site_config避免冲突
        $new_site_config = [
            'site_title' => $_POST['site_title'] ?? '旋动密信',
            'site_description' => $_POST['site_description'] ?? '',
            'site_url' => $_POST['site_url'] ?? ''
        ];

        // 验证必填字段
        if(empty($new_site_config['site_title']) || empty($new_site_config['site_url'])) {
            $message = ['text' => '站点标题和站点地址不能为空', 'type' => 'error'];
        } else {
            // 连接数据库并保存站点信息
            include 'config.php';
            
            // 检查vdinformation表是否存在
            $result = $conn->query("SHOW TABLES LIKE 'vdinformation'");
            if($result->num_rows == 0) {
                $message = ['text' => '站点配置表不存在，请重新安装', 'type' => 'error'];
            } else {
                // 插入站点配置 - 使用$new_site_config变量
                $stmt = $conn->prepare("INSERT INTO vdinformation (site_title, site_description, site_url) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $new_site_config['site_title'], $new_site_config['site_description'], $new_site_config['site_url']);
                
                if ($stmt->execute()) {
                    header("Location: install.php?step=3");
                    exit;
                } else {
                    $message = ['text' => '站点信息保存失败: ' . $conn->error, 'type' => 'error'];
                }
                $stmt->close();
                $conn->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>旋动密信安装向导</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#7C3AED',
                        accent: '#EC4899',
                        neutral: '#64748B',
                        'neutral-light': '#F1F5F9',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .step-active {
                @apply border-primary bg-primary text-white;
            }
            .step-inactive {
                @apply border-gray-300 bg-white text-gray-500 hover:border-primary hover:text-primary transition-colors;
            }
            .form-input {
                @apply w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all;
            }
            .btn-primary {
                @apply w-full py-3 px-6 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 active:bg-primary/80 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2;
            }
            .card {
                @apply bg-white rounded-xl shadow-md overflow-hidden transition-all hover:shadow-lg;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <div class="max-w-2xl mx-auto px-4 py-8 md:py-12">
        <!-- 安装程序标题 -->
        <div class="text-center mb-10">
            <h1 class="text-[clamp(1.8rem,4vw,2.5rem)] font-bold text-gray-800 mb-2">安装旋动密信</h1>
            <p class="text-neutral">开源的加密信件系统</p>
        </div>

        <!-- 消息提示 -->
        <?php if(!empty($message['text'])): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] == 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200'; ?>">
            <i class="fa fa-<?php echo $message['type'] == 'error' ? 'exclamation-circle' : 'check-circle'; ?> mr-2"></i><?php echo $message['text']; ?>
        </div>
        <?php endif; ?>

        <!-- 安装步骤内容 -->
        <div class="card">
            <!-- 步骤1：数据库配置 -->
            <?php if($step == 1): ?>
            <div class="p-6 md:p-8 animate-fadeIn">
                <h2 class="text-xl font-bold text-gray-800 mb-6">旋刻科技开源代码使用提示</h2>
                <p>一旦您开始安装，即表示您同意下述提示说明：<br><br>
                个人用户：<br>可自由使用、修改、分发代码，但禁止将代码或衍生作品用于任何商业目的（包括直接销售、内部分发收费、广告收入等）。<br><br>
                企业用户：<br>
                使用代码需在每个页面显著位置添加指向 xuankr.com 的超链接。<br>
                禁止将代码或衍生作品商业化（定义同上）。<br><br>
                共同要求：所有二次开发成果必须公开源码（AGPLv3）并保留本使用提示。<br>愿共同构建良好的开源生态！完整协议：根目录/LICENSE。</p>
            </div>
            <div class="p-6 md:p-8 animate-fadeIn">
                <h2 class="text-xl font-bold text-gray-800 mb-6">数据库配置</h2>
                <form method="post" class="space-y-5">
                    <div>
                        <label for="servername" class="block text-sm font-medium text-gray-700 mb-1">数据库地址</label>
                        <input type="text" id="servername" name="servername" value="localhost" class="form-input"
                            placeholder="数据库服务器地址，通常为 localhost">
                    </div>
                    <div>
                        <label for="dbname" class="block text-sm font-medium text-gray-700 mb-1">数据库名称</label>
                        <input type="text" id="dbname" name="dbname" class="form-input"
                            placeholder="数据库名称" required>
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">数据库用户名</label>
                        <input type="text" id="username" name="username" class="form-input"
                            placeholder="数据库用户名" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">数据库密码</label>
                        <input type="password" id="password" name="password" class="form-input"
                            placeholder="数据库密码">
                    </div>
                    <div class="pt-3">
                        <button type="submit" class="btn-primary">
                            <span>下一步</span>
                            <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- 步骤2：站点配置 -->
            <?php if($step == 2): ?>
            <div class="p-6 md:p-8 animate-fadeIn">
                <h2 class="text-xl font-bold text-gray-800 mb-6">写入站点信息</h2>
                <form method="post" class="space-y-5">
                    <div>
                        <label for="site_title" class="block text-sm font-medium text-gray-700 mb-1">命名</label>
                        <input type="text" id="site_title" name="site_title" class="form-input"
                            placeholder="站点名称">
                    </div>
                    <div>
                        <label for="site_description" class="block text-sm font-medium text-gray-700 mb-1">站点介绍</label>
                        <textarea id="site_description" name="site_description" rows="3" class="form-input"
                            placeholder="写一个独属于你的站点标题"></textarea>
                    </div>
                    <div>
                        <label for="site_url" class="block text-sm font-medium text-gray-700 mb-1">站点地址</label>
                        <input type="url" id="site_url" name="site_url" value="http://<?php echo $current_domain; ?>" class="form-input"
                            placeholder="您的网站完整URL，如 https://example.com">
                    </div>
                    <div class="pt-3">
                        <button type="submit" class="btn-primary">
                            <span>完成安装</span>
                            <i class="fa fa-check"></i>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- 步骤3：安装完成 -->
            <?php if($step == 3): ?>
            <div class="p-6 md:p-8 text-center animate-fadeIn">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa fa-check text-3xl text-green-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">安装成功</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">您已成功安装旋动密信，请删除根目录install.php文件</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center text-gray-500 text-sm mt-12 pb-6">
        <p>纵然寻觅，情深难觅</p>
    </footer>

    <script>
        // 页面加载时的动画效果
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loaded');
        });
    </script>
</body>
</html>