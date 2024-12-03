import { evaluate } from "./interpreter.mts";
import { Parser } from "./parser.mts";

async function main() {
  const input = process.argv[2] ?? "A >= (B - 100) && C === \"HELLO\"";
  const variables = JSON.parse(process.argv[3] ?? '{ "A": 105, "B": 200, "C": "HELLO" }');
  // This can probably be memoized, as there's no usage of variables yet;
  console.time("main");
  console.time("parser.produceAST");
  const program = Parser().produceAST(input);
  for (let i = 0; i < 999999; i++) Parser().produceAST(input);
  console.timeEnd("parser.produceAST");
  // Can we construct a function out of this, which accepts
  console.time("evaluate");
  const result = evaluate(program, variables);
  for (let i = 0; i < 999999; i++) evaluate(program, variables);
  console.timeEnd("evaluate");
  console.timeEnd("main");
  return result.value;
}

console.log(await main());