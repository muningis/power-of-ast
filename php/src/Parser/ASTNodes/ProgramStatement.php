<?php

namespace Parser\ASTNodes;

class ProgramStatement implements Statement
{
  private string $kind = "Program";
  /** @var Statement[] */
  private array $body;

  public function __construct(array $body = [])
  {
    $this->body = $body;
  }

  public function getKind(): string
  {
    return $this->kind;
  }

  public function getBody(): array
  {
    return $this->body;
  }

  public function addStatement(Statement $statement): void
  {
    $this->body[] = $statement;
  }
}
