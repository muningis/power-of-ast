import { NumberValue, RuntimeValue, StringValue, BoolValue, V_NULL, V_NUMBER, V_STRING, ValueType, V_BOOL } from "./values.mts";
import { BinaryExpression, Identifier, LogicalExpression, NumericLiteral, ProgramStatement, Statement, StringLiteral, UnaryExpression } from "./ast.mts";
import { Parser } from "./parser.mts";

const checkTypes = (left: RuntimeValue, right: RuntimeValue, expectedType?: ValueType) => expectedType ? (left.type === right.type && left.type === expectedType) : (left.type === right.type);
const checkType = (value: RuntimeValue, expectedType: ValueType) => value.type === expectedType;

function evaluateUnaryExpression(unaryExpression: UnaryExpression, variables: Record<string, string | number>): NumberValue {
  const argument = evaluate(unaryExpression.argument, variables);
  if (!checkType(argument, "number")) throw new Error("Can't use - on non-numbers");
  return V_NUMBER(-(argument.value as number));
}

function evaluateNumericBinaryExpression(left: NumberValue, right: NumberValue, operator: string): NumberValue {
  switch (operator) {
    case "+":
      return { type: 'number', value: left.value + right.value };
    case "-":
      return { type: 'number', value: left.value - right.value };
    case "*":
      return { type: 'number', value: left.value * right.value };
    case "/":
      return { type: 'number', value: left.value / right.value };
    default:
      return { type: 'number', value: left.value % right.value };
  }
}

function evaluateLogicalExpression(logicalExpression: LogicalExpression, variables: Record<string, string | number>): RuntimeValue {
  const leftSide = evaluate(logicalExpression.left, variables);
  const rightSide = evaluate(logicalExpression.right, variables);
  if (logicalExpression.operator === "&&") return V_BOOL(Boolean(leftSide.value) && Boolean(rightSide.value))
  else return V_BOOL(Boolean(leftSide.value) || Boolean(rightSide.value))
}

const COMPARISON_CHARACTERS = [">", ">=", "<", "<=", "===", "!=="];
function evaluateBinaryExpression(binaryExpression: BinaryExpression, variables: Record<string, string | number>): RuntimeValue {
  const leftSide = evaluate(binaryExpression.left, variables);
  const rightSide = evaluate(binaryExpression.right, variables);
  if (COMPARISON_CHARACTERS.indexOf(binaryExpression.operator) >= 0) {
    switch (binaryExpression.operator) {
      case ">":
        if (!checkTypes(leftSide, rightSide, "number")) throw new Error(`Can't compare ${leftSide.type} with ${rightSide.type}`);
        return { type: 'boolean', value: (leftSide as NumberValue).value > (rightSide as NumberValue).value } as BoolValue;
      case ">=":
        if (!checkTypes(leftSide, rightSide, "number")) throw new Error(`Can't compare ${leftSide.type} with ${rightSide.type}`);
        return { type: 'boolean', value: (leftSide as NumberValue).value >= (rightSide as NumberValue).value } as BoolValue;
      case "<":
        if (!checkTypes(leftSide, rightSide, "number")) throw new Error(`Can't compare ${leftSide.type} with ${rightSide.type}`);
        return { type: 'boolean', value: (leftSide as NumberValue).value < (rightSide as NumberValue).value } as BoolValue;
      case "<=":
        if (!checkTypes(leftSide, rightSide, "number")) throw new Error(`Can't compare ${leftSide.type} with ${rightSide.type}`);
        return { type: 'boolean', value: (leftSide as NumberValue).value <= (rightSide as NumberValue).value } as BoolValue;
      case "!==":
        if (!checkTypes(leftSide, rightSide)) throw new Error(`Can't compare ${leftSide.type} with ${rightSide.type}`);
        return { type: 'boolean', value: leftSide.value !== rightSide.value } as BoolValue;
      default:
        if (!checkTypes(leftSide, rightSide)) throw new Error(`Can't compare ${leftSide.type} with ${rightSide.type}`);
        return { type: 'boolean', value: leftSide.value === rightSide.value } as BoolValue;
    }
  } else {
    if (!checkTypes(leftSide, rightSide, "number")) throw new Error(`Can't use math expressions with ${leftSide.type} and ${rightSide.type}`);
    return evaluateNumericBinaryExpression(V_NUMBER(leftSide.value as number), V_NUMBER(rightSide.value as number), binaryExpression.operator)
  }
}

function evaluateProgram(program: ProgramStatement, variables: Record<string, string | number>): RuntimeValue {
  let lastEvaluated: RuntimeValue = V_NULL();
  for (const statement of program.body) {
    lastEvaluated = evaluate(statement, variables);
  }
  return lastEvaluated;
}

function evaluateIdentifier(identifier: Identifier, variables: Record<string, number | string>): RuntimeValue {
  if (!(identifier.symbol in variables)) throw new Error(`${identifier.symbol} is undefined`);
  const value = variables[identifier.symbol];
  return typeof value === "number" ? V_NUMBER(value) : typeof value === "string" ? V_STRING(value) : V_NULL();
}

export function evaluate(astNode: Statement, variables: Record<string, string | number> = {}): RuntimeValue {
  switch (astNode.kind) {
    case "NumericLiteral":
      return { value: (astNode as NumericLiteral).value, type: 'number' } as NumberValue;
    case "StringLiteral":
      return { value: (astNode as StringLiteral).value, type: 'string' } as StringValue;
    case "UnaryExpression":
      return evaluateUnaryExpression(astNode as UnaryExpression, variables);
    case "BinaryExpression":
      return evaluateBinaryExpression(astNode as BinaryExpression, variables);
    case "LogicalExpression":
      return evaluateLogicalExpression(astNode as LogicalExpression, variables);
    case "Program":
      return evaluateProgram(astNode as ProgramStatement, variables);
    case "Identifier":
      return evaluateIdentifier(astNode as Identifier, variables) as NumberValue | StringValue;
    default:
      throw new Error(`Unexpected Node ${JSON.stringify(astNode)}`)
  }
}

if (import.meta.vitest) {
  const { describe, it, expect } = import.meta.vitest;
  describe("#evaluate()", () => {
    describe("Throw errors", () => {
      it("should throw error if variable in expression is undefined", () => {
        const program = Parser().produceAST("FOO === 5");
        expect(() => evaluate(program)).toThrowError("FOO is undefined");
      });

      it("should throw that string and number can not be compared", () => {
        const program = Parser().produceAST("\"FOO\" === 5");
        expect(() => evaluate(program)).toThrowError("Can't compare string with number");
      });

      it("should throw that string and number can not be compared", () => {
        const program = Parser().produceAST("\"FOO\" > 5");
        expect(() => evaluate(program)).toThrowError("Can't compare string with number");
      });

      it("should throw that string and number can not be compared", () => {
        const program = Parser().produceAST("\"FOO\" >= 5");
        expect(() => evaluate(program)).toThrowError("Can't compare string with number");
      });

      it("should throw that string and number can not be compared", () => {
        const program = Parser().produceAST("\"FOO\" < 5");
        expect(() => evaluate(program)).toThrowError("Can't compare string with number");
      });

      it("should throw that string and number can not be compared", () => {
        const program = Parser().produceAST("\"FOO\" <= 5");
        expect(() => evaluate(program)).toThrowError("Can't compare string with number");
      });

      it("should throw that arithmetic operations can't be done on string and number", () => {
        const program = Parser().produceAST("\"FOO\" + 5");
        expect(() => evaluate(program)).toThrowError("Can't use math expressions with string and number");
      });

      it("should throw that arithmetic operations can't be done on string and number", () => {
        const program = Parser().produceAST("\"FOO\" - 5");
        expect(() => evaluate(program)).toThrowError("Can't use math expressions with string and number");
      });

      it("should throw that arithmetic operations can't be done on string and number", () => {
        const program = Parser().produceAST("\"FOO\" / 5");
        expect(() => evaluate(program)).toThrowError("Can't use math expressions with string and number");
      });

      it("should throw that arithmetic operations can't be done on string and number", () => {
        const program = Parser().produceAST("\"FOO\" * 5");
        expect(() => evaluate(program)).toThrowError("Can't use math expressions with string and number");
      });

      it("should throw that arithmetic operations can't be done on string and number", () => {
        const program = Parser().produceAST("\"FOO\" % 5");
        expect(() => evaluate(program)).toThrowError("Can't use math expressions with string and number");
      });
    });

    describe("Compare strings", () => {
      type TestCase = { expression: string; expect: boolean; variables: Record<string, string | number> };
      ([
        { expression: 'GREETING === "Hello, World!"', expect: true, variables: { GREETING: "Hello, World!" } },
        { expression: 'GREETING === "Hello, Team!"', expect: false, variables: { GREETING: "Hello, World!" } },
        { expression: 'GREETING !== "Hello, Team!"', expect: true, variables: { GREETING: "Hello, World!" } },
      ] as TestCase[]).forEach((testCase: TestCase) => {
        it(`should evaluate ( ${testCase.expression} ) as ${testCase.expect}`, () => {
          const program = Parser().produceAST(testCase.expression);
          expect(evaluate(program, testCase.variables).value).toBe(testCase.expect);
        })
      })
    });

    describe("Compare numbers", () => {
      type TestCase = { expression: string; expect: boolean; variables: Record<string, string | number> };
      ([
        { expression: "A >= 100", expect: true, variables: { A: 105 } },
        { expression: "A < 100", expect: false, variables: { A: 105 } },
      ] as TestCase[]).forEach((testCase: TestCase) => {
        it(`should evaluate ( ${testCase.expression} ) as ${testCase.expect}`, () => {
          const program = Parser().produceAST(testCase.expression);
          expect(evaluate(program, testCase.variables).value).toBe(testCase.expect);
        })
      })
    });

    describe("Perform arithmetic operations", () => {
      type TestCase = { expression: string; expect: boolean; variables: Record<string, string | number> };
      ([
        { expression: "A === 100 + 5", expect: true, variables: { A: 105 } },
        { expression: "A - 20 === 80", expect: true, variables: { A: 100 } },
      ] as TestCase[]).forEach((testCase: TestCase) => {
        it(`should evaluate ( ${testCase.expression} ) as ${testCase.expect}`, () => {
          const program = Parser().produceAST(testCase.expression);
          expect(evaluate(program, testCase.variables).value).toBe(testCase.expect);
        })
      })
    });

    describe("Perform complicated comparisons", () => {
      type TestCase = { expression: string; expect: boolean; variables: Record<string, string | number> };
      ([
        { expression: "A >= (B - 100) && C === \"HELLO\"", expect: true, variables: { A: 105, B: 200, C: "HELLO" } },
        { expression: "(A - B) / (C + D) > 1", expect: true, variables: { A: 100, B: 50, C: 25, D: 10 } },
        { expression: "(P + Q) / (R - S) < -2", expect: false, variables: { P: 50, Q: 20, R: 30, S: 10 } },
        { expression: "(P * P) / Q === (R + S)", expect: true, variables: { P: 10, Q: 2, R: 20, S: 30 } }
      ] as TestCase[]).forEach((testCase) => {
        it(`should evaluate ( ${testCase.expression} ) as ${testCase.expect}`, () => {
          const program = Parser().produceAST(testCase.expression);
          expect(evaluate(program, testCase.variables).value).toBe(testCase.expect);
        })
      })
    });
  })
}