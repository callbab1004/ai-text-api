<?php

namespace App\Services;

use App\Domain\LLM\LLMAdapter;
use Illuminate\Support\Facades\DB;

class TextService
{
    public function __construct(private readonly LLMAdapter $llm) {}

    public function transform(int $apiKeyId, array $data): array
    {
        $t0   = microtime(true);
        $mode = $data['mode'];
        $text = (string)($data['text'] ?? '');

        // --- LLM 호출 (더미/스위치 포함 어댑터가 처리) ---
        if ($mode === 'translate') {
            $resp = $this->llm->translate(
                $text,
                $data['target_lang'] ?? 'en',
                $data['source_lang'] ?? null,
                $data['tone'] ?? null,
                $data['max_length'] ?? null,
            );
        } else { // correct
            $resp = $this->llm->correct(
                $text,
                $data['tone'] ?? null,
                $data['max_length'] ?? null,
            );
        }

        $latency   = (int) round((microtime(true) - $t0) * 1000);
        $outText   = (string)($resp['output_text'] ?? '');
        $inLen     = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        $outLen    = function_exists('mb_strlen') ? mb_strlen($outText, 'UTF-8') : strlen($outText);

        // --- 단순 insert (스키마 그대로) ---
        DB::table('requests_log')->insert([
            'api_key_id'   => $apiKeyId,
            'mode'         => $mode,
            'source_lang'  => $data['source_lang'] ?? null,   // translate 아닐 때도 그냥 null 허용
            'target_lang'  => $data['target_lang'] ?? null,
            'input_chars'  => $inLen,
            'output_chars' => $outLen,
            'status'       => 'success',
            'latency_ms'   => $latency,
            'error_message'=> null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // --- 응답 ---
        return [
            'data' => [
                'mode'        => $mode,
                'source_lang' => $data['source_lang'] ?? null,
                'target_lang' => $data['target_lang'] ?? null,
                'input_chars' => $inLen,
                'output_text' => $outText,
                'usage'       => $resp['usage'] ?? null,
            ],
            'meta' => [
                'request_id' => 'req_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(3)),
                'latency_ms' => $latency,
            ],
        ];
    }
}