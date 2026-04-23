<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatches Laravel's built-in VerifyEmail notification to a customer.
 *
 * Runs on the queue so the register / resend endpoints return
 * immediately without waiting on SMTP. Matches the existing pattern
 * of SendOrderConfirmationToCustomer.
 */
class SendCustomerEmailVerification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $customerId)
    {
    }

    public function handle(): void
    {
        $customer = Customer::query()->find($this->customerId);

        if (! $customer || $customer->hasVerifiedEmail()) {
            return;
        }

        $customer->sendEmailVerificationNotification();
    }
}
