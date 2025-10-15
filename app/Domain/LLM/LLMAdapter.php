<?php

namespace App\Domain\LLM;

interface LLMAdapter
{
    /**
     * Grammar/style correction.
     * @return array{output_text:string, usage?:array|null}
     */
    public function correct(string $text, ?string $tone = null, ?int $maxLength = null): array;

    /**
     * Translation.
     * @return array{output_text:string, usage?:array|null}
     */
    public function translate(string $text, string $targetLang, ?string $sourceLang = null, ?string $tone = null, ?int $maxLength = null): array;
}