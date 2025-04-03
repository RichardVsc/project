<?php

namespace App\Http\Middleware;

use App\Exceptions\TransferException;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TransferLockMiddleware
{
    public function handle($request, Closure $next)
    {
        $payer = Auth::user();
        $lockKey = 'user:transfer:lock:' . $payer->id;

        $lock = Cache::lock($lockKey);
        if (!$lock->get()) {
            throw new TransferException(
                'Outro processo está realizando uma transferência para este usuário. Tente novamente em instantes.',
                429
            );
        }

        try {
            return $next($request);
        } finally {
            $lock->release();
        }
    }
}
