MicroCRUD
=========
A library that you can point at a database table, and it'll give you a bunch
of HTML forms for create / read / update / delete actions.

I kept re-implementing (badly) a bunch of code to do this stuff. Instead of
implementing it badly a 10th time, I decided to put it into a self-contained
library with the union of features.

Currently this uses FFS-PHP's PDO, but it wouldn't be too hard to use vanilla
PDO if anybody requested that feature.

```php
use \MicroCRUD\{Table, TextColumn, ActionColumn};

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

```html
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

Notes for anything that might be non-obvious
--------------------------------------------
The InetColumn type supports exact matching for most databases, but with
Postgres, it supports range matching (eg searching for "1.2.3.0/24" will
return search results including "1.2.3.6"). It'd be nice to support this
cross-database, but pragmatically, one person has asked for this feature,
they are using postgres as a backend, and postgres makes this trivial :P
