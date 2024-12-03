import { RuleTester } from '@typescript-eslint/rule-tester';
import { hardConst } from '../lib/rules/hard-const.ts';

const ruleTester = new RuleTester({
  parser: '@typescript-eslint/parser',
  parserOptions: {
    ecmaFeatures: {
      jsx: true,
    },
  },
});

ruleTester.run('eslint-plugin-ast-demo/hard-const', hardConst, {
  valid: [
    {
      code: `const SECONDS_IN_DAY: number = 86400`,
    },
    {
      code: `const FOO: string = "bar";`,
    },
  ],
  invalid: [
    {
      code: `const secondsInDay: number = 86400`,
      errors: [
        {
          messageId: 'issue:bad-pattern',
        },
      ],
    },
    {
      code: `const SECONDS_IN_DAY: number = 24 * 60 * 60`,
      errors: [
        {
          messageId: 'issue:non-static',
        },
      ],
    },
    {
      code: `function hello() { const greeting = "world!"; return \`Hello, \$\{greeting\}\` }`,
      errors: [{
        messageId: 'issue:must-be-at-root'
      }]
    }, {
      code: `const T = true`,
      errors: [{
        messageId: "issue:missing-type"
      }]
    }
  ],
});
