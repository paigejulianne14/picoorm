# PicoORM
PicoORM: a very lightweight ORM for PHP >=5.4.0

by Paige Julianne Sullivan <wiley14@gmail.com> https://paigejulianne.com


##Installation
Include `src/PicoORM.php` in your project files or use `composer require paigejulianne14/picoorm`

##Configuration
You will need to create a PDO connection and "share" it with PicoORM
through the `$GLOBAL['_PICO_PDO']` variable.

An example might be:
```
$GLOBAL['_PICO_PDO'] = new PDO("mysql:dbname=test;host=localhost", "dbuser", "dbpass");
```

See https://php.net/PDO for more information on PDO connection strings and parameters.

*You will need to create the PDO connection before you can use the class.*

##Create Your Classes
Create a class with the same name as your table and extend the *PicoORM* class.  For example, a wrapper around the `users`
table should be similar to the following:
```
class User extends \PicoORM {

}
``` 
For basic operations, you really don't need to do any more, as seen in the included
example `user.php` class.

You can also extend/modify any method in the class or add classes.  Example:
```
class User extends \PicoORM {
  
    public function __construct($id_value, $id_column = 'id') {
    	// do something special
    	parent::__construct($id_value, $id_column); 
    }
	
    public function customFunction($value, $data) {
    	// do something with $value and $data
    }  

}
```
Make sure you `include` or `require` your classes for them to function.

##Specifying Database
The code was designed to operate on a single database by default, but you can also specify the database
name by using a `namespace` statement before you instantiate your class.  Examples:

```
// create a class that uses the EndUsers database
namespace EndUsers;
class Users extends \PicoORM {
    ...
}

// create a class that uses the Vendors database
namespace Vendors;
class Users extends \PicoORM {
    ...
}
```

##Basic Functionality
Assuming you have a `User` class (even if it's empty), you can retrieve a single record with the value 1 in the 
`id` column through the following:

```
$user = new User(1);
```

If you use something other than the `id` column, pass the column name as the second parameter:

```
$user = new User('paige','userid');
```

You can now use setters and getters to retrieve and modify the data in the record/object.  The property name
matches up to a column name in the table:

```
// get the contents of the user_id column
$userId = $user->user_id;

// set the contents of the user_id column
$user->user_id = $newUserId;
```

If you want to delete the record from the database, you can simply call the delete method and unset the object:

```
$user->delete();
unset($user);
```

###getAllObjects

Example of getting all users (returned as an object array) with `permission` > 100, sorted by user_id:
```
$adminUsers = User::getAllObjects('id', [['permission','>',100]], 'user_id');
```

The parameters are as follows:
```
id_column       - the primary column used by construct to select the record
filter_array    - array of "filters" to be used in the SELECT statement
    - [0]       - column name
    - [1]       - any comparison operator supported by the database
    - [2]       - the data value to compare
filter_glue     - the "glue" to use between filters, defaults to AND
force_array     - return an array, even if a single result returned
```

A more advanced example might look like:
```
$adminUsers = User::getAllObjects('id', [['permission','>',100],['admin_user'],'=',1]],'OR', true);
```

###createNew
This lets you very quickly add new records to the database, using an array to set values
```
$newUser = User::createNew([
    'user_name' => 'paige',
    'first_name' => 'Paige',
    'last_name' => 'Sullivan'
]);
```

If the operation fails, you will get a *false* on return.  If it succeeds, you will get a `PDOStatement` object.

##Advanced Functions
If you already know SQL, need to use another database, or have some other custom functionality,
you can use the following methods:

```
class::_fetch($sql, $valueArray, $database)
class::_fetchAll($sql, $valueArray, $database)
class::_doQuery($sql, $valueArray, $database)
```
Where:
```
sql             - formatted or complete SQL statement to execute
                  if using formatted SQL, use the ? holder and data will be inserted in order
                  you can use the 'magic' string "_DB_" to insert the database and table name
valueArray      - array of data to be incorporated in the SQL statement
database        - if a database different than specified in the basedb configuration value
```

Examples (using User as the class name):
```
// gets the first user who's first name starts with the letter 'P'
User::_fetch('SELECT * FROM _DB_ WHERE SUBSTRING(first_name, 0, 1) = ?', ['P']);

// gets all users who's first name starts with the letter 'P'
User::_fetchAll('SELECT * FROM _DB_ WHERE SUBSTRING(first_name, 0, 1) = ?', ['P']);
```

In your custom SQL statement, you can use the "magic" string `_DB_` that will be properly replaced
with the database and table you are using as seen above.