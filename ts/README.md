# Power of AST - Typescript Edition

## Requirements

| Name     | Version            |
|----------|--------------------|
| node.js  | >= 18.17.1         |
| npm      | 9.x.x              |

## Setup

```sh
npm install
```

## Running Linter

Before running linter, you will have to build custom rule:
```sh
npm run sh
```

And enable it in `.eslintrc.json` file:
```json
{
  "ast-demo/hard-const": "error"
}
```

Then you can run it:
```sh
npm run lint
```

You should have plenty of errors like:
```
error  const variables must have type definition           ast-demo/hard-const
error  const variables can only be top-level declarations  ast-demo/hard-const
```

## Tests

You can run test to test both custom eslint rule and interpreter:
```sh
npm run test
```

- Custom rule tests can be found in `ts/libs/eslint-plugin-ast-demo/tests`
- Custom interpreter test uses in-file testing, and they can be found in `ts/packages/basic-ast/src/lexer.mts` and `ts/packages/basic-ast/src/interpreter.mts`

