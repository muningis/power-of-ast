<?php

namespace Tests\Parser;

use PHPUnit\Framework\TestCase;
use Parser\Frontend\Lexer;
use Parser\Frontend\ETokenType;

class LexerTest extends TestCase
{
  public function testComplexExpression(): void
  {
    $expression = 'FOO === "BAR" && (SUM === 5 || REGULAR_SUM === 50)';
    $results = array_map(
      fn($token) => $token->toArray(),
      Lexer::tokenize($expression)
    );

    $this->assertEquals(
      [
        ["type" => ETokenType::Identifier, "value" => "FOO"],
        ["type" => ETokenType::Comparison, "value" => "==="],
        ["type" => ETokenType::String, "value" => "BAR"],
        ["type" => ETokenType::BinaryOperator, "value" => "&&"],
        ["type" => ETokenType::OpenParenthesis, "value" => "("],
        ["type" => ETokenType::Identifier, "value" => "SUM"],
        ["type" => ETokenType::Comparison, "value" => "==="],
        ["type" => ETokenType::Number, "value" => "5"],
        ["type" => ETokenType::BinaryOperator, "value" => "||"],
        ["type" => ETokenType::Identifier, "value" => "REGULAR_SUM"],
        ["type" => ETokenType::Comparison, "value" => "==="],
        ["type" => ETokenType::Number, "value" => "50"],
        ["type" => ETokenType::CloseParenthesis, "value" => ")"],
        ["type" => ETokenType::EndOfExpression, "value" => "EOE"],
      ],
      $results
    );
  }

  public function testComplexExpressionWithComparisons(): void
  {
    $expression =
      'REGULAR_SUM === 50 && (SUM === 5 || FOO === "BAR") && SOMETHING > 5 && SOMETHAT < 9';
    $results = array_map(
      fn($token) => $token->toArray(),
      Lexer::tokenize($expression)
    );

    $this->assertEquals(
      [
        ["type" => ETokenType::Identifier, "value" => "REGULAR_SUM"],
        ["type" => ETokenType::Comparison, "value" => "==="],
        ["type" => ETokenType::Number, "value" => "50"],
        ["type" => ETokenType::BinaryOperator, "value" => "&&"],
        ["type" => ETokenType::OpenParenthesis, "value" => "("],
        ["type" => ETokenType::Identifier, "value" => "SUM"],
        ["type" => ETokenType::Comparison, "value" => "==="],
        ["type" => ETokenType::Number, "value" => "5"],
        ["type" => ETokenType::BinaryOperator, "value" => "||"],
        ["type" => ETokenType::Identifier, "value" => "FOO"],
        ["type" => ETokenType::Comparison, "value" => "==="],
        ["type" => ETokenType::String, "value" => "BAR"],
        ["type" => ETokenType::CloseParenthesis, "value" => ")"],
        ["type" => ETokenType::BinaryOperator, "value" => "&&"],
        ["type" => ETokenType::Identifier, "value" => "SOMETHING"],
        ["type" => ETokenType::MoreThan, "value" => ">"],
        ["type" => ETokenType::Number, "value" => "5"],
        ["type" => ETokenType::BinaryOperator, "value" => "&&"],
        ["type" => ETokenType::Identifier, "value" => "SOMETHAT"],
        ["type" => ETokenType::LessThan, "value" => "<"],
        ["type" => ETokenType::Number, "value" => "9"],
        ["type" => ETokenType::EndOfExpression, "value" => "EOE"],
      ],
      $results
    );
  }

  public function testExpressionWithArithmetic(): void
  {
    $expression = "15 <= (2 * FOO)";
    $results = array_map(
      fn($token) => $token->toArray(),
      Lexer::tokenize($expression)
    );

    $this->assertEquals(
      [
        ["type" => ETokenType::Number, "value" => "15"],
        ["type" => ETokenType::LessThan, "value" => "<="],
        ["type" => ETokenType::OpenParenthesis, "value" => "("],
        ["type" => ETokenType::Number, "value" => "2"],
        ["type" => ETokenType::Operator, "value" => "*"],
        ["type" => ETokenType::Identifier, "value" => "FOO"],
        ["type" => ETokenType::CloseParenthesis, "value" => ")"],
        ["type" => ETokenType::EndOfExpression, "value" => "EOE"],
      ],
      $results
    );
  }
}
