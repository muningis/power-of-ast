<?php

namespace Parser\Backend;

use Parser\ASTNodes\{
  ProgramStatement,
  Statement,
  BinaryExpression,
  Identifier,
  UnaryExpression,
  LogicalExpression
};
use Parser\Values\{RuntimeValue, NumberValue, Values};

class Interpreter
{
  private const COMPARISON_CHARACTERS = [">", ">=", "<", "<=", "===", "!=="];

  private function checkTypes(
    RuntimeValue $left,
    RuntimeValue $right,
    ?string $expectedType = null
  ): bool {
    return $expectedType
      ? $left->getType() === $right->getType() &&
          $left->getType() === $expectedType
      : $left->getType() === $right->getType();
  }

  private function checkType(RuntimeValue $value, string $expectedType): bool
  {
    return $value->getType() === $expectedType;
  }

  private function evaluateUnaryExpression(
    UnaryExpression $unaryExpression,
    array $variables
  ): NumberValue {
    $argument = $this->evaluate($unaryExpression->getArgument(), $variables);
    if (!$this->checkType($argument, "number")) {
      throw new \RuntimeException("Can't use - on non-numbers");
    }
    return Values::createNumber(-$argument->getValue());
  }

  private function evaluateNumericBinaryExpression(
    NumberValue $left,
    NumberValue $right,
    string $operator
  ): NumberValue {
    $result = match ($operator) {
      "+" => $left->getValue() + $right->getValue(),
      "-" => $left->getValue() - $right->getValue(),
      "*" => $left->getValue() * $right->getValue(),
      "/" => $left->getValue() / $right->getValue(),
      default => $left->getValue() % $right->getValue(),
    };
    return Values::createNumber($result);
  }

  private function evaluateLogicalExpression(
    LogicalExpression $logicalExpression,
    array $variables
  ): RuntimeValue {
    $leftSide = $this->evaluate($logicalExpression->getLeft(), $variables);
    $rightSide = $this->evaluate($logicalExpression->getRight(), $variables);

    return Values::createBool(
      $logicalExpression->getOperator() === "&&"
        ? (bool) $leftSide->getValue() && (bool) $rightSide->getValue()
        : (bool) $leftSide->getValue() || (bool) $rightSide->getValue()
    );
  }

  private function evaluateBinaryExpression(
    BinaryExpression $binaryExpression,
    array $variables
  ): RuntimeValue {
    $leftSide = $this->evaluate($binaryExpression->getLeft(), $variables);
    $rightSide = $this->evaluate($binaryExpression->getRight(), $variables);
    $operator = $binaryExpression->getOperator();

    if (in_array($operator, self::COMPARISON_CHARACTERS)) {
      if ($operator === "!==" || $operator === "===") {
        if (!$this->checkTypes($leftSide, $rightSide)) {
          throw new \RuntimeException(
            "Can't compare {$leftSide->getType()} with {$rightSide->getType()}"
          );
        }
        return Values::createBool(
          $operator === "==="
            ? $leftSide->getValue() === $rightSide->getValue()
            : $leftSide->getValue() !== $rightSide->getValue()
        );
      }

      if (!$this->checkTypes($leftSide, $rightSide, "number")) {
        throw new \RuntimeException(
          "Can't compare {$leftSide->getType()} with {$rightSide->getType()}"
        );
      }

      return Values::createBool(
        match ($operator) {
          ">" => $leftSide->getValue() > $rightSide->getValue(),
          ">=" => $leftSide->getValue() >= $rightSide->getValue(),
          "<" => $leftSide->getValue() < $rightSide->getValue(),
          "<=" => $leftSide->getValue() <= $rightSide->getValue(),
          default => throw new \RuntimeException(
            "Unknown comparison operator: $operator"
          ),
        }
      );
    }

    if (!$this->checkTypes($leftSide, $rightSide, "number")) {
      throw new \RuntimeException(
        "Can't use math expressions with {$leftSide->getType()} and {$rightSide->getType()}"
      );
    }

    return $this->evaluateNumericBinaryExpression(
      Values::createNumber($leftSide->getValue()),
      Values::createNumber($rightSide->getValue()),
      $operator
    );
  }

  private function evaluateProgram(
    ProgramStatement $program,
    array $variables
  ): RuntimeValue {
    $lastEvaluated = Values::createNull();
    foreach ($program->getBody() as $statement) {
      $lastEvaluated = $this->evaluate($statement, $variables);
    }
    return $lastEvaluated;
  }

  private function evaluateIdentifier(
    Identifier $identifier,
    array $variables
  ): RuntimeValue {
    $symbol = $identifier->getSymbol();
    if (!array_key_exists($symbol, $variables)) {
      throw new \RuntimeException("$symbol is undefined");
    }

    $value = $variables[$symbol];
    return is_numeric($value)
      ? Values::createNumber((float) $value)
      : (is_string($value)
        ? Values::createString($value)
        : Values::createNull());
  }

  public function evaluate(
    Statement $astNode,
    array $variables = []
  ): RuntimeValue {
    return match ($astNode->getKind()) {
      "NumericLiteral" => Values::createNumber($astNode->getValue()),
      "StringLiteral" => Values::createString($astNode->getValue()),
      "UnaryExpression" => $this->evaluateUnaryExpression($astNode, $variables),
      "BinaryExpression" => $this->evaluateBinaryExpression(
        $astNode,
        $variables
      ),
      "LogicalExpression" => $this->evaluateLogicalExpression(
        $astNode,
        $variables
      ),
      "Program" => $this->evaluateProgram($astNode, $variables),
      "Identifier" => $this->evaluateIdentifier($astNode, $variables),
      default => throw new \RuntimeException(
        "Unexpected Node " . json_encode($astNode)
      ),
    };
  }
}
