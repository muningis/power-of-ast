<?php

require_once __DIR__ . "/vendor/autoload.php";

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__)
  ->exclude("vendor")
  ->name("*.php");

return (new PhpCsFixer\Config())
  ->setFinder($finder)
  ->registerCustomFixers([new CustomCSRules\EnumNaming()])
  ->setRules([
    "PowerOfAST/enum_naming" => true,
  ]);
