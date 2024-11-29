<?php

namespace Parser\Values;

/**
 * Represents a boolean value in the interpreter
 */
class BoolValue implements RuntimeValue
{
  private string $type = "boolean";
  private bool $value;

  public function __construct(bool $value)
  {
    $this->value = $value;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getValue(): bool
  {
    return $this->value;
  }
}
