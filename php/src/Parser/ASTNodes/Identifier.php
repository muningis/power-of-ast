<?php

namespace Parser\ASTNodes;

class Identifier implements Expression
{
  private string $kind = "Identifier";

  public function __construct(private string $symbol)
  {
  }

  public function getKind(): string
  {
    return $this->kind;
  }

  public function getSymbol(): string
  {
    return $this->symbol;
  }
}
