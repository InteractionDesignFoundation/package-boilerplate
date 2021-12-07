<?php

declare(strict_types=1);

namespace IxDFCodingStandard\Sniffs\Files;

use BadMethodCallException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class NonExistingTemplateSniff implements Sniff
{
    private const INVALID_METHOD_CALL = 'Invalid method call';

    private const INCLUDE_BLADE_DIRECTIVE = '/@(include|includeIf|includeWhen|component|extends)\(\'([a-zA-Z_\-.]+?)\'/'; // @includeIf

    /** @var array<string, bool> */
    private array $checkedFiles = [];

    /** @inheritDoc */
    public function register(): array
    {
        return [\T_OPEN_TAG, \T_INLINE_HTML];
    }

    /** @inheritDoc */
    public function process(File $phpcsFile, $stackPtr): int
    {
        $tokens = $phpcsFile->getTokens();

        $filename = $phpcsFile->getFilename();

        $hash = md5($filename);
        if (($this->checkedFiles[$hash] ?? false) || $filename === 'STDIN' || strpos($filename, '.stub') !== false) {
            return 0;
        }
        $this->checkedFiles[$hash] = true;

        foreach ($tokens as $position => $token) {
            if ($this->isBladeIncludeDirective($token['content'])) {
                $this->validateTemplateName($this->getBladeTemplateName($token['content']), $phpcsFile, $position);
            } elseif ($this->isViewFacade($tokens, $position)) {
                $this->validateTemplateName($this->getViewFacadeTemplateName($tokens, $position), $phpcsFile, $position);
            } elseif ($this->isViewFunctionFactory($tokens, $position)) {
                $this->validateTemplateName($this->getViewFunctionFactoryTemplateName($tokens, $position), $phpcsFile, $position);
            } elseif ($this->isViewFunction($tokens, $position)) {
                $this->validateTemplateName($this->getViewFunctionTemplateName($tokens, $position), $phpcsFile, $position);
            }
        }

        return 0;
    }

    private function isBladeIncludeDirective(string $tokenContent): bool
    {
        return preg_match(self::INCLUDE_BLADE_DIRECTIVE, $tokenContent) === 1;
    }

    private function getBladeTemplateName(string $tokenContent): string
    {
        if (!$this->isBladeIncludeDirective($tokenContent)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        $matches = [];
        preg_match(self::INCLUDE_BLADE_DIRECTIVE, $tokenContent, $matches);

        if (!isset($matches[2])) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return (string) $matches[2];
    }

    private function templateIsMissing(string $templateName): bool
    {
        return !file_exists($this->getTemplatePath($templateName));
    }

    private function getTemplatePath(string $name): string
    {
        return dirname(__DIR__, 4).'/resources/views/'.str_replace('.', '/', $name).'.blade.php';
    }

    private function reportMissingTemplate(File $phpcsFile, int $stackPtr, string $templateName): void
    {
        $phpcsFile->addWarning(
            'Template "%s" (%s) does not exist in "%s"',
            $stackPtr,
            'TemplateNotFound',
            [$templateName, $this->getTemplatePath($templateName), $phpcsFile->getFilename()]
        );
    }

    private function validateTemplateName(string $templateName, File $phpcsFile, int $stackPtr): void
    {
        if (rtrim($templateName, '-_.') !== $templateName) {
            return;
        }

        if (strpos($templateName, '$') !== false) {
            return;
        }

        if ($this->templateIsMissing($templateName)) {
            $this->reportMissingTemplate($phpcsFile, $stackPtr, $templateName);
        }
    }

    private function isViewFacade(array $tokens, int $position): bool
    {
        return isset($tokens[$position + 2]) &&
            ($tokens[$position]['content'] === 'View' || $tokens[$position]['content'] === 'ViewFacade') &&
            $tokens[$position + 1]['type'] === 'T_DOUBLE_COLON' &&
            $tokens[$position + 2]['content'] === 'make' &&
            $tokens[$position + 3]['type'] === 'T_CONSTANT_ENCAPSED_STRING';
    }

    private function getViewFacadeTemplateName(array $tokens, int $position): string
    {
        if (! $this->isViewFacade($tokens, $position)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }
        $lookupPosition = $position + 4;
        do {
            if ($tokens[$lookupPosition]['type'] !== 'T_WHITESPACE') {
                return trim($tokens[$lookupPosition]['content'], '\'');
            }
            $lookupPosition++;
        } while (isset($tokens[$lookupPosition]) && $lookupPosition < $position + 14);

        throw new BadMethodCallException('Unable to find the template name');
    }

    private function isViewFunctionFactory(array $tokens, int $position): bool
    {
        return isset($tokens[$position + 4]) &&
            $tokens[$position]['content'] === 'view' &&
            $tokens[$position + 1]['content'] === '(' &&
            $tokens[$position + 2]['content'] === ')' &&
            $tokens[$position + 3]['content'] === '->' &&
            $tokens[$position + 4]['content'] === 'make' &&
            $tokens[$position + 6]['type'] !== 'T_VARIABLE';
    }

    private function getViewFunctionFactoryTemplateName(array $tokens, int $position): string
    {
        if (! $this->isViewFunctionFactory($tokens, $position)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }
        $lookupPosition = $position + 6;
        do {
            if ($tokens[$lookupPosition]['type'] !== 'T_WHITESPACE') {
                return trim($tokens[$lookupPosition]['content'], '\'');
            }
            $lookupPosition++;
        } while (isset($tokens[$lookupPosition]) && $lookupPosition < $position + 16);

        throw new BadMethodCallException('Unable to find the template name');
    }

    private function isViewFunction(array $tokens, int $position): bool
    {
        return isset($tokens[$position - 1], $tokens[$position + 4]) &&
            $tokens[$position - 1]['type'] === 'T_WHITESPACE' &&
            $tokens[$position]['content'] === 'view' &&
            $tokens[$position + 1]['content'] === '(' &&
            $tokens[$position + 2]['type'] === 'T_CONSTANT_ENCAPSED_STRING';
    }

    private function getViewFunctionTemplateName(array $tokens, int $position): string
    {
        if (! $this->isViewFunction($tokens, $position)) {
            throw new BadMethodCallException(self::INVALID_METHOD_CALL);
        }

        return trim($tokens[$position + 2]['content'], '\'');
    }
}
