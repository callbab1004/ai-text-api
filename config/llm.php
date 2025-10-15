<?php

return [
    'dummy'   => filter_var(env('LLM_DUMMY', true), FILTER_VALIDATE_BOOLEAN),
    'model'   => env('LLM_MODEL', 'gpt-4o-mini'),
    'base'    => rtrim(env('OPENAI_API_BASE', 'https://api.openai.com/v1'), '/'),
    'api_key' => env('OPENAI_API_KEY'),
    'timeout' => (int) env('LLM_TIMEOUT', 15),
];
