<?php

namespace App\Jobs\Notification;

use App\Exceptions\NotificationFailedException;
use App\Models\Notification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public int $userId;
    public string $message;

    /**
     * Create a new job instance.
     *
     * @param int $userId
     * @param string $message
     */
    public function __construct(int $userId, string $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    /**
     * Execute the job to send a notification.
     *
     * This method creates a notification attempt in the database, sends the
     * notification via an external API, and updates the notification status
     * based on whether the API call was successful or not.
     *
     * @return void
     */
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

                return;
            }

            throw new NotificationFailedException('Falha no envio da notificação');
        } catch (NotificationFailedException $e) {
            $attempt->status = 'failed';
            $attempt->save();
            $this->fail($e);
        } catch (Exception $e) {
            $attempt->status = 'failed';
            $attempt->save();
            $this->fail($e);
        }
    }

    public function retryAfter()
    {
        return 60;
    }
}
