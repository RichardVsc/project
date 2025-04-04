<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogTransferRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Transfer Request', [
            'user_id' => $request->user()->id ?? null,
            'recipient_id' => $request->input('recipient_id'),
            'amount' => $request->input('amount'),
            'ip' => $request->ip(),
        ]);

        $response = $next($request);

        Log::info('Transfer Response', [
            'status' => $response->status(),
            'content' => $response->getContent(),
        ]);

        return $response;
    }
}
