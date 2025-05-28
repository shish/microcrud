<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/model.php";

$db = create_mock_db();
$t = new IPBanTable($db);
$t->inputs = $_GET;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MicroCRUD Example</title>
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
}
input:not([type="checkbox"]):not([type="radio"]),
button,
select {
    width: 100%;
    box-sizing: border-box;
}
    </style>
</head>
<body>
<?php
print($t->table($t->query()));
print($t->paginator());
?>
</body>
</html>
