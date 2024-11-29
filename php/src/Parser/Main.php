<?php

namespace Parser;

require_once __DIR__ . "/../../vendor/autoload.php";

use Parser\Frontend\Parser;
use Parser\Backend\Interpreter;

function Main()
{
  $expression = 'FOO === "BAR" && (SUM === 5 || REGULAR_SUM === 50)';
  $variables = [
    "FOO" => "BAR",
    "SUM" => 5,
    "REGULAR_SUM" => 50,
  ];

  $parser = new Parser();
  $interpreter = new Interpreter();

  $ast = $parser->produceAST($expression);
  $result = $interpreter->evaluate($ast, $variables);

  var_dump($result->getValue());
}

Main();
