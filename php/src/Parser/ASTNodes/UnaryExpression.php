<?php

namespace Parser\ASTNodes;

class UnaryExpression implements Expression
{
  private string $kind = "UnaryExpression";

  public function __construct(
    private string $operator,
    private bool $prefix,
    private Expression $argument
  ) {
  }

  public function getKind(): string
  {
    return $this->kind;
  }

  public function getOperator(): string
  {
    return $this->operator;
  }

  public function isPrefix(): bool
  {
    return $this->prefix;
  }

  public function getArgument(): Expression
  {
    return $this->argument;
  }
}
