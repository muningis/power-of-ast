<?php

namespace Parser\Frontend;

use InvalidArgumentException;
use RuntimeException;

use Parser\ASTNodes\{
  Statement,
  Expression,
  ProgramStatement,
  BinaryExpression,
  LogicalExpression,
  UnaryExpression,
  Identifier,
  NumericLiteral,
  StringLiteral
};

class Parser
{
  /** @var Token[] */
  private array $tokens = [];

  private function notEoe(): bool
  {
    return isset($this->tokens[0]) &&
      $this->tokens[0]->getType() !== ETokenType::EndOfExpression;
  }

  private function at(): ?Token
  {
    return $this->tokens[0] ?? null;
  }

  private function next(
    ?int $expectedToken = null,
    string $errorMessage = "Unexpected token"
  ): Token {
    $token = $this->tokens[0] ?? null;
    if ($expectedToken !== null && $token->getType() !== $expectedToken) {
      throw new InvalidArgumentException($errorMessage);
    }
    return array_shift($this->tokens);
  }

  private function expect(
    ETokenType $type,
    string $errorMessage = "Unexpected token"
  ): void {
    $prev = array_shift($this->tokens);
    if (!$prev || $prev->getType() !== $type) {
      throw new RuntimeException(
        sprintf(
          "Parser Error:\n %s, found %s, expected %s",
          $errorMessage,
          $prev ? $prev->getType()->name : "null",
          $type->name
        )
      );
    }
  }

  private function parseStatement(): Statement
  {
    return $this->parseExpression();
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
  private function parseExpression(): Expression
  {
    return $this->parseLogicalExpression();
  }

  private const LOGICAL_CHARACTERS = ["&&", "||"];
  private function parseLogicalExpression(): Expression
  {
    $left = $this->parseComparisonExpression();
    while (
      $this->at() &&
      in_array($this->at()->getValue(), self::LOGICAL_CHARACTERS)
    ) {
      $operator = $this->next()->getValue();
      $right = $this->parseComparisonExpression();
      $left = new LogicalExpression($left, $right, $operator);
    }
    return $left;
  }

  private const COMPARISON_CHARACTERS = [">", ">=", "<", "<=", "===", "!=="];
  private function parseComparisonExpression(): Expression
  {
    $left = $this->parseAdditiveExpression();
    while (
      $this->at() &&
      in_array($this->at()->getValue(), self::COMPARISON_CHARACTERS)
    ) {
      $operator = $this->next()->getValue();
      $right = $this->parseAdditiveExpression();
      $left = new BinaryExpression($left, $right, $operator);
    }
    return $left;
  }

  private const MULTIPLICATIVE_CHARACTERS = ["/", "*", "%"];
  private function parseMultiplicativeExpression(): Expression
  {
    $left = $this->parsePrimaryExpression();
    while (
      $this->at() &&
      in_array($this->at()->getValue(), self::MULTIPLICATIVE_CHARACTERS)
    ) {
      $operator = $this->next()->getValue();
      $right = $this->parsePrimaryExpression();
      $left = new BinaryExpression($left, $right, $operator);
    }
    return $left;
  }

  private const ADDITIVE_CHARACTERS = ["-", "+"];
  private function parseAdditiveExpression(): Expression
  {
    $left = $this->parseMultiplicativeExpression();
    while (
      $this->at() &&
      in_array($this->at()->getValue(), self::ADDITIVE_CHARACTERS)
    ) {
      $operator = $this->next()->getValue();
      $right = $this->parseMultiplicativeExpression();
      $left = new BinaryExpression($left, $right, $operator);
    }
    return $left;
  }

  private function parsePrimaryExpression(): Expression
  {
    $token = $this->at();
    if (!$token) {
      throw new RuntimeException("Unexpected end of input");
    }

    switch ($token->getType()) {
      case ETokenType::Unary:
        $operator = $this->at()->getValue();
        $this->next();
        return new UnaryExpression($operator, true, $this->parseExpression());

      case ETokenType::Identifier:
        return new Identifier($this->next()->getValue());

      case ETokenType::Number:
        return new NumericLiteral((float) $this->next()->getValue());

      case ETokenType::String:
        return new StringLiteral($this->next()->getValue());

      case ETokenType::OpenParenthesis:
        $this->next(); // consume OpenParenthesis
        $value = $this->parseExpression();
        $this->expect(
          ETokenType::CloseParenthesis,
          "Unexpected token found inside parenthesis expression. Expected closing parenthesis"
        );
        return $value;

      default:
        throw new RuntimeException(
          sprintf(
            "Unexpected token found during parse - %s",
            json_encode($this->at())
          )
        );
    }
  }

  public function produceAST(string $expression): ProgramStatement
  {
    $lexer = new Lexer();
    $this->tokens = $lexer->tokenize($expression);

    $program = new ProgramStatement([]);

    while ($this->notEoe()) {
      $program->addStatement($this->parseStatement());
    }

    return $program;
  }
}
