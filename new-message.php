<?php include 'config.php'; ?>
<?php

// 生成5位随机信件ID
function generateMessageId() {
    return sprintf('%05d', rand(0, 99999));
}

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 连接数据库
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }

    // 获取表单数据
    $messageId = generateMessageId();
    $content = $_POST["content"];
    $burnAfterReading = isset($_POST["burn_after_reading"]) ? 1 : 0;
    $recipient = $burnAfterReading ? null : $_POST["recipient"];

    // 插入数据库
    $sql = "INSERT INTO messages (message_id, recipient, content, burn_after_reading, encryption_status) VALUES ('$messageId', '$recipient', '$content', $burnAfterReading, " . ($burnAfterReading ? 1 : 0) . ")";
    if ($conn->query($sql) === TRUE) {
        // 处理阅后即焚逻辑
        if ($burnAfterReading) {
            $link = "<?php echo $site_url; ?>/strong-encryption.php?messageid=$messageId";
            // 返回JSON格式数据给前端
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message_id' => $messageId]);
            exit;
        }
        $conn->close();
        // 跳转到成功页面
        header("Location: success.html");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
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
    <!-- 添加页面内弹窗结构 -->
    <div id="modalOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-button p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">确认提交</h3>
            <p class="text-gray-700 mb-6" id="modalMessage">确定要提交吗？请勿在信件中输入包含个人隐私、违反法律法规的内容。</p>
            <div class="flex justify-end space-x-3">
                <button id="modalCancel" class="px-4 py-2 border border-gray-300 rounded-button text-gray-700 hover:bg-gray-50">取消</button>
                <button id="modalConfirm" class="px-4 py-2 bg-secondary text-white rounded-button hover:bg-secondary/90">确定</button>
            </div>
        </div>
    </div>
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
            <form id="messageForm" class="space-y-4 mb-6" method="post" action="">
<div class="relative">
<input type="text" id="recipient" name="recipient" placeholder="你要留给......" class="w-full py-4 px-7 bg-white rounded-button text-md text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-primary/20">
</div>
<div class="relative">
<textarea id="content" name="content" placeholder="请输入信件内容" class="w-full py-4 px-7 bg-white rounded-button text-md text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-primary/20 min-h-[120px] resize-none"></textarea>
</div>
<div class="flex items-center justify-between py-4 px-4 bg-white rounded-button mb-4">
<span class="text-gray-700">为本信开启阅后即焚</span>
<label class="relative inline-flex items-center cursor-pointer">
<input type="checkbox" id="burnAfterReading" name="burn_after_reading" class="sr-only peer">
<div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary"></div>
</label>
</div>
<p class="skip-link text-sm text-gray-600 hover:text-gray-600">* 阅后即焚：开启此功能后，提交留信将生成一个链接，查看一次后自动永久删除。开启本功能不再支持指定收信人。</p>
<button type="submit" class="login-button w-full py-4 px-4 bg-secondary text-white rounded-button flex items-center justify-center space-x-2 whitespace-nowrap text-lg">
<span>提交</span>
</button>
</form>
</div>
<script>
    // 获取模态框元素
    const modalOverlay = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalCancel = document.getElementById('modalCancel');
    const messageForm = document.getElementById('messageForm');

    // 显示模态框函数
    function showModal(title, message, confirmCallback) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modalOverlay.classList.remove('hidden');

        // 确认按钮事件
        const confirmHandler = () => {
            confirmCallback();
            modalOverlay.classList.add('hidden');
            modalConfirm.removeEventListener('click', confirmHandler);
        };

        // 取消按钮事件
        const cancelHandler = () => {
            modalOverlay.classList.add('hidden');
            modalCancel.removeEventListener('click', cancelHandler);
        };

        modalConfirm.addEventListener('click', confirmHandler);
        modalCancel.addEventListener('click', cancelHandler);
    }

    // 阅后即焚功能逻辑
    document.getElementById('burnAfterReading').addEventListener('change', function(e) {
    const recipientInput = document.getElementById('recipient');
    if (e.target.checked) {
        recipientInput.disabled = true;
        recipientInput.placeholder = "阅后即焚模式不支持指定收信人";
    } else {
        recipientInput.disabled = false;
        recipientInput.placeholder = "你要留给......";
    }
});

// 表单提交确认
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // 显示自定义确认弹窗
    showModal('确认提交', '确定要提交吗？请勿在信件中输入包含个人隐私、违反法律法规的内容。', () => {
        const burnAfterReading = document.getElementById('burnAfterReading').checked;
        if (burnAfterReading) {
            // 使用AJAX提交表单获取真实ID
            const formData = new FormData(this);
            fetch('', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const link = `<?php echo $site_url; ?>/strong-encryption.php?messageid=${data.message_id}`;
                    navigator.clipboard.writeText(link).then(() => {
                        showModal('操作成功', '链接已复制，请妥善保管。', () => {
                            window.location.href = '/success.html';
                        });
                    });
                }
            });
        } else {
            this.submit();
        }
    });
});
</script>
</body>
</html>