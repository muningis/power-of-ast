<?php

namespace Tests\Parser;

use PHPUnit\Framework\TestCase;
use Parser\Frontend\Parser;

class ParserTest extends TestCase
{
  private Parser $parser;

  protected function setUp(): void
  {
    $this->parser = new Parser();
  }

  public function testParsesSimpleExpression(): void
  {
    $ast = $this->parser->produceAST("42");

    $this->assertEquals("Program", $ast->getKind());
    $this->assertCount(1, $ast->getBody());

    $expr = $ast->getBody()[0];
    $this->assertEquals("NumericLiteral", $expr->getKind());
    $this->assertEquals(42, $expr->getValue());
  }

  public function testParsesBinaryExpression(): void
  {
    $ast = $this->parser->produceAST("1 + 2");

    $this->assertEquals("Program", $ast->getKind());
    $this->assertCount(1, $ast->getBody());

    $expr = $ast->getBody()[0];
    $this->assertEquals("BinaryExpression", $expr->getKind());
    $this->assertEquals("+", $expr->getOperator());

    $left = $expr->getLeft();
    $this->assertEquals("NumericLiteral", $left->getKind());
    $this->assertEquals(1, $left->getValue());

    $right = $expr->getRight();
    $this->assertEquals("NumericLiteral", $right->getKind());
    $this->assertEquals(2, $right->getValue());
  }

  public function testParsesComplexExpression(): void
  {
    $ast = $this->parser->produceAST(
      'FOO === "BAR" && (SUM === 5 || REGULAR_SUM === 50)'
    );

    $this->assertEquals("Program", $ast->getKind());
    $this->assertCount(1, $ast->getBody());

    $expr = $ast->getBody()[0];
    $this->assertEquals("LogicalExpression", $expr->getKind());
    $this->assertEquals("&&", $expr->getOperator());
  }

  public function testParsesUnaryExpression(): void
  {
    $ast = $this->parser->produceAST("-42");

    $this->assertEquals("Program", $ast->getKind());
    $this->assertCount(1, $ast->getBody());

    $expr = $ast->getBody()[0];
    $this->assertEquals("UnaryExpression", $expr->getKind());
    $this->assertEquals("-", $expr->getOperator());
    $this->assertTrue($expr->isPrefix());

    $arg = $expr->getArgument();
    $this->assertEquals("NumericLiteral", $arg->getKind());
    $this->assertEquals(42, $arg->getValue());
  }

  public function testParsesParenthesizedExpression(): void
  {
    $ast = $this->parser->produceAST("(1 + 2) * 3");

    $this->assertEquals("Program", $ast->getKind());
    $this->assertCount(1, $ast->getBody());

    $expr = $ast->getBody()[0];
    $this->assertEquals("BinaryExpression", $expr->getKind());
    $this->assertEquals("*", $expr->getOperator());
  }

  public function testHandlesOperatorPrecedence(): void
  {
    $ast = $this->parser->produceAST("1 + 2 * 3");
    $expr = $ast->getBody()[0];

    $this->assertEquals("BinaryExpression", $expr->getKind());
    $this->assertEquals("+", $expr->getOperator());

    $right = $expr->getRight();
    $this->assertEquals("BinaryExpression", $right->getKind());
    $this->assertEquals("*", $right->getOperator());
  }
}
