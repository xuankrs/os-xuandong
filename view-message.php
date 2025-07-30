<?php include 'config.php'; ?>
<?php

$messages = [];
$recipient = '';
$showNoResults = false;

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["recipient"])) {
    $recipient = trim($_POST["recipient"]);
    if (!empty($recipient)) {
        // 连接数据库
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("连接失败: " . $conn->connect_error);
        }

        // 查询信件
        $stmt = $conn->prepare("SELECT message_id, recipient, content, created_at FROM messages WHERE recipient = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $recipient);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // 获取所有信件
            while($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
        } else {
            $showNoResults = true;
        }

        $stmt->close();
        $conn->close();
    } else {
        $showNoResults = true;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $site_title; ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="assets/tailwind.config.js"></script>
<link rel="stylesheet" type="text/css" href="assets/style.css">
</head>
<body class="flex flex-col justify-between min-h-screen px-8 py-12">
    <header class="header px-4 py-3">
        <div class="flex items-center justify-between max-w-[420px] mx-auto">
            <a href="index.php" class="back-button p-2 -ml-2 rounded-full hover:bg-gray-100 active:bg-gray-200">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="text-lg font-medium text-gray-700">&nbsp;查找留言</div>
            <div class="w-10"></div>
        </div>
    </header>
    <div class="flex-1 flex flex-col items-center justify-center">
        <div class="w-full max-w-[420px] mx-auto">
            <?php if (empty($messages)): ?>
            <form id="messageForm" class="space-y-4 mb-6" method="post" action="">
                <div class="relative">
                    <input type="text" id="recipient" name="recipient" placeholder="按收信人姓名查找" class="w-full py-4 px-7 bg-white rounded-button text-md text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-primary/20" value="<?php echo htmlspecialchars($recipient); ?>">
                </div>
                <p class="skip-link text-sm text-gray-600 hover:text-gray-600" style="display: <?php echo $showNoResults ? 'block' : 'none'; ?>">未找到此姓名的信件。</p>
                <button type="submit" class="login-button w-full py-4 px-4 bg-secondary text-white rounded-button flex items-center justify-center space-x-2 whitespace-nowrap text-lg">
                    <span>执行</span>
                </button>
            </form>
            <?php endif; ?>

            <?php if (!empty($messages)): ?>
            <div class="space-y-4">
                <h1 class="text-xl font-medium text-gray-900">查询结果 (共 <?php echo count($messages); ?> 封)</h1>
                <?php foreach ($messages as $message): ?>
                <div class="article-card bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($message['content']); ?></p>
                    <div class="flex items-center gap-4 mt-3">
                        <span class="text-xs text-gray-400">收信人: <?php echo htmlspecialchars($message['recipient']); ?></span>
                        <span class="text-xs text-gray-400">时间: <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>