import { AST_NODE_TYPES } from '@typescript-eslint/types';
import { ESLintUtils } from '@typescript-eslint/utils';

const createRule = ESLintUtils.RuleCreator(name => `https://muningis.lt/eslint/${name}`);

const ALLOWED_CONST_NAME_PATTERN = /[A-Z][A-Z_]*/;
const DISALLOWED_CHARACTERS = /[a-z0-9$]/;

export const hardConst = createRule({
  name: "hard-const",
  meta: {
    docs: {
      description: 'A rule to showcase usage of AST!',
    },
    type: 'problem',
    messages: {
      "issue:missing-identifier": "const must have an identifier.",
      "issue:bad-pattern": "const can only start with `A-Z` and can only contain `A-Z` and `_`.",
      "issue:non-static": "const variables can only contain static values.",
      "issue:must-be-at-root": 'const variables can only be top-level declarations.',
      "issue:missing-type": 'const variables must have type definition',
    },
    schema: [],
  },
  defaultOptions: [],
  create(context) {
    return {
      VariableDeclaration: node => {
        if (node.kind !== "const") return;

        if (node.parent.type !== AST_NODE_TYPES.Program) {
          context.report({
            node,
            data: { type: node.parent.parent?.type },
            messageId: 'issue:must-be-at-root',
          })
          return;
        }

        node.declarations.forEach(declaration => {
          if (!("name" in declaration.id)) {
            context.report({
              node,
              messageId: 'issue:missing-identifier',
            })
            return;
          }
          if (!("typeAnnotation" in declaration.id) || typeof declaration.id.typeAnnotation === "undefined") {
            context.report({
              node,
              messageId: 'issue:missing-type',
            })
            return;
          }


          if (!ALLOWED_CONST_NAME_PATTERN.test(declaration.id.name) || DISALLOWED_CHARACTERS.test(declaration.id.name)) {
            context.report({
              node,
              messageId: 'issue:bad-pattern',
            })
            return;
          }

          if (declaration.init?.type !== AST_NODE_TYPES.Literal) {
            context.report({
              node,
              messageId: 'issue:non-static',
            })
            return;
          }
        })
      }
    }
  }
});
