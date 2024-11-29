<?php

namespace Parser\Frontend;

enum ETokenType: int
{
  case Identifier = 0;
  case Number = 1;
  case String = 2;
  case Comparison = 3;
  case LessThan = 4;
  case MoreThan = 5;
  case Unary = 6;
  case OpenParenthesis = 7;
  case CloseParenthesis = 8;
  case Operator = 9;
  case BinaryOperator = 10;
  case EndOfExpression = 11;
}
