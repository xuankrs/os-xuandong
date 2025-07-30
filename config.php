<?php
// 数据库配置
$servername = "localhost";
$username = "zm";
$password = "eHrFwCpnF3S45z32";
$dbname = "zm";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 获取站点配置
$site_config = [];
$sql = "SELECT * FROM vdinformation LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $site_config = $result->fetch_assoc();
}

$site_title = isset($site_config['site_title']) ? $site_config['site_title'] : '旋动密信';
$site_description = isset($site_config['site_description']) ? $site_config['site_description'] : '加密信件系统';
$site_url = isset($site_config['site_url']) ? $site_config['site_url'] : '';
?>