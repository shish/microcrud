<?php

declare(strict_types=1);

namespace MicroCRUD;

class InetColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        $driver = $this->table->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        switch ($driver) {
            case "pgsql":
                return "({$this->name} && cast(:{$this->name} as inet))";
            default:
                return "({$this->name} = :{$this->name})";
        }
    }
}
