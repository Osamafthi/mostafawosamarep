<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\CustomerLoginRequest;
use App\Http\Requests\V1\Auth\CustomerRegisterRequest;
use App\Http\Resources\V1\CustomerResource;
use App\Jobs\SendCustomerEmailVerification;
use App\Models\Customer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function register(CustomerRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'default_shipping_address' => $data['default_shipping_address'] ?? null,
        ]);

        // Queue the verification email so the HTTP response is not
        // blocked by SMTP — requires `php artisan queue:work`.
        SendCustomerEmailVerification::dispatch($customer->id);

        $token = $customer->createToken('customer-api', ['customer'])->plainTextToken;

        return ApiResponse::created([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => ['customer'],
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $customer = Customer::query()->where('email', $credentials['email'])->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            return ApiResponse::unauthorized('Invalid credentials');
        }

        $token = $customer->createToken('customer-api', ['customer'])->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => ['customer'],
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        $token?->delete();

        return ApiResponse::success(['logged_out' => true]);
    }

    /**
     * Authenticated endpoint used by the frontend's "resend email"
     * button. Re-dispatches the queued verification notification.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($customer->hasVerifiedEmail()) {
            return ApiResponse::success(['already_verified' => true]);
        }

        SendCustomerEmailVerification::dispatch($customer->id);

        return ApiResponse::success(['sent' => true]);
    }

    /**
     * Public endpoint hit when the customer clicks the signed link in
     * their verification email. Validates the signature + hash, flips
     * `email_verified_at`, and redirects to the storefront.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect($this->frontendVerifyUrl('expired'));
        }

        $customer = Customer::query()->find($id);

        if (! $customer || ! hash_equals(sha1((string) $customer->getEmailForVerification()), $hash)) {
            return redirect($this->frontendVerifyUrl('invalid'));
        }

        if (! $customer->hasVerifiedEmail()) {
            $customer->markEmailAsVerified();
        }

        return redirect($this->frontendVerifyUrl('success'));
    }

    private function frontendVerifyUrl(string $state): string
    {
        $base = rtrim((string) config('app.frontend_url', 'http://localhost/mostafawosama'), '/');

        return $base . '/views/customer/verify-email.php?state=' . $state;
    }
}
