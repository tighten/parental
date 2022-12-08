![Parental - Use single table inheritance in your Laravel App](parental-banner.png)

# Parental

Parental is a Laravel package that brings STI (Single Table Inheritance) capabilities to Eloquent.

### What is single table inheritance (STI)?

It's a fancy name for a simple concept: Extending a model (usually to add specific behavior), but referencing the same table.

## Installation

```bash
composer require tightenco/parental
```

## Simple Usage

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

// The "parent"
class User extends Model
{
    use HasChildren;
    //
}
```

```php
namespace App\Models;

use Parental\HasParent;

// The "child"
class Admin extends User
{
    use HasParent;

    public function impersonate($user) {
        //...
    }
}
```

```php
use App\Models\Admin;

// Returns "Admin" model, but reference "users" table:
$admin = Admin::first();

// Can now access behavior exclusive to "Admin"s
$admin->impersonate($user);
```

### What problem did we just solve?
Without Parental, calling `Admin::first()` would throw an error because Laravel would be looking for an `admins` table. Laravel generates expected table names, as well as foreign keys and pivot table names, using the model's class name. By adding the `HasParent` trait to the Admin model, Laravel will now reference the parent model's class name `users`.

## Accessing Child Models from Parents

```php
// First, we need to create a `type` column on the `users` table
Schema::table('users', function ($table) {
    $table->string('type')->nullable();
});
```

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

// The "parent"
class User extends Model
{
    use HasChildren;

    protected $fillable = ['type'];
}
```

```php
namespace App\Models;

use Parental\HasParent;

// A "child"
class Admin extends User
{
    use HasParent;
}
```

```php
namespace App\Models;

use Parental\HasParent;

// Another "child"
class Guest extends User
{
    use HasParent;
}
```


```php
use App\Models\Admin;
use App\Models\Guest;
use App\Models\User;

// Adds row to "users" table with "type" column set to: "App/Admin"
Admin::create(...);

// Adds row to "users" table with "type" column set to: "App/Guest"
Guest::create(...);

// Returns 2 model instances: Admin, and Guest
User::all();
```

### What problem did we just solve?
Before, if we ran: `User::first()` we would only get back `User` models. By adding the `HasChildren` trait and a `type` column to the `users` table, running `User::first()` will return an instance of the child model (`Admin` or `Guest` in this case).

## Type Aliases
If you don't want to store raw class names in the type column, you can override them using the `$childTypes` property.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

class User extends Model
{
    use HasChildren;

    protected $fillable = ['type'];

    protected $childTypes = [
        'admin' => Admin::class,
        'guest' => Guest::class,
    ];
}
```

Now, running `Admin::create()` will set the `type` column in the `users` table to `admin` instead of `App\Admin`.

This feature is useful if you are working with an existing type column, or if you want to decouple application details from your database.

## Custom Type Column Name
You can override the default type column by setting the `$childColumn` property on the parent model.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

class User extends Model
{
    use HasChildren;

    protected $fillable = ['parental_type'];

    protected $childColumn = 'parental_type';
}
```

## Laravel Nova Support
If you want to use share parent Nova resources with child models, you may register the following provider at the end of the boot method of your NovaServiceProvider:

```php
class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot() {
        parent::boot();
        // ...
        $this->app->register(\Parental\Providers\NovaResourceProvider::class);
    }
}
```

---

Thanks to [@sschoger](https://twitter.com/steveschoger) for the sick logo design, and [@DanielCoulbourne](https://twitter.com/DCoulbourne) for helping brainstorm the idea on [Twenty Percent Time](http://twentypercent.fm/).
