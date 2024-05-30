<?php
$host = 'localhost';
$db = 'u2668243_test_bd';
$user = 'u2668243_test_bd';
$pass = 'test_bd';

$conn = new mysqli($host, $user, $pass, $db);
mysqli_set_charset($conn, "utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getProductCount($groupId, $conn) {
    $count = 0;
    
    $stmt = $conn->prepare("SELECT id FROM groups WHERE id_parent = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $count += getProductCount($row['id'], $conn);
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE id_group = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count += $row['count'];
    
    return $count;
}
function getGroups($parentId, $conn) {
    $groups = [];
    $stmt = $conn->prepare("SELECT id, name FROM groups WHERE id_parent = ?");
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }

    return $groups;
}
function renderGroups($parentId, $conn) {
    $groups = getGroups($parentId, $conn);

    echo "<ul>";
    foreach ($groups as $group) {
        $count = getProductCount($group['id'], $conn);
        echo "<li><a href='?group={$group['id']}'>{$group['name']} ({$count})</a></li>";
    }
    echo "</ul>";
}

function renderProducts($groupId, $conn) {
    $stmt = $conn->prepare("
        SELECT products.id, products.name FROM products 
        JOIN groups ON products.id_group = groups.id 
        WHERE groups.id = ? OR groups.id_parent = ?
    ");
    $stmt->bind_param("ii", $groupId, $groupId);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['name']}</li>";
    }
    echo "</ul>";
}

if (isset($_GET['group'])) {
    $groupId = intval($_GET['group']);
} else {
    $groupId = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Groups</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Groups</h1>
    <?php renderGroups(0, $conn); ?>

    <?php if ($groupId > 0): ?>
        <h2>Subgroups and Products</h2>
        <?php renderGroups($groupId, $conn); ?>
        <?php renderProducts($groupId, $conn); ?>
    <?php else: ?>
        <h2>All Products</h2>
        <?php renderProducts(0, $conn); ?>
    <?php endif; ?>

</body>
</html>