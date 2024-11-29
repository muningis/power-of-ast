<?php

namespace Parser\Values;
/**
 * Base interface for all runtime values
 */
interface RuntimeValue
{
  public function getType(): string;
  public function getValue(): mixed;
}
