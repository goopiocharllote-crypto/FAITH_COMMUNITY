<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Payments\Payable;
use App\Payments\PaymentGateway;
use App\Payments\PaymentGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * GCash payments through PayMongo Checkout Sessions.
 *
 * @see https://developers.paymongo.com/reference/create-a-checkout
 */
class PayMongoGateway implements PaymentGateway
{
    public function __construct(
        private readonly ?string $secretKey,
        private readonly string $baseUrl,
    ) {
    }

    public function name(): string
    {
        return 'paymongo';
    }

    public function createCheckout(Payment $payment, Payable $payable, string $successUrl, string $cancelUrl): array
    {
        $user = $payable->payer();

        $response = $this->send(fn (PendingRequest $http) => $http->post('/checkout_sessions', [
            'data' => [
                'attributes' => [
                    'line_items' => [[
                        'currency' => config('payments.currency'),
                        'amount' => (int) round((float) $payment->amount * 100),
                        'name' => $payable->paymentDescription(),
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => ['gcash'],
                    'description' => $payable->paymentDescription().' · '.$payable->paymentReference(),
                    'reference_number' => $payable->paymentReference(),
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                    'billing' => array_filter([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->mobile ? '0'.$user->mobile : null,
                    ]),
                    'metadata' => [
                        'payment_id' => (string) $payment->id,
                        'reference' => $payable->paymentReference(),
                    ],
                ],
            ],
        ]));

        $id = $response->json('data.id');
        $url = $response->json('data.attributes.checkout_url');

        if (! is_string($id) || ! is_string($url)) {
            throw new PaymentGatewayException('PayMongo did not return a checkout URL.');
        }

        return ['id' => $id, 'url' => $url];
    }

    public function fetchStatus(Payment $payment): array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->get('/checkout_sessions/'.$payment->checkout_id));

        return self::statusFromSession($response->json('data') ?? []);
    }

    /**
     * Interpret a checkout_session object (from the API or a webhook).
     *
     * @return array{status: string, payment_id: string|null}
     */
    public static function statusFromSession(array $session): array
    {
        foreach ($session['attributes']['payments'] ?? [] as $gatewayPayment) {
            if (($gatewayPayment['attributes']['status'] ?? null) === 'paid') {
                return ['status' => Payment::STATUS_PAID, 'payment_id' => $gatewayPayment['id'] ?? null];
            }
        }

        if (($session['attributes']['payment_intent']['attributes']['status'] ?? null) === 'succeeded') {
            return ['status' => Payment::STATUS_PAID, 'payment_id' => null];
        }

        if (($session['attributes']['status'] ?? null) === 'expired') {
            return ['status' => Payment::STATUS_CANCELLED, 'payment_id' => null];
        }

        return ['status' => Payment::STATUS_PENDING, 'payment_id' => null];
    }

    /**
     * Verify the Paymongo-Signature header: "t=<timestamp>,te=<test sig>,li=<live sig>".
     */
    public static function signatureIsValid(string $header, string $payload, ?string $secret, int $toleranceSeconds = 300): bool
    {
        if (! $secret || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $piece) {
            [$key, $value] = array_pad(explode('=', trim($piece), 2), 2, '');
            $parts[$key] = $value;
        }

        $timestamp = (int) ($parts['t'] ?? 0);

        if ($timestamp === 0 || abs(time() - $timestamp) > $toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach (['te', 'li'] as $key) {
            if (! empty($parts[$key]) && hash_equals($expected, $parts[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  callable(PendingRequest): Response  $call
     */
    private function send(callable $call): Response
    {
        if (! $this->secretKey) {
            throw new PaymentGatewayException('PAYMONGO_SECRET_KEY is not set.');
        }

        try {
            $response = $call(
                Http::baseUrl($this->baseUrl)
                    ->withBasicAuth($this->secretKey, '')
                    ->acceptJson()
                    ->asJson()
                    ->timeout(20)
            );
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayException('Could not reach PayMongo: '.$exception->getMessage(), 0, $exception);
        }

        if ($response->failed()) {
            $detail = $response->json('errors.0.detail') ?? $response->body();

            throw new PaymentGatewayException('PayMongo error ('.$response->status().'): '.$detail);
        }

        return $response;
    }
}
