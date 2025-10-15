<?php

namespace App\Infrastructure\LLM;

use App\Domain\LLM\LLMAdapter;
use Illuminate\Support\Facades\Http;

/**
 * LLM 어댑터
 * - dummy=true: 하드코딩 example 모드
 *   1) "안녕하세요." -> "Hello"
 *   2) "공백 정리 테스트" -> "공백정리테스트"
 *   그 외 입력은 400(BAD_REQUEST)
 * - dummy=false: OpenAI Responses API 호출 (실구현)
 */
class OpenAIAdapter implements LLMAdapter
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string  $base = 'https://api.openai.com/v1',
        private readonly string  $model = 'gpt-4o-mini',
        private readonly int     $timeout = 15,
        private readonly bool    $dummy = true, // ← 스위치
    ) {}

    /** @return array{output_text:string, usage?:array|null} */
    public function correct(string $text, ?string $tone = null, ?int $maxLength = null): array
    {
        if ($this->dummy) {
            return $this->exampleMode($text);
        }
        
        $system = "You are a writing assistant. Correct grammar, spelling, and style in the user's language. ".
                  "Preserve meaning. Return plain text only.";
        if ($tone === 'formal') $system .= " Use a formal tone.";
        if ($tone === 'casual') $system  .= " Use a casual tone.";
        if ($maxLength)         $system  .= " Keep the output within {$maxLength} characters if reasonable.";

        return $this->callLLM($system, $text);
    }

    /** @return array{output_text:string, usage?:array|null} */
    public function translate(string $text, string $targetLang, ?string $sourceLang = null, ?string $tone = null, ?int $maxLength = null): array
    {
        if ($this->dummy) {
            return $this->exampleMode($text);
        }
        
        $src = $sourceLang ? strtoupper($sourceLang) : "auto-detect";
        $system = "You are a translator. Translate from {$src} to ".strtoupper($targetLang).". ".
                  "Preserve meaning and naturalness. Return plain text only.";
        if ($tone === 'formal') $system .= " Use a formal tone.";
        if ($tone === 'casual') $system  .= " Use a casual tone.";
        if ($maxLength)         $system  .= " Keep the output within {$maxLength} characters if reasonable.";

        return $this->callLLM($system, $text);
    }

    /** example 모드: 고정 입력 2개만 처리, 나머지는 400 */
    private function exampleMode(string $text): array
    {
        if ($text === '안녕하세요.') {
            return ['output_text' => 'Hello', 'usage' => null];
        }
        if ($text === '공백 정리 테스트') {
            return ['output_text' => '공백정리테스트', 'usage' => null];
        }
        throw new \DomainException('Unsupported example input', 400);
    }

    /** 실제 LLM 호출 (TODO 지점 예시) */
    private function callLLM(string $system, string $user): array
    {
        $this->assertConfigured();

        try {
            $resp = Http::withToken($this->apiKey)
                ->baseUrl($this->base)
                ->timeout($this->timeout)
                ->post('/responses', [
                    'model' => $this->model,
                    'input' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $user],
                    ],
                ]);

            if ($resp->failed()) {
                $msg = $resp->json('error.message') ?? $resp->body();
                throw new \RuntimeException("LLM HTTP {$resp->status()}: ".$msg);
            }

            // Responses API: text 추출 경로
            $out = $resp->json('output.0.content.0.text')
                ?? $resp->json('choices.0.message.content') // (호환용)
                ?? '';

            return [
                'output_text' => (string) $out,
                'usage'       => $resp->json('usage') ?? null,
            ];
        } catch (ConnectionException $e) {
            throw new \RuntimeException("LLM connection failed: ".$e->getMessage(), 0, $e);
        }
    }

    private function assertConfigured(): void
    {
        if (empty($this->apiKey)) {
            abort(501, 'LLM not configured');
        }
    }

    private function notImplemented(string $method): array
    {
        abort(501, "LLM {$method}() not implemented yet");
    }
}
