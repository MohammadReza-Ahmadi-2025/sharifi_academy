<?php

namespace App\Support\Search;

/**
 * Meilisearch does not tolerate typos on the first letter of a word.
 * Expand queries with common Persian/Arabic keyboard neighbors so
 * e.g. «گتیا» still finds «کتیا».
 */
class PersianSearchQuery
{
    /**
     * Bidirectional first-letter (and whole-string) neighbor swaps.
     *
     * @var array<string, list<string>>
     */
    private const NEIGHBORS = [
        'ک' => ['گ', 'ك'],
        'گ' => ['ک', 'ك'],
        'ك' => ['ک', 'گ'],
        'ی' => ['ي', 'ئ'],
        'ي' => ['ی'],
        'ه' => ['ة'],
        'ة' => ['ه'],
        'ز' => ['ذ', 'ض', 'ظ'],
        'ذ' => ['ز'],
        'ض' => ['ز'],
        'ظ' => ['ز'],
        'ت' => ['ط'],
        'ط' => ['ت'],
        'س' => ['ص', 'ث'],
        'ص' => ['س'],
        'ث' => ['س'],
        'ا' => ['آ', 'أ', 'إ'],
        'آ' => ['ا'],
        'أ' => ['ا'],
        'إ' => ['ا'],
    ];

    /**
     * @return list<string>
     */
    public function variants(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }

        $variants = [$q];

        foreach (preg_split('/\s+/u', $q) ?: [] as $word) {
            if ($word === '' || mb_strlen($word) < 2) {
                continue;
            }

            $first = mb_substr($word, 0, 1);
            $rest = mb_substr($word, 1);
            foreach (self::NEIGHBORS[$first] ?? [] as $swap) {
                $variants[] = $this->replaceWord($q, $word, $swap.$rest);
            }
        }

        // Whole-query Arabic/Persian letter normalization (not first-letter-only).
        $normalized = $this->normalizeLetters($q);
        if ($normalized !== $q) {
            $variants[] = $normalized;
        }

        return array_values(array_unique($variants));
    }

    public function normalizeLetters(string $q): string
    {
        return strtr($q, [
            'ك' => 'ک',
            'ي' => 'ی',
            'ة' => 'ه',
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ؤ' => 'و',
            'ئ' => 'ی',
        ]);
    }

    private function replaceWord(string $haystack, string $word, string $replacement): string
    {
        $parts = preg_split('/\s+/u', $haystack) ?: [];
        foreach ($parts as $i => $part) {
            if ($part === $word) {
                $parts[$i] = $replacement;

                break;
            }
        }

        return implode(' ', $parts);
    }
}
