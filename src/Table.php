<?php
namespace MicroCRUD;

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

    public $create_url = null;
    public $update_url = null;
    public $delete_url = null;
    public $token = null;

    public function __construct(\PDO $db, $token=null)
    {
        $this->db = $db;
        $this->token = $token;
    }

    // args
    public function size()
    {
        $size = !empty($_GET["s__size"]) ? (int)$_GET["s__size"] : $this->size;
        if ($size > $this->limit) {
            $size = $this->limit;
        }
        return $size;
    }

    // database
    public function get_filter()
    {
        $filters = ["1=1"];
        $args = [];
        foreach ($this->columns as $col) {
            if (!empty($_GET["s_{$col->name}"])) {
                $filters[] = $col->filter;
                $val = $_GET["s_{$col->name}"];
                if ($col->input_mod) {
                    $val = ($col->input_mod)($val);
                }
                $args[$col->name] = $val;
            }
        }
        foreach ($this->flags as $flag => $filter) {
            if (!empty($_GET["s_{$flag}"])) {
                if ($filter[1]) {
                    $filters[] = $filter[1];
                }
            } else {
                if ($filter[0]) {
                    $filters[] = $filter[0];
                }
            }
        }
        return [implode(" AND ", $filters), $args];
    }

    public function query()
    {
        list($filter, $args) = $this->get_filter();

        $page = !empty($_GET["s__page"]) ? (int)$_GET["s__page"] : 1;
        $order = !empty($this->order_by) ? "ORDER BY " . join(", ", $this->order_by) : "";
        $size = $this->size();

        $query = "
			{$this->base_query}
			WHERE {$filter}
			$order
			LIMIT :limit
			OFFSET :offset
        ";
        $args["offset"] = $size * ($page-1);
        $args["limit"] = $size;

        $stmt = $this->db->prepare($query);
        $stmt->execute($args);
        $rows = $stmt->fetchAll();

        return $rows;
    }

    public function count()
    {
        list($filter, $args) = $this->get_filter();

        $query = "
			SELECT COUNT(*) FROM (
				{$this->base_query}
				WHERE {$filter}
			)
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute($args);
        $row = $stmt->fetch();

        return $row[0];
    }

    public function count_pages(): int
    {
        return $this->count() / $this->size();
    }

    // html generation
    public function table(array $rows): HTMLElement
    {
        return TABLE(
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
        $thead = THEAD();

        $tr = TR();
        foreach ($this->columns as $col) {
            $tr->appendChild(TH($col->title));
        }
        $tr->appendChild(TH("Action"));
        $thead->appendChild($tr);

        $tr = TR();
        foreach ($this->columns as $col) {
            $tr->appendChild(TH($col->read_input()));
        }
        $tr->appendChild(TH(
            INPUT(["type"=>"hidden", "name"=>"r__size", "value"=>@$_GET["r__size"]]),
            INPUT(["type"=>"hidden", "name"=>"r__page", "value"=>@$_GET["r__page"]]),
            INPUT(["type"=>"submit", "value"=>"Search"])
        ));
        foreach ($this->flags as $flag => $_vals) {
            $tr->appendChild(
                INPUT(["type"=>"hidden", "name"=>"r_{$flag}", "value"=>@$_GET["r_{$flag}"]])
            );
        }
        $thead->appendChild(FORM($tr));

        return $thead;
    }

    public function tbody(array $rows): HTMLElement
    {
        $tbody = TBODY();
        foreach ($rows as $row) {
            $tr = TR();
            $tbody->appendChild($tr);
            foreach ($this->columns as $col) {
                $tr->appendChild(TD($col->display($row[$col->name])));
            }
            if ($this->delete_url) {
                $tr->appendChild(TD(FORM(
                    ["method"=>"POST", "action"=>$this->delete_url],
                    INPUT(["type"=>"hidden", "name"=>"auth_token", "value"=>$this->token]),
                    INPUT(["type"=>"hidden", "name"=>"d_id", "value"=>$row["id"]]),
                    INPUT(["type"=>"submit", "value"=>"Delete"])
                )));
            }
        }
        return $tbody;
    }

    public function tfoot(): HTMLElement
    {
        $tfoot = TFOOT();
        if ($this->create_url) {
            $tr = TR();
            $tfoot->appendChild(FORM(["method"=>"POST", 'action'=>$this->create_url], $tr));
            foreach ($this->columns as $col) {
                $tr->appendChild(TH($col->create_input()));
            }
            $tr->appendChild(TH(
                INPUT(["type"=>"hidden", "name"=>"auth_token", "value"=>$this->token]),
                INPUT(["type"=>"submit", "value"=>"Add"])
            ));
        }
        return $tfoot;
    }

    public function modify_url(array $changes): string
    {
        foreach ($changes as $k => $v) {
            $_GET[$k] = $v;
        }
        return http_build_query($_GET);
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
