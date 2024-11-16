<?php

declare(strict_types=1);

namespace CustomCSRules;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

class EnumNaming implements FixerInterface
{
  public function getName(): string
  {
    return "PowerOfAST/enum_naming";
  }

  public function getDefinition(): FixerDefinitionInterface
  {
    return new FixerDefinition(
      "Enum names should not start with letter E.",
      []
    );
  }

  public function isCandidate(Tokens $tokens): bool
  {
    // Only process files that contain enums
    return $tokens->isTokenKindFound(T_ENUM);
  }

  public function isRisky(): bool
  {
    return false;
  }

  public function fix(\SplFileInfo $file, Tokens $tokens): void
  {
    try {
      foreach ($tokens as $index => $token) {
        if (!$token->isGivenKind(T_ENUM)) {
          continue;
        }

        $nameIndex = $tokens->getNextMeaningfulToken($index);
        if ($nameIndex === null) {
          continue;
        }

        $nameToken = $tokens[$nameIndex];
        if (!$nameToken->isGivenKind(T_STRING)) {
          continue;
        }

        $enumName = $nameToken->getContent();
        if (strncmp($enumName, "E", 1) === 0) {
          throw new \PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException(
            $this->getName(),
            sprintf('Enum "%s" should not start with letter E', $enumName)
          );
        }
      }
    } catch (\Exception $e) {
      // Log the error for debugging
      error_log(
        sprintf(
          "Error in EnumNaming fixer: %s in file %s",
          $e->getMessage(),
          $file->getPathname()
        )
      );
      throw $e;
    }
  }

  public function getPriority(): int
  {
    return 0;
  }

  public function supports(\SplFileInfo $file): bool
  {
    return true;
  }
}
