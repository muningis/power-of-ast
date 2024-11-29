<?php

namespace Parser\ASTNodes;

class NumericLiteral implements Expression
{
  private string $kind = "NumericLiteral";

  public function __construct(private float $value)
  {
  }

  public function getKind(): string
  {
    return $this->kind;
  }

  public function getValue(): float
  {
    return $this->value;
  }
}
