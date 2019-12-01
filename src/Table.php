<?php
namespace MicroCRUD;

use \FFSPHP\PDO;

use MicroHTML\HTMLElement;
use function MicroHTML\TABLE;
use function MicroHTML\THEAD;
use function MicroHTML\TBODY;
use function MicroHTML\TFOOT;
use function MicroHTML\TR;
use function MicroHTML\TH;
use function MicroHTML\TD;
use function MicroHTML\INPUT;
use function MicroHTML\FORM;
use function MicroHTML\DIV;
use function MicroHTML\A;

class Table
{
    public $table = null;
    public $base_query = null;
    public $size = 100;
    public $limit = 1000;
    public $columns = [];
    public $order_by = [];
    public $flags = [];
    public $db = null;
    public $primary_key = "id";
    public $table_attrs = [];

    public $create_url = null;
    public $update_url = null;
    public $delete_url = null;
    public $token = null;
    
    public $inputs = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // args
    public function size(): ?int
    {
        // admin can set $table->size = null if they want all results
        if (is_null($this->size)) {
            return null;
        }

        $size = !empty($this->inputs["r__size"]) ? (int)$this->inputs["r__size"] : $this->size;
        if ($size > $this->limit) {
            $size = $this->limit;
        }
        return $size;
    }

    // database
    public function get_filter(): array
    {
        $filters = [];
        $args = [];
        foreach ($this->columns as $col) {
            $val = @$this->inputs["r_{$col->name}"];
            // convert an array of empty strings into an empty value
            if (is_array($val)) {
                $all_empty = true;
                foreach ($val as $v) {
                    if ($v) {
                        $all_empty = false;
                    }
                }
                if ($all_empty) {
                    $val = null;
                }
            }
            if (!empty($val)) {
                $filters[] = $col->filter;
                if ($col->input_mod) {
                    $val = ($col->input_mod)($val);
                }
                if (!is_array($val)) {
                    $args[$col->name] = $val;
                } else { // array
                    foreach ($val as $k => $v) {
                        $args["{$col->name}_$k"] = $v;
                    }
                }
            }
        }
        foreach ($this->flags as $flag => $filter) {
            if (!empty($this->inputs["r_{$flag}"])) {
                if ($filter[1]) {
                    $filters[] = $filter[1];
                }
            } else {
                if ($filter[0]) {
                    $filters[] = $filter[0];
                }
            }
        }
        if (empty($filters)) {
            $filters[] = "(1=1)";
        }
        return [implode(" AND ", $filters), $args];
    }

    public function query(): array
    {
        // WHERE
        list($filter, $args) = $this->get_filter();

        // ORDER BY
        $order_by = "";
        if (!empty($this->inputs["r__sort"])) {
            $asc = true;
            $suggested_order = $this->inputs["r__sort"];
            if ($suggested_order[0] == "-") {
                $asc = false;
                $suggested_order = substr($suggested_order, 1);
            }
            foreach ($this->columns as $col) {
                if ($col->name == $suggested_order) {
                    $order_by = "ORDER BY " . $col->name . ($asc ? " ASC" : " DESC");
                    break;
                }
            }
        }
        if (empty($order_by) && !empty($this->order_by)) {
            $order_by = "ORDER BY " . join(", ", $this->order_by);
        }

        // LIMIT / OFFSET
        $page = !empty($this->inputs["r__page"]) ? (int)$this->inputs["r__page"] : 1;
        $size = $this->size();
        $pager = "";
        if (!is_null($size)) {
            $pager = "LIMIT :limit OFFSET :offset";
            $args["offset"] = $size * ($page-1);
            $args["limit"] = $size;
        }

        $query = "
			{$this->base_query}
			WHERE {$filter}
			$order_by
			$pager
        ";

        return $this->db->execute($query, $args)->fetchAll();
    }

    public function count(): int
    {
        list($filter, $args) = $this->get_filter();

        $query = "
			SELECT COUNT(*) FROM (
				{$this->base_query}
				WHERE {$filter}
			) AS tbl2
        ";

        return $this->db->execute($query, $args)->fetch()[0];
    }

    public function count_pages(): int
    {
        return $this->count() / $this->size();
    }

    // html generation
    public function table(array $rows): HTMLElement
    {
        return TABLE(
            $this->table_attrs,
            "\n",
            $this->thead(),
            "\n",
            $this->tbody($rows),
            "\n",
            $this->tfoot(),
            "\n"
        );
    }

    public function thead(): HTMLElement
    {
        $thead = THEAD(["id"=>"read"]);

        $tr = TR();
        foreach ($this->columns as $col) {
            $sort_name = (@$this->inputs["r__sort"] == $col->name) ? "-{$col->name}" : $col->name;
            $sort = "?" . $this->modify_url(["r__sort"=>$sort_name]);
            $tr->appendChild(TH(A(["href"=>$sort], $col->title)));
        }
        $tr->appendChild(TH("Action"));
        $thead->appendChild($tr);

        $tr = TR();
        foreach ($this->columns as $col) {
            $tr->appendChild(TD($col->read_input($this->inputs)));
        }
        $tr->appendChild(TD(
            INPUT(["type"=>"hidden", "name"=>"r__size", "value"=>@$this->inputs["r__size"]]),
            INPUT(["type"=>"hidden", "name"=>"r__page", "value"=>@$this->inputs["r__page"]]),
            INPUT(["type"=>"submit", "value"=>"Search"])
        ));
        foreach ($this->flags as $flag => $_vals) {
            $tr->appendChild(
                INPUT(["type"=>"hidden", "name"=>"r_{$flag}", "value"=>@$this->inputs["r_{$flag}"]])
            );
        }
        $thead->appendChild(FORM($tr));

        return $thead;
    }

    public function tbody(array $rows): HTMLElement
    {
        $tbody = TBODY(["id"=>"update"]);
        foreach ($rows as $row) {
            $tr = TR();
            $tbody->appendChild($tr);
            foreach ($this->columns as $col) {
                $tr->appendChild(TD($col->display($row)));
            }
            if ($this->delete_url) {
                $tr->appendChild(TD(FORM(
                    ["method"=>"POST", "action"=>$this->delete_url],
                    INPUT(["type"=>"hidden", "name"=>"auth_token", "value"=>$this->token]),
                    INPUT(["type"=>"hidden", "name"=>"d_{$this->primary_key}", "value"=>$row[$this->primary_key]]),
                    INPUT(["type"=>"submit", "value"=>"Delete"])
                )));
            }
        }
        return $tbody;
    }

    public function tfoot(): HTMLElement
    {
        $tfoot = TFOOT(["id"=>"create"]);
        if ($this->create_url) {
            $tr = TR();
            $tfoot->appendChild(FORM(["method"=>"POST", 'action'=>$this->create_url], $tr));
            foreach ($this->columns as $col) {
                $tr->appendChild(TD($col->create_input($this->inputs)));
            }
            $tr->appendChild(TD(
                INPUT(["type"=>"hidden", "name"=>"auth_token", "value"=>$this->token]),
                INPUT(["type"=>"submit", "value"=>"Add"])
            ));
        }
        return $tfoot;
    }

    public function modify_url(array $changes): string
    {
        $args_copy = $this->inputs;
        foreach ($changes as $k => $v) {
            $args_copy[$k] = $v;
        }
        return http_build_query($args_copy);
    }

    public function page_url(int $page): string
    {
        return "?" . $this->modify_url(["r__page"=>$page]);
    }

    public function paginator(): HTMLElement
    {
        $min = 1;
        $max = $this->count_pages();
        $d = DIV();
        $d->appendChild(A(["href"=>$this->page_url($min)], "First"));
        $d->appendChild(" | ");
        foreach (range($min, $max+1) as $p) {
            $d->appendChild(A(["href"=>$this->page_url($p)], "$p"));
            $d->appendChild(" | ");
        }
        $d->appendChild(A(["href"=>$this->page_url($max)], "Last"));
        return $d;
    }
}
