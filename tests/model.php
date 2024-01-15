<?php

declare(strict_types=1);

use MicroCRUD\ActionColumn;
use MicroCRUD\InetColumn;
use MicroCRUD\IntegerColumn;
use MicroCRUD\StringColumn;
use MicroCRUD\DateColumn;
use MicroCRUD\DateTimeColumn;
use MicroCRUD\TextColumn;
use MicroCRUD\EnumColumn;
use MicroCRUD\Column;
use MicroCRUD\Table;
use FFSPHP\PDO;

use function MicroHTML\A;

class CustomColumn extends Column
{
    public function __construct()
    {
        parent::__construct("user_or_ip", "Author");
    }

    public function get_sql_filter(): string
    {
        return "((ip=:user_or_ip_1) OR (banner=:user_or_ip_0))";
    }

    public function modify_input_for_read(string|array $input): mixed
    {
        assert(is_string($input));
        $user = $input;
        if (preg_match("/[0-9.]+/", $input)) {
            $ip = $input;
        } else {
            $ip = null;
        }
        return [$user, $ip];
    }

    public function display(array $row): \MicroHTML\HTMLElement|string
    {
        if ($row['ip'] == "1.2.3.4") {
            return A(["href" => "/users/edwin"], "Edwin");
        } else {
            return $row['ip'];
        }
    }
}

class IPBanTable extends Table
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);

        $this->table = "bans";
        $this->base_query = "
			SELECT * FROM (
				SELECT bans.*, users.name AS banner
				FROM bans JOIN users ON banner_id=users.id
			) AS tbl1
		";
        $this->size = 10;
        $this->limit = 20;
        $this->set_columns([
            new InetColumn("ip", "IP"),
            new EnumColumn("mode", "Mode", ["Block" => "block", "Firewall" => "firewall", "Read-only" => "readonly"]),
            new TextColumn("reason", "Reason"),
            new StringColumn("banner", "Banner"),
            new IntegerColumn("banner_id", "Banner ID"),
            new DateColumn("added", "Added"),
            new DateTimeColumn("expires", "Expires"),
            new CustomColumn(),
            new ActionColumn("id"),
        ]);
        # MySQL / SQLite don't support "NULLS LAST" :(
        $this->order_by = ["CASE WHEN expires IS NULL THEN 0 ELSE 1 END", "expires", "id"];
        $this->flags = [
            "all" => ["((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", null],
        ];
        $this->create_url = "/ip_ban/create";
        $this->delete_url = "/ip_ban/remove";
    }
}

function create_mock_db(): PDO
{
    $e = getenv('DSN');
    $dsn = $e ? $e : 'sqlite::memory:';
    $db = new PDO($dsn, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    # FFS.
    $dbe = explode(":", $dsn)[0];
    $aipk = [
        "pgsql" => "INTEGER PRIMARY KEY NOT NULL GENERATED ALWAYS AS IDENTITY",
        "sqlite" => "INTEGER PRIMARY KEY AUTOINCREMENT",
        "mysql" => "INTEGER PRIMARY KEY AUTO_INCREMENT"
    ][$dbe];

    $db->exec("DROP TABLE IF EXISTS users");
    $db->exec("CREATE TABLE users (
		id $aipk,
		name varchar(250) NOT NULL
	);");
    $db->exec("INSERT INTO users(name) VALUES ('Alice');");
    $db->exec("INSERT INTO users(name) VALUES ('Bob');");
    $db->exec("INSERT INTO users(name) VALUES ('Charlie');");
    $db->exec("INSERT INTO users(name) VALUES ('Davina');");

    $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
    switch ($driver) {
        case "pgsql": $inet = "INET";
            break;
        default: $inet = "varchar(250)";
            break;
    }

    $db->exec("DROP TABLE IF EXISTS bans");
    $db->exec("CREATE TABLE bans (
		id $aipk,
		ip $inet NOT NULL,
		mode varchar(250) NOT NULL DEFAULT 'block',
		reason text NOT NULL,
		banner_id integer NOT NULL,
		added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		expires timestamp NULL DEFAULT NULL
	);");

    $n = 1;
    foreach (['block', 'firewall', 'readonly'] as $mode) {
        foreach (['leech', 'spam', 'offtopic'] as $reason) {
            foreach ([1, 2, 3] as $banner_id) {
                foreach (["'1980-01-01 12:34'", "'1990-01-01 12:45'", "'2000-01-01 12:56'"] as $added) {
                    foreach (['NULL', "'2010-01-01'", "'2030-01-01'"] as $expires) {
                        $q = "
							INSERT INTO bans(ip, mode, reason, banner_id, added, expires)
							VALUES ('1.2.3.$n', '$mode', '$reason', $banner_id, $added, $expires);
						";
                        $db->exec($q);
                        $n++;
                    }
                }
            }
        }
    }

    return $db;
}
