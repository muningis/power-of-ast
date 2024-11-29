<?php

namespace Parser\Values;

/**
 * Represents a string value in the interpreter
 */
class StringValue implements RuntimeValue
{
  private string $type = "string";
  private string $value;

  public function __construct(string $value)
  {
    $this->value = $value;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getValue(): string
  {
    return $this->value;
  }
}
