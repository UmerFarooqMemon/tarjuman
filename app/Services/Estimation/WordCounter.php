<?php

namespace App\Services\Estimation;

class WordCounter
{
    /**
     * Count source words using Unicode-aware whitespace splitting.
     * Ignores dotted lines, punctuation-only tokens, and decorative OCR junk.
     */
    public function count(string $text): int
    {
        return count($this->countableTokens($text));
    }

    /**
     * Count tokens that belong to a writing script (ignores other-script OCR noise).
     *
     * @param  bool  $includeDigitOnly  Attribute bare numbers to this script pass (use once).
     */
    public function countScript(string $text, string $script, bool $includeDigitOnly = false): int
    {
        $count = 0;

        foreach ($this->rawTokens($text) as $part) {
            if (! $this->isCountableToken($part)) {
                continue;
            }

            if ($this->tokenMatchesScript($part, $script)) {
                $count++;

                continue;
            }

            if ($includeDigitOnly && preg_match('/^\d+[.,\/\-]?\d*$/u', $part)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return list<string>
     */
    public function countableTokens(string $text): array
    {
        $tokens = [];

        foreach ($this->rawTokens($text) as $part) {
            if ($this->isCountableToken($part)) {
                $tokens[] = $part;
            }
        }

        return $tokens;
    }

    /**
     * Reject form lines, logo noise, and punctuation-only OCR artifacts.
     */
    public function isCountableToken(string $token): bool
    {
        $token = trim($token);

        if ($token === '' || $token === 'nan') {
            return false;
        }

        // Pure punctuation / decorative strokes (......, ----, ____, •••).
        if (preg_match('/^[\p{P}\p{S}\._\-=~*•·‧∙⋯…\|\/\\\\]+$/u', $token)) {
            return false;
        }

        $lettersAndDigits = preg_replace('/[^\p{L}\p{N}]/u', '', $token) ?? '';

        if ($lettersAndDigits === '') {
            return false;
        }

        // Mostly decorative with a stray character: "a........", "..x.."
        $visibleLen = mb_strlen($token);
        if ($visibleLen >= 4 && (mb_strlen($lettersAndDigits) / $visibleLen) < 0.35) {
            return false;
        }

        // Single isolated symbol-like leftovers that aren't real words.
        if (mb_strlen($lettersAndDigits) === 1 && preg_match('/^[\p{P}\p{S}]/u', $token)) {
            return false;
        }

        return true;
    }

    public function tokenMatchesScript(string $token, string $script): bool
    {
        return match ($script) {
            'arabic' => (bool) preg_match(
                '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u',
                $token
            ),
            'hebrew' => (bool) preg_match('/[\x{0590}-\x{05FF}]/u', $token),
            'cjk' => (bool) preg_match(
                '/[\x{3040}-\x{30FF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}\x{AC00}-\x{D7AF}]/u',
                $token
            ),
            'latin' => (bool) preg_match('/\p{Latin}/u', $token),
            default => true,
        };
    }

    /**
     * @return list<string>
     */
    protected function rawTokens(string $text): array
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($normalized === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($parts) ? $parts : [];
    }
}
