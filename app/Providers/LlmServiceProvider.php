<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\LLM\LLMAdapter;
use App\Infrastructure\LLM\OpenAIAdapter;

class LlmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LLMAdapter::class, function () {
            $cfg = config('llm');
            return new OpenAIAdapter(
                apiKey: $cfg['api_key'],
                base:   $cfg['base'],
                model:  $cfg['model'],
                timeout:$cfg['timeout'],
                dummy:  $cfg['dummy'],
            );
        });
    }
}