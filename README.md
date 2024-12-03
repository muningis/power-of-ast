# Power of AST

## Table of contents

## Typescript

### Setup
```sh
cd ts
npm install
npm run build
```

### Enable rule
- Open `.eslintrc.json` and set `ast-demo/hard-const` to `"error"`
- Now you can run `npm run lint` and you should have plenty of lint errors coming from custom rule.

### Running tests
This will run tests both for custom AST interpreter and Custom ESLint rule
```sh
npm run test
```



## PHP

### Setup

```sh
composer install
```

### Running tests
AST Implementation can be verified by tests found in `php/tests` directory and by running `vendor/bin/phpunit tests/Parser/*.php`

### Lint

You can run lint'er with custom rule:
```sh
vendor/bin/php-cs-fixer fix
```

You should get error:
```
Error in EnumNaming fixer: [PowerOfAST/enum_naming] Enum "ETokenType" should not start with letter E in file /Users/muningis/code/power-of-ast/php/src/Parser/Frontend/ETokenType.php
```


## Test suite

There will be a test suite, which will run same expressions on different implementations.
