<?php

namespace App\Jobs\Notification;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $message;

    public function __construct(int $userId, string $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function handle()
    {
        $attempt = Notification::create([
            'user_id' => $this->userId,
            'message' => $this->message,
            'status' => 'failed', 
        ]);

        $url = 'https://util.devi.tools/api/v1/notify';

        try {
            $response = Http::post($url, [
                'user_id' => $this->userId,
                'message' => $this->message,
            ]);

            if ($response->successful()) {
                $attempt->status = 'sent';
                $attempt->save();
            } else {
                $attempt->status = 'failed';
                $attempt->save();
            }
        } catch (\Exception $e) {
            $attempt->status = 'failed';
            $attempt->save();
        }
    }
    
    public function retryUntil()
    {
        return now()->addMinutes(3); 
    }
}
