<?php
namespace MicroCRUD;

class InetColumn extends Column
{
    public function get_sql_filter(): string
    {
        $driver = $this->table->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        switch ($driver) {
            case "pgsql":
                return "({$this->name} && inet :{$this->name})";
            default:
                return "({$this->name} = :{$this->name})";
        }
    }
}
