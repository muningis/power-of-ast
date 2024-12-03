import type {
  BinaryExpression,
  Expression,
  Identifier,
  LogicalExpression,
  NumericLiteral,
  ProgramStatement,
  Statement,
  StringLiteral,
  UnaryExpression
} from "./ast.mts";
import type { Token } from "./lexer.mts";
import { TokenType, tokenize } from "./lexer.mts";


export function Parser() {
  const tokens = new Array<Token>();

  const notEoe = () => tokens[0]?.type !== TokenType.EndOfExpression;
  const at = () => tokens[0] as Token;
  const next = (expectedToken?: TokenType, errorMessage = "Unexpected token") => {
    const token = tokens[0] as Token;
    if (expectedToken && token.type !== expectedToken) throw new Error(errorMessage);
    return tokens.shift() as Token;
  }
  const expect = (type: TokenType, errorMessage = "Unexpected token") => {
    const prev = tokens.shift() as Token;
    if (!prev || prev.type !== type) {
      throw new Error(`Parser Error:\n ${errorMessage}, found ${prev.type}, expected ${type}`)
    }
  }

  const parseStatement = (): Statement => {
    return parseExpression();
  }

  /**
   * Order of Precedence (low to high)
   * LogicalExpression
   * ComparisonExpression
   * AdditiveExpression
   * MultiplicativeExpression
   * UnaryExpression
   * PrimaryExpression
   */
  const parseExpression = (): Expression => {
    return parseLogicalExpression();
  }

  const LOGICAL_CHARACTERS = ["&&", "||"];
  const parseLogicalExpression = (): Expression => {
    let left = parseComparisonExpression();
    while (LOGICAL_CHARACTERS.indexOf(at().value) >= 0) {
      const operator = next().value;
      const right = parseComparisonExpression();
      left = {
        kind: "LogicalExpression",
        operator,
        left,
        right
      } as LogicalExpression;
    }
    return left;
  }

  const COMPARISON_CHARACTERS = [">", ">=", "<", "<=", "===", "!=="];
  const parseComparisonExpression = (): Expression => {
    let left = parseAdditiveExpression();
    while (COMPARISON_CHARACTERS.indexOf(at().value) >= 0) {
      const operator = next().value;
      const right = parseAdditiveExpression();
      left = {
        kind: "BinaryExpression",
        operator,
        left,
        right
      } as BinaryExpression;
    }
    return left;
  }

  /**
   * 10 + 5 * 5
   * 10 + (5 * 5)
   */
  const MULTIPLICATIVE_CHARACTERS = ["/", "*", "%"];
  const parseMultiplicativeExpression = (): Expression => {
    let left = parsePrimaryExpression();

    while (MULTIPLICATIVE_CHARACTERS.indexOf(at().value) >= 0) {
      const operator = next().value;
      const right = parsePrimaryExpression();
      left = {
        kind: "BinaryExpression",
        left,
        right,
        operator
      } as BinaryExpression

    }
    return left;
  }

  /**
   * 10 + 5 - 5
   * (10 + 5) - 5
   */
  const ADDITIVE_CHARACTERS = ["-", "+"];
  const parseAdditiveExpression = (): Expression => {
    let left = parseMultiplicativeExpression();

    while (ADDITIVE_CHARACTERS.indexOf(at().value) >= 0) {
      const operator = next().value;
      const right = parseMultiplicativeExpression();
      left = {
        kind: "BinaryExpression",
        left,
        right,
        operator
      } as BinaryExpression

    }
    return left;
  }

  const parsePrimaryExpression = (): Expression => {
    const token = at();
    switch (token.type) {
      case TokenType.Unary:
        const operator = at().value;
        next();
        return { kind: "UnaryExpression", operator, prefix: true, argument: parseExpression() } as UnaryExpression;
      case TokenType.Identifier:
        return { kind: "Identifier", symbol: next().value } as Identifier
      case TokenType.Number:
        return { kind: "NumericLiteral", value: parseFloat(next().value) } as NumericLiteral
      case TokenType.String:
        return { kind: "StringLiteral", value: next().value } as StringLiteral
      case TokenType.OpenParenthesis:
        next(); // consume OpenParenthesis
        // eslint-disable-next-line no-case-declarations
        const value = parseExpression();
        expect(TokenType.CloseParenthesis, "Unexpected token found inside parenthesis expression. Expected closing parenthesis"); // consume CloseParenthesis
        return value;
      default:
        throw new Error(`Unexpected token found during parse - ${JSON.stringify(at())}`);
    }
  }

  const produceAST = (expression: string): ProgramStatement => {
    tokens.push(...tokenize(expression));
    const program: ProgramStatement = {
      kind: "Program",
      body: []
    }

    while (notEoe()) {
      program.body.push(parseStatement())
    }

    return program;
  }

  return { produceAST };
}