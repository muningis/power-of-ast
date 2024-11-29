<?php

namespace Parser\Values;

/**
 * Represents a number value in the interpreter
 */
class NumberValue implements RuntimeValue
{
  private string $type = "number";
  private float $value;

  public function __construct(float $value)
  {
    $this->value = $value;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getValue(): float
  {
    return $this->value;
  }
}
