<?php include 'config.php'; ?>
<?php

// 检查是否提供了messageid参数
if (!isset($_GET['messageid']) || empty($_GET['messageid'])) {
    header("Location: new-message.php");
    exit;
}

$messageid = $_GET['messageid'];

// 验证messageid格式（5位数字）
if (!preg_match('/^\d{5}$/', $messageid)) {
    header("Location: not.html");
    exit;
}

// 连接数据库
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 查询信件信息
$stmt = $conn->prepare("SELECT * FROM messages WHERE message_id = ?");
$stmt->bind_param("s", $messageid);
$stmt->execute();
$result = $stmt->get_result();

// 检查信件是否存在
if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    header("Location: not.html");
    exit;
}

$message = $result->fetch_assoc();
$stmt->close();

// 检查加密状态
if ($message['encryption_status'] == 0) {
    $conn->close();
    header("Location: view-message.php");
    exit;
}

// 检查阅读状态
if ($message['read_status'] == 1) {
    $conn->close();
    header("Location: not.html");
    exit;
}

// 更新阅读状态为已读
$updateStmt = $conn->prepare("UPDATE messages SET read_status = 1 WHERE message_id = ?");
$updateStmt->bind_param("s", $messageid);
$updateStmt->execute();
$updateStmt->close();
$conn->close();
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
<body class="flex flex-col min-h-screen px-4 py-6">
    <header class="header px-4 py-3">
        <div class="flex items-center justify-between max-w-[420px] mx-auto">
            <a>
            </a>
            <div class="text-lg font-medium text-gray-700">&nbsp;<?php echo $site_title; ?></div>
            <div class="w-10"></div>
        </div>
    </header>
    <div class="scrollable-container max-w-[420px] mx-auto w-full">
        <br><br><br>
        <p class="text-default text-gray-500">留言内容</p>
        <p class="text-sm text-gray-500" style="color:#000000"><?php echo htmlspecialchars($message['content']); ?></p><br>
        <p class="text-default text-gray-500">留言时间</p>
        <p class="text-sm text-gray-500" style="color:#000000"><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></p>
    </div>
</body>
</html>