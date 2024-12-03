type NodeType =
  | "Program"
  | "NumericLiteral"
  | "StringLiteral"
  | "Identifier"
  | "BinaryExpression"
  | "LogicalExpression"
  | "UnaryExpression";

export interface Statement {
  kind: NodeType;
}

export interface ProgramStatement extends Statement {
  kind: "Program";
  body: Statement[];
}

export interface Expression extends Statement {}

export interface BinaryExpression extends Expression {
  kind: "BinaryExpression";
  left: Expression;
  right: Expression;
  /**
   * @description "+" | "-" | "*" | "/" | "%" | ">" | ">=" | "<" | "<=" | "===" | "!=="
   */
  operator: string;
}

export interface LogicalExpression extends Expression {
  kind: "LogicalExpression";
  left: Expression;
  right: Expression;
  /**
   * @description "&&" | "||"
   */
  operator: string;
}

export interface UnaryExpression extends Expression {
  kind: "UnaryExpression";
  /**
   * @description "-"
   */
  operator: string;
  prefix: boolean;
  argument: Expression;
}

export interface Identifier extends Statement {
  kind: "Identifier";
  symbol: string;
}

export interface NumericLiteral extends Statement {
  kind: "NumericLiteral";
  value: number;
}

export interface StringLiteral extends Statement {
  kind: "StringLiteral";
  value: string;
}