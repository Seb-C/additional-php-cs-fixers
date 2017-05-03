This library adds some custom fixers for php-cs-fixer (v2).

Installation
============

Add this package to your composer.json:
```json
{
    "require-dev": {
        "seb-c/additional-php-cs-fixers": "dev-master"
    },
}
```

Modify your `.php_cs`:

- Include the composer autoload:
```php
include 'vendor/autoload.php';
```

- Register the custom fixers:
```php
return PhpCsFixer\Config::create()
    //...
    ->registerCustomFixers(SebC\AdditionalPhpCsFixers\Helper::getCustomFixers())
```

- Use the new rules as you wish:
```
$rules = [
    // ...
    'SebCAdditionalPhpCsFixers/disallow_unaliased_classes' => [
        'replace_namespaces' => [
            'Fuel\Core' => '',
            'Illuminate\Support\Facades' => '',
        ],
    ],
];
```

`disallow_unaliased_classes` rule:
==================================

This prevents any use of some specific namespace, and encourages to replace it with another.

This is mainly useful/designed to force the use of aliased classes in some frameworks like Laravel or FuelPHP.

As example, with the following rules configuration:
- `'Fuel\Core' => '',` will trigger an error everytime a class such as `Fuel\Core\Config` is directly called, and will suggest to replace it with `Config`
- `'Illuminate\Support\Facades' => '',` will prevent a call like `Illuminate\Support\Facades\Validator` and replace it with `Validator`
- `'Some\Evil\Stuff' => 'OtherStuff',` will replace `Some\Evil\Stuff\Foo::myFunction()` and replace it with `OtherStuff\Foo::myFunction()`

This also works with `use`d namespaces.
