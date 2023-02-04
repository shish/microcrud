<?php

declare(strict_types=1);

require_once "vendor/autoload.php";
require_once "model.php";

$db = create_mock_db();
$t = new IPBanTable($db);
$t->inputs = $_GET;
print($t->table($t->query()));
print($t->paginator());
