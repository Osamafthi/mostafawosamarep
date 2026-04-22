<?php

namespace App\Jobs;

use App\Mail\OrderReceivedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderNotificationToAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::query()->with('items')->find($this->orderId);
        $adminTo = config('mail.admin_to');

        if (! $order || empty($adminTo)) {
            return;
        }

        Mail::to($adminTo)->send(new OrderReceivedMail($order));
    }
}
