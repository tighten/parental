![Parental - Use single table inheritance in your Laravel App](parental-banner.png)

# Parental

Parental is an early-stage development, alpha Laravel package by Tighten that brings STI (Single Table Inheritance) capabilities to Eloquent.

## Installation

```bash
composer require "tightenco/parental=0.4-alpha"
```

## Simple Usage

1. Create "child" model: ex. `Admin.php`
1. Extend "parent" model: ex. `User.php`
1. Add `HasParentModel` trait to `Admin.php`

```php
use Tightenco\Parental\HasParentModel;

class Admin extends User
{
    use HasParentModel;
}
```

```php
// Returns "Admin" model, but reference "users" table:
Admin::first();
```

### What's happening?
Laravel performs some internal magic to derive the table name and field names from each model's class name. For example, calling `Admin::first()`, even though Admin extends User, would throw an error because it would be looking for an `admins` table. Fortunately, you can manually set a `protected $table = 'users'` property on the Admin model to override this behavior. Unfortunately, it's difficult to do something similar for foreign key names, and pivot column / table names.

By adding the `HasParentModel` class to your Admin model, all the hard work is done automatically. `Admin::first()` will look in the parent User model's table, but return the proper Admin model.

## Next-level Usage

1. Create "child/children" models: ex. `Admin.php`, `Manager.php`
1. Extend "parent" model: ex. `User.php`
1. Add `type` column to `users` table
1. Add `HasParentModel` trait to `Admin.php`, `Manager.php`
1. Add `ReturnsChildModels` trait to `User.php`

```php
use Tightenco\Parental\HasParentModel;

class Admin extends User
{
    use HasParentModel;
}
```

```php
use Tightenco\Parental\HasParentModel;

class Manager extends User
{
    use HasParentModel;
}
```

```php
use Tightenco\Parental\ReturnsChildModels;

class User extends Model
{
    use ReturnsChildModels;
}
```

```php
// In users table migration:
$table->string('type')->nullable();
```

```php
// Adds row to "users" table with "type" column set to: "App/Admin"
Admin::create(...);

// Adds row to "users" table with "type" column set to: "App/Manager"
Manager::create(...);

// Returns 2 model instances: Admin, and Manager
User::all();
```

### What's happening?
Before, when we just added the `HasParentModel`, we got half-way there. That enabled us to find, retreive, and use the `Admin` model, instead of the `User` model. However, this is only one side of the equation. Before, if we ran: `User::first()` we would only get back `User` models. By simply adding the `ReturnsChildModels` to the `User` model, now running `User::first()` will return an instance of whatever model, that `User` is supposed to be. To accomplish this sort polymorphic behavior, we need to use a `type` column on the `users` table to keep track of what model instance to return from User queries.

## Type Aliases
If for some reason you don't want to store raw class names in the type column, you can override them using the `$childTypeAliases` property.

```php
use Tightenco\Parental\ReturnsChildModels;

class User extends Model
{
    use ReturnsChildModels;

    protected $childTypeAliases = [
        'admin' => App\Admin::class,
    ];
}
```

Now, running `Admin::create()` will set the `type` column in the `users` table to `admin` instead of `App\Admin`.

This feature is useful for those who are already using a type column with a different naming scheme, or those who don't want to store application classes directly in the database.

## Custom Type Column
You can override the default type column by setting the `$childTypeColumn` property on the parent model.

```php
use Tightenco\Parental\ReturnsChildModels;

class User extends Model
{
    use ReturnsChildModels;

    protected $childTypeColumn = 'parental_type';
}
```

---

Thanks to [@sschoger](https://twitter.com/steveschoger) for the sick logo design, and [@DanielCoulbourne](https://twitter.com/DCoulbourne) for helping brainstorm the idea on [Twenty Percent Time](http://twentypercent.fm/).