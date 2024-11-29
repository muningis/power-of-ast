<?php

namespace Parser\ASTNodes;

interface Statement
{
  public function getKind(): string;
}
