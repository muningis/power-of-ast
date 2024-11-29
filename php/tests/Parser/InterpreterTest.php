<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Parser\Backend\Interpreter;
use Parser\Frontend\Parser;
use RuntimeException;

class InterpreterTest extends TestCase
{
  private Interpreter $interpreter;
  private Parser $parser;

  protected function setUp(): void
  {
    $this->interpreter = new Interpreter();
    $this->parser = new Parser();
  }

  public function testThrowErrorOnUndefinedVariable(): void
  {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("FOO is undefined");

    $program = $this->parser->produceAST("FOO === 5");
    $this->interpreter->evaluate($program);
  }

  /**
   * @dataProvider provideInvalidTypeComparisons
   */
  public function testThrowErrorOnInvalidTypeComparisons(
    string $expression
  ): void {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("Can't compare string with number");

    $program = $this->parser->produceAST($expression);
    $this->interpreter->evaluate($program);
  }

  public function provideInvalidTypeComparisons(): array
  {
    return [
      "comparison ===" => ['"FOO" === 5'],
      "comparison >" => ['"FOO" > 5'],
      "comparison >=" => ['"FOO" >= 5'],
      "comparison <" => ['"FOO" < 5'],
      "comparison <=" => ['"FOO" <= 5'],
    ];
  }

  /**
   * @dataProvider provideInvalidArithmeticOperations
   */
  public function testThrowErrorOnInvalidArithmeticOperations(
    string $expression
  ): void {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
      "Can't use math expressions with string and number"
    );

    $program = $this->parser->produceAST($expression);
    $this->interpreter->evaluate($program);
  }

  public function provideInvalidArithmeticOperations(): array
  {
    return [
      "addition" => ['"FOO" + 5'],
      "subtraction" => ['"FOO" - 5'],
      "division" => ['"FOO" / 5'],
      "multiplication" => ['"FOO" * 5'],
      "modulo" => ['"FOO" % 5'],
    ];
  }

  /**
   * @dataProvider provideStringComparisons
   */
  public function testStringComparisons(
    string $expression,
    bool $expected,
    array $variables
  ): void {
    $program = $this->parser->produceAST($expression);
    $result = $this->interpreter->evaluate($program, $variables);
    $this->assertEquals($expected, $result->getValue());
  }

  public function provideStringComparisons(): array
  {
    return [
      "equality true" => [
        'GREETING === "Hello, World!"',
        true,
        ["GREETING" => "Hello, World!"],
      ],
      "equality false" => [
        'GREETING === "Hello, Team!"',
        false,
        ["GREETING" => "Hello, World!"],
      ],
      "inequality true" => [
        'GREETING !== "Hello, Team!"',
        true,
        ["GREETING" => "Hello, World!"],
      ],
    ];
  }

  /**
   * @dataProvider provideNumberComparisons
   */
  public function testNumberComparisons(
    string $expression,
    bool $expected,
    array $variables
  ): void {
    $program = $this->parser->produceAST($expression);
    $result = $this->interpreter->evaluate($program, $variables);
    $this->assertEquals($expected, $result->getValue());
  }

  public function provideNumberComparisons(): array
  {
    return [
      "greater or equal" => ["A >= 100", true, ["A" => 105]],
      "less than" => ["A < 100", false, ["A" => 105]],
    ];
  }

  /**
   * @dataProvider provideArithmeticOperations
   */
  public function testArithmeticOperations(
    string $expression,
    bool $expected,
    array $variables
  ): void {
    $program = $this->parser->produceAST($expression);
    $result = $this->interpreter->evaluate($program, $variables);
    $this->assertEquals($expected, $result->getValue());
  }

  public function provideArithmeticOperations(): array
  {
    return [
      "addition" => ["A === 100 + 5", true, ["A" => 105]],
      "subtraction" => ["A - 20 === 80", true, ["A" => 100]],
    ];
  }

  /**
   * @dataProvider provideComplexExpressions
   */
  public function testComplexExpressions(
    string $expression,
    bool $expected,
    array $variables
  ): void {
    $program = $this->parser->produceAST($expression);
    $result = $this->interpreter->evaluate($program, $variables);
    $this->assertEquals($expected, $result->getValue());
  }

  public function provideComplexExpressions(): array
  {
    return [
      "mixed comparison" => [
        'A >= (B - 100) && C === "HELLO"',
        true,
        ["A" => 105, "B" => 200, "C" => "HELLO"],
      ],
      "division greater than" => [
        "(A - B) / (C + D) > 1",
        true,
        ["A" => 100, "B" => 50, "C" => 25, "D" => 10],
      ],
      "division less than" => [
        "(P + Q) / (R - S) < -2",
        false,
        ["P" => 50, "Q" => 20, "R" => 30, "S" => 10],
      ],
      "complex equality" => [
        "(P * P) / Q === (R + S)",
        true,
        ["P" => 10, "Q" => 2, "R" => 20, "S" => 30],
      ],
    ];
  }
}
