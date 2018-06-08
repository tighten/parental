![Parental - Use single table inheritance in your Laravel App](parental-banner.png)

# Parental

Parental is a early-stage development, alpha Laravel package by Tighten that brings STI (Single Table Inheritance) capabilities to Eloquent.

## The Problem

When you extend an Eloquent model, Eloquent looks at the class name to determine important database tables and fields.

```php
/** Admin model extends User model */

$admin = Admin::all(); // thinks table name is "admins" (instead of "users")

$admin->comments; // thinks foreign key is "admin_id" (instead of "user_id")

$admin->tags; // thinks pivot table is "admin_tag" (instead of "tag_user")
```

## The Solution

After pulling in Parental, simply add the `HasParentModel` trait to your child model.

```php
class Admin extends User
{
    use Tightenco\Parental\HasParentModel;
}
```

Voilà!

## Installation

```bash
composer require tightenco/parental
```
