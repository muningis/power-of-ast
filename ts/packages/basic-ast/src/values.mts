
export type ValueType = "string" | "number" | "null" | "boolean";

export interface RuntimeValue {
  type: ValueType;
  value: unknown;
}

export const V_NULL = () => ({ type: "null", value: null } as NullValue)
export interface NullValue extends RuntimeValue {
  type: "null";
  value: null;
}

export const V_STRING = (value: string) => ({ type: "string", value } as StringValue)
export interface StringValue extends RuntimeValue {
  type: "string";
  value: string;
}

export const V_NUMBER = (value: number) => ({ type: "number", value } as NumberValue)
export interface NumberValue extends RuntimeValue {
  type: "number";
  value: number;
}

export const V_BOOL = (value: boolean) => ({ type: "boolean", value } as BoolValue)
export interface BoolValue extends RuntimeValue {
  type: "boolean";
  value: boolean;
}
