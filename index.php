<?php include 'config.php'; ?>
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

    <div class="flex-1 flex flex-col items-center justify-center">
        <h1 class="text-3xl font-medium text-gray-700 mb-2">欢迎使用<?php echo $site_title; ?></h1>
        <p class="text-base text-gray-500 text-center"><?php echo $site_description; ?></p>
    </div>

    <div class="w-full max-w-[420px] mx-auto">
        <div class="space-y-4 mb-6">
            <button class="login-button w-full py-4 px-4 bg-secondary text-white rounded-button flex items-center justify-center space-x-2 whitespace-nowrap text-lg" onclick="window.location.href='new-message.php'">
                <span>新建留言</span>
            </button>
            <button class="login-button w-full py-4 px-4 bg-primary text-white rounded-button flex items-center justify-center space-x-2 whitespace-nowrap text-lg" onclick="window.location.href='view-message.php'">
                <span style="color:#000000">查找留言</span>
            </button>
        </div>
    </div>
</body>

</html>