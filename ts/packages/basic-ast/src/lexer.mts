export enum TokenType {
  Identifier,
  Number,
  String,
  
  Comparison,
  LessThan,
  MoreThan,
  
  Unary,
  OpenParenthesis,
  CloseParenthesis,
  Operator,
  BinaryOperator,

  EndOfExpression,
}

export interface Token {
  type: TokenType;
  value: string;
}

const VARIABLE_FIRST_CHARACTER = /[A-Za-z_$]/;
const VARIABLE_CHARACTERS = /[A-Za-z0-9_$]/;
const NUMBER = /[0-9]/;
const OPERATOR = /[+-/*%]/;
const QUOTE = /["']/;

const token = (value: string, type: TokenType) => ({
  type,
  value
});

export function tokenize(expression: string): Token[] {
  const tokens = new Array<Token>();

  let cursor = 0;
  const len = expression.length
  while (cursor < len) {
    // Non-null expression is allowed here, as cursor can not be higher than last index of a string
    const char = expression[cursor]!;
    if (VARIABLE_FIRST_CHARACTER.test(char)) {
      let symbol = char;
      cursor++;
      while (cursor < len && VARIABLE_CHARACTERS.test(expression[cursor]!)) {
        symbol = symbol + expression[cursor];
        cursor++
      }
      tokens.push(token(symbol, TokenType.Identifier));
      continue;
    } else if (char === "(") {
      tokens.push(token(char, TokenType.OpenParenthesis));
      cursor++;
      continue;
    } else if (char === ")") {
      tokens.push(token(char, TokenType.CloseParenthesis))
      cursor++;
      continue;
    } else if (char === "-" && (expression[cursor + 1] === "(" || NUMBER.test(expression[cursor + 1]!))) {
      tokens.push(token(char, TokenType.Unary));
      cursor++;
      continue;
    } else if (OPERATOR.test(char)) {
      tokens.push(token(char, TokenType.Operator));
      cursor++;
      continue;
    } else if (QUOTE.test(char)) {
      const quote = char;
      let text = "";
      while (expression[++cursor] !== quote) {
        text = text + expression[cursor];
      }
      tokens.push(token(text, TokenType.String));
      cursor++;
      continue;
    } else if (NUMBER.test(char)) {
      let number = char;
      let foundDecimalSeparator = false;
      while (cursor < len && (NUMBER.test(expression[++cursor]!) || (!foundDecimalSeparator && expression[cursor] === "."))) {
        if (expression[cursor] === ".") foundDecimalSeparator = true;
        number = number + expression[cursor];
      }
      tokens.push(token(number, TokenType.Number));
      continue;
    } else if (char === "&" && expression[cursor + 1] === "&") {
      tokens.push(token("&&", TokenType.BinaryOperator));
      cursor += 2;
      continue;
    } else if (char === "|" && expression[cursor + 1] === "|") {
      tokens.push(token("||", TokenType.BinaryOperator));
      cursor += 2;
      continue;
    } else if (char === "!" && expression[cursor + 1] === "=" && expression[cursor + 2] === "=") {
      tokens.push(token("!==", TokenType.Comparison));
      cursor += 3;
      continue;
    } else if (char === "=" && expression[cursor + 1] === "=" && expression[cursor + 2] === "=") {
      tokens.push(token("===", TokenType.Comparison));
      cursor += 3;
      continue;
    } else if (char === ">") {
      const moreThanOrEqual = expression[cursor + 1] === "=";
      tokens.push(token(moreThanOrEqual ? ">=" :">", TokenType.MoreThan));
      cursor = cursor + (moreThanOrEqual ? 2 : 1);
      continue;
    } else if (char === "<") {
      const lessThanOrEqual = expression[cursor + 1] === "=";
      tokens.push(token(lessThanOrEqual ? "<=" :"<", TokenType.LessThan));
      cursor = cursor + (lessThanOrEqual ? 2 : 1);
      continue;
    } else if (char === " ") {
      cursor++;
      continue;
    }

    throw new Error(`Unrecognized symbol, ${char} as position ${cursor}`)
  }

  tokens.push(token("EOE", TokenType.EndOfExpression));
  return tokens;
}

if (import.meta.vitest) {
  const { describe, it, expect } = import.meta.vitest;
  describe("#tokenize()", () => {
    it(`should tokenize 'FOO === "BAR" && (SUM === 5 || REGULAR_SUM === 50)'`, () => {
      const results = tokenize("FOO === \"BAR\" && (SUM === 5 || REGULAR_SUM === 50)");
      expect(results).toStrictEqual([
        { type: TokenType.Identifier, value: "FOO" },
        { type: TokenType.Comparison, value: "===" },
        { type: TokenType.String, value: "BAR" },
        { type: TokenType.BinaryOperator, value: "&&" },
        { type: TokenType.OpenParenthesis, value: "(" },
        { type: TokenType.Identifier, value: "SUM" },
        { type: TokenType.Comparison, value: "===" },
        { type: TokenType.Number, value: "5" },
        { type: TokenType.BinaryOperator, value: "||" },
        { type: TokenType.Identifier, value: "REGULAR_SUM" },
        { type: TokenType.Comparison, value: "===" },
        { type: TokenType.Number, value: "50" },
        { type: TokenType.CloseParenthesis, value: ")" },
        { type: TokenType.EndOfExpression, value: "EOE" }
      ]);
    });

    it(`should tokenize 'REGULAR_SUM === 50 && (SUM === 5 || FOO === "BAR") && SOMETHING > 5 && SOMETHAT < 9' `, () => {
      const results = tokenize("REGULAR_SUM === 50 && (SUM === 5 || FOO === \"BAR\") && SOMETHING > 5 && SOMETHAT < 9");
      expect(results).toStrictEqual([
        { type: TokenType.Identifier, value: "REGULAR_SUM" },
        { type: TokenType.Comparison, value: "===" },
        { type: TokenType.Number, value: "50" },
        { type: TokenType.BinaryOperator, value: "&&" },
        { type: TokenType.OpenParenthesis, value: "(" },
        { type: TokenType.Identifier, value: "SUM" },
        { type: TokenType.Comparison, value: "===" },
        { type: TokenType.Number, value: "5" },
        { type: TokenType.BinaryOperator, value: "||" },
        { type: TokenType.Identifier, value: "FOO" },
        { type: TokenType.Comparison, value: "===" },
        { type: TokenType.String, value: "BAR" },
        { type: TokenType.CloseParenthesis, value: ")" },
        { type: TokenType.BinaryOperator, value: "&&" },
        { type: TokenType.Identifier, value: "SOMETHING" },
        { type: TokenType.MoreThan, value: ">" },
        { type: TokenType.Number, value: "5" },
        { type: TokenType.BinaryOperator, value: "&&" },
        { type: TokenType.Identifier, value: "SOMETHAT" },
        { type: TokenType.LessThan, value: "<" },
        { type: TokenType.Number, value: "9" },
        { type: TokenType.EndOfExpression, value: "EOE" }
      ]);
    });

    it(`should tokenize '15 <= (2 * FOO)' `, () => {
      const results = tokenize("15 <= (2 * FOO)");
      expect(results).toStrictEqual([
        { type: TokenType.Number, value: "15" },
        { type: TokenType.LessThan, value: "<=" },
        { type: TokenType.OpenParenthesis, value: "(" },
        { type: TokenType.Number, value: "2" },
        { type: TokenType.Operator, value: "*" },
        { type: TokenType.Identifier, value: "FOO" },
        { type: TokenType.CloseParenthesis, value: ")" },
        { type: TokenType.EndOfExpression, value: "EOE" }

      ]);
    });
  })
}