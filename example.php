<?php

require_once "vendor/autoload.php";
require_once "tests/model.php";

$db = create_mock_db();
$t = new IPBanTable($db);
$t->inputs = $_GET;
print($t->table($t->query()));
print($t->paginator());
