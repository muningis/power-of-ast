<?php

namespace Parser\Values;

/**
 * Represents a null value in the interpreter
 */
class NullValue implements RuntimeValue
{
  private string $type = "null";
  private ?string $value = null;

  public function getType(): string
  {
    return $this->type;
  }

  public function getValue(): mixed
  {
    return $this->value;
  }
}
