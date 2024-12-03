# Power of AST - PHP Edition

## Requirements

| Name     | Version            |
|----------|--------------------|
| PHP      | >= 7.4.0 && < 8.2  |
| Composer | 2.x.x              |

## Setup

```sh
composer install
composer dump-autoload
```

## Running Linter

```sh
vendor/bin/php-cs-fixer fix
```

You should get error:
```
Error in EnumNaming fixer: [PowerOfAST/enum_naming] Enum "ETokenType" should not start with letter E in file /Users/muningis/code/power-of-ast/php/src/Parser/Frontend/ETokenType.php
```

## Tests

You can run test to verify that interpreter works as expected. Tests are found in `php/tests/Parser`, and can be ram be executing this command:
```sh
vendor/bin/phpunit tests/Parser/*.php
```

## Running interpreter
Interpreter can be ran by executing following command:
```sh
composer run-parser 
```

Evaluated expression can be modified in `php/src/Parser/Main.php` file on `line 15`
