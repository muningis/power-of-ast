<?php

namespace Parser\Values;

/**
 * Factory functions for creating runtime values
 */
class Values
{
  public static function createNull(): NullValue
  {
    return new NullValue();
  }

  public static function createString(string $value): StringValue
  {
    return new StringValue($value);
  }

  public static function createNumber(float $value): NumberValue
  {
    return new NumberValue($value);
  }

  public static function createBool(bool $value): BoolValue
  {
    return new BoolValue($value);
  }
}
