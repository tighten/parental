![Parental - Use single table inheritance in your Laravel App](/art/parental-banner.png)

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

Now, running `Admin::create()` will set the `type` column in the `users` table to `admin` instead of `App\Models\Admin`.

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

## Transforming Models Between Types

You may transform a model from one type to another using the `become()` method.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;
use Parental\HasParent;

class Order extends Model
{
    use HasChildren;

    protected $fillable = ['type', 'total'];

    protected $childTypes = [
        'pending' => PendingOrder::class,
        'shipped' => ShippedOrder::class,
    ];
}

class PendingOrder extends Order
{
    use HasParent;
}

class ShippedOrder extends Order
{
    use HasParent;
}
```

```php
use App\Models\Order;
use App\Models\ShippedOrder;

// Retrieve a pending order
$order = Order::first();

// Ship the order by transforming it
$order = $order->become(ShippedOrder::class);

// Updates the "type" column to "shipped" and returns a ShippedOrder instance
$order->save();
```

### What problem did we just solve?

The `become()` method will return a new instance of the specified child model with all the attributes of the original model. You must call `save()` on the returned model to persist the change to the database. This allows you to easily transition a model between different types while maintaining its data integrity, such as changing an order from pending to shipped, or a draft post to a published post.

This is also useful when you're using observers or callbacks, since the specific child model's behavior will be triggered after the transition.

A new model event is fired when a model is _becoming_ another type, you may listen to it like so:

```php
ShippedOrder::becoming(function ($shippedOrder) {
    // Do something before the model is saved...
});
```

## Eager Loading Child Models

To help with eager-loading relationships on child models, Parental provides a set of helpers that you may use in your queries. For the examples, we'll use the following models:

```php
class Room extends Model
{
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}

class Message extends Model
{
    use HasChildren;

    protected $fillable = ['type', 'content'];

    protected $childTypes = [
        'text' => TextMessage::class,
        'image' => ImageMessage::class,
    ];
}

class TextMessage extends Message
{
    use HasParent;

    public function mentions(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

class ImageMessage extends Message
{
    use HasParent;

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
```

### Eager Loading From Model Instance

You may eager-load relationships of different models from a parent model instance using the `loadChildren` method:

```php
$message = Message::first();

$message->loadChildren([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
]);
```

This will ensure that, if `$message` is an instance of `TextMessage`, the `mentions` relationship will be eager-loaded. If it's an instance of `ImageMessage`, the `attachments` relationship will be eager-loaded.

Alternatively, you may eager-load the relationship counts using the `loadChildrenCount` method:

```php
$message = Message::first();

$message->loadChildrenCount([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
]);
```

This will ensure that, if `$message` is an instance of `TextMessage`, the `mentions_count` attribute will be filled. If it's an instance of `ImageMessage`, the `attachments_count` attribute will be filled.

### Eager Loading From Eloquent Collection

You may eager-load relationships from an Eloquent Collection using the `loadChildren` method:

```php
$messages = Message::all();

$messages->loadChildren([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
]);
```

This will ensure that the appropriate relationships are eager-loaded for each child model in the collection based on its type.

Alternatively, you may eager-load the relationship counts using the `loadChildrenCount` method:

```php
$messages = Message::all();

$messages->loadChildrenCount([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
]);
```

This will ensure the `mentions_count` attribute will be filled for instances of the `TextMessage` model, and the `attachments_count` attribute will be filled for instances of the `ImageMessage` model.

### Eager Loading From Query and Relationship

You may eager-load relationships directly from a query or relationship using the `childrenWith` method:

```php
// From a query...
$messages = Message::query()->childrenWith([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
])->get();

// From a relationship...
$room = Room::first();
$messages = $room->messages()->childrenWith([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
])->get();
```

This will ensure that the appropriate relationships are eager-loaded for each child model in the result set based on its type.

Alternatively, you may eager-load the relationship counts using the `childrenWithCount` method:

```php
// From a query...
$messages = Message::query()->childrenWithCount([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
])->get();

// From a relationship...
$room = Room::first();
$messages = $room->messages()->childrenWithCount([
    TextMessage::class => ['mentions'],
    ImageMessage::class => ['attachments'],
])->get();
```

This will ensure the `mentions_count` attribute is filled on instances of the `TextMessage` model, and the `attachments_count` attribute is filled on instances of the `ImageMessage` model.

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
