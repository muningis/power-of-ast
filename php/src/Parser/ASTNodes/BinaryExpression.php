<?php

namespace Parser\ASTNodes;

class BinaryExpression implements Expression
{
  private string $kind = "BinaryExpression";

  public function __construct(
    private Expression $left,
    private Expression $right,
    private string $operator
  ) {
  }

  public function getKind(): string
  {
    return $this->kind;
  }

  public function getLeft(): Expression
  {
    return $this->left;
  }

  public function getRight(): Expression
  {
    return $this->right;
  }

  public function getOperator(): string
  {
    return $this->operator;
  }
}
