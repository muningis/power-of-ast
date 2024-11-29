<?php

namespace Parser\Frontend;

use InvalidArgumentException;

class Token
{
  public function __construct(private string $value, private ETokenType $type)
  {
  }

  public function getType(): ETokenType
  {
    return $this->type;
  }

  public function getValue(): string
  {
    return $this->value;
  }
}

class Lexer
{
  private const VARIABLE_FIRST_CHARACTER = '/^[A-Za-z_$]/';
  private const VARIABLE_CHARACTERS = '/^[A-Za-z0-9_$]/';
  private const NUMBER = "/^[0-9]/";
  private const OPERATOR = "/^[+\-\/*%]/";
  private const QUOTE = '/^["\']$/';

  private static function createToken(string $value, ETokenType $type): Token
  {
    return new Token($value, $type);
  }

  public function tokenize(string $expression): array
  {
    $tokens = [];
    $cursor = 0;
    $len = strlen($expression);

    while ($cursor < $len) {
      $char = $expression[$cursor];

      // Identifier
      if (preg_match(self::VARIABLE_FIRST_CHARACTER, $char)) {
        $symbol = $char;
        $cursor++;
        while (
          $cursor < $len &&
          preg_match(self::VARIABLE_CHARACTERS, $expression[$cursor])
        ) {
          $symbol .= $expression[$cursor];
          $cursor++;
        }
        $tokens[] = self::createToken($symbol, ETokenType::Identifier);
        continue;
      }

      // Parentheses
      if ($char === "(") {
        $tokens[] = self::createToken($char, ETokenType::OpenParenthesis);
        $cursor++;
        continue;
      }
      if ($char === ")") {
        $tokens[] = self::createToken($char, ETokenType::CloseParenthesis);
        $cursor++;
        continue;
      }

      // Unary operator
      if (
        $char === "-" &&
        ($cursor + 1 < $len &&
          ($expression[$cursor + 1] === "(" ||
            preg_match(self::NUMBER, $expression[$cursor + 1])))
      ) {
        $tokens[] = self::createToken($char, ETokenType::Unary);
        $cursor++;
        continue;
      }

      // Arithmetic operators
      if (preg_match(self::OPERATOR, $char)) {
        $tokens[] = self::createToken($char, ETokenType::Operator);
        $cursor++;
        continue;
      }

      // String literals
      if (preg_match(self::QUOTE, $char)) {
        $quote = $char;
        $text = "";
        while ($expression[++$cursor] !== $quote) {
          $text .= $expression[$cursor];
        }
        $tokens[] = self::createToken($text, ETokenType::String);
        $cursor++;
        continue;
      }

      // Numbers
      if (preg_match(self::NUMBER, $char)) {
        $number = $char;
        $foundDecimalSeparator = false;
        while (
          $cursor + 1 < $len &&
          (preg_match(self::NUMBER, $expression[$cursor + 1]) ||
            (!$foundDecimalSeparator && $expression[$cursor + 1] === "."))
        ) {
          $cursor++;
          if ($expression[$cursor] === ".") {
            $foundDecimalSeparator = true;
          }
          $number .= $expression[$cursor];
        }
        $tokens[] = self::createToken($number, ETokenType::Number);
        $cursor++;
        continue;
      }

      // Logical operators
      if (
        $char === "&" &&
        isset($expression[$cursor + 1]) &&
        $expression[$cursor + 1] === "&"
      ) {
        $tokens[] = self::createToken("&&", ETokenType::BinaryOperator);
        $cursor += 2;
        continue;
      }
      if (
        $char === "|" &&
        isset($expression[$cursor + 1]) &&
        $expression[$cursor + 1] === "|"
      ) {
        $tokens[] = self::createToken("||", ETokenType::BinaryOperator);
        $cursor += 2;
        continue;
      }

      // Comparison operators
      if ($char === "!" && substr($expression, $cursor, 3) === "!==") {
        $tokens[] = self::createToken("!==", ETokenType::Comparison);
        $cursor += 3;
        continue;
      }
      if ($char === "=" && substr($expression, $cursor, 3) === "===") {
        $tokens[] = self::createToken("===", ETokenType::Comparison);
        $cursor += 3;
        continue;
      }

      // Greater/Less than operators
      if ($char === ">") {
        $moreThanOrEqual =
          isset($expression[$cursor + 1]) && $expression[$cursor + 1] === "=";
        $tokens[] = self::createToken(
          $moreThanOrEqual ? ">=" : ">",
          ETokenType::MoreThan
        );
        $cursor += $moreThanOrEqual ? 2 : 1;
        continue;
      }
      if ($char === "<") {
        $lessThanOrEqual =
          isset($expression[$cursor + 1]) && $expression[$cursor + 1] === "=";
        $tokens[] = self::createToken(
          $lessThanOrEqual ? "<=" : "<",
          ETokenType::LessThan
        );
        $cursor += $lessThanOrEqual ? 2 : 1;
        continue;
      }

      // Whitespace
      if ($char === " ") {
        $cursor++;
        continue;
      }

      throw new InvalidArgumentException(
        sprintf("Unrecognized symbol, %s at position %d", $char, $cursor)
      );
    }

    $tokens[] = self::createToken("EOE", ETokenType::EndOfExpression);
    return $tokens;
  }
}
