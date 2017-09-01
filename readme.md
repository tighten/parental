# Model Inheritance

## The Problem
When you extend an Eloquent model, Eloquent looks at the class name to determine important database tables and fields.
```php
/** Admin extends User */

$admin = Admin::all(); // thinks table name is "admins" (instead of "users")

$admin->comments; // thinks foreign key is "admin_id" (instead of "user_id")

$admin->tags; // thinks pivot table is "admin_tag" (instead of "tag_user")
```

## The Solution
All you need to do is add the `HasParentModel` trait to your child model.
```php
class Admin extends User
{
    use Tightenco\Parental\HasParentModel;
}
```
Vioala!

## Installation
`$ composer require tightenco/parental`
