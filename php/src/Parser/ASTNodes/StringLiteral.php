<?php

namespace Parser\ASTNodes;

class StringLiteral implements Expression
{
  private string $kind = "StringLiteral";

  public function __construct(private string $value)
  {
  }

  public function getKind(): string
  {
    return $this->kind;
  }

  public function getValue(): string
  {
    return $this->value;
  }
}
