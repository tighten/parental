# Model Inheritance

## Installation
`> composer require tightenco/model-inheritance`

## Usage
To use this package, add the `HasParentModel` trait to an eloquent model that extends another model.
```
<?php

use Tightenco\ModelInheritance\HasParentModel;

class Admin extends User
{
    use HasParentModel;
}
```