<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TextService;

class TextController extends Controller
{
    public function __construct(private readonly TextService $service) {}

    public function transform(Request $request)
    {
        $data = $request->validate([
            'mode'        => 'required|in:correct,translate',
            'text'        => 'required|string|max:4000',
            'source_lang' => 'nullable|string|max:8',
            'target_lang' => 'required_if:mode,translate|nullable|string|max:8',
            'tone'        => 'nullable|in:neutral,formal,casual',
            'max_length'  => 'nullable|integer|min:1|max:10000',
        ]);

        $apiKeyId = (int) $request->attributes->get('api_key_id');

        try {
            return response()->json($this->service->transform($apiKeyId, $data));
        } catch (\DomainException $e) {
            // echo $e;
            return response()->json([
                'error' => ['code' => 'BAD_REQUEST', 'message' => $e->getMessage()]
            ], $e->getCode() ?: 400);
        } catch (\Throwable $e) {
            // echo $e;
            return response()->json([
                'error' => ['code' => 'INTERNAL', 'message' => 'Unexpected error']
            ], 500);
        }
    }
}
