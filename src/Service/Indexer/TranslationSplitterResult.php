<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

class TranslationSplitterResult
{
    /**
     * @param string[] $bases
     * @param array<string,array<string>> $translations
     */
    public function __construct(
        private readonly array $bases,
        private readonly array $translations
    ) {
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        $locales = array_keys($this->translations);
        sort($locales);
        return $locales;
    }

    /**
     * @return string[]
     */
    public function getBases(): array
    {
        return $this->bases;
    }

    /**
     * @return string[]
     */
    public function getTranslations(string $locale): array
    {
        return $this->translations[$locale] ?? [];
    }
}
