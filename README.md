MicroCRUD
=========
I kept re-implementing (badly) a bunch of code to do this stuff. Instead of
implementing it badly a 10th time, I decided to put it into a self-contained
library with the union of features.

Currently this uses FFS-PHP's PDO, but it wouldn't be too hard to use vanilla
PDO if anybody requested that feature.

```
use \MicroCRUD\{Table, ActionColumn, TextColumn};

class MyTable extends Table {
    public function __construct($db) {
        parent::__construct($db);
        $this->set_columns([
            TextColumn("username", "Username"),
            TextColumn("email", "Email Address"),
            ActionColumn()
        ]);
    }
}

$t = MyTable($db);
print($t->table());
```

```
<table>
	<thead>
		[... titles and search fields ...]
	</thead>
	<tbody>
		[... contents of the table, in pages ...]
	</tbody>
	<tfoot>
		[... code to add a new entry ...]
	</tfoot>
</table>
```
