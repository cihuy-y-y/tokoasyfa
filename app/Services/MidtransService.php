<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    private function http()
    {
        $key = config('services.midtrans.server_key');
        if (! $key) {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diisi di .env');
        }

        return Http::baseUrl(config('services.midtrans.base_url'))
            ->withBasicAuth($key, '')
            ->acceptJson()->asJson()->timeout(20);
    }

    private function charge(array $payload): array
    {
        $res = $this->http()->post('/v2/charge', $payload);
        $j   = $res->json() ?? [];

        Log::warning('midtrans charge', ['http' => $res->status(), 'body' => $res->body()]);

        if (! in_array((string) ($j['status_code'] ?? ''), ['200', '201'], true)) {
            throw new \RuntimeException(
                ($j['status_message'] ?? $j['error_messages'][0] ?? 'Gagal membuat pembayaran.')
                    . ' (HTTP ' . $res->status() . ')'
            );
        }
        return $j;
    }

    private function aksi(array $j, string $nama): ?string
    {
        foreach ($j['actions'] ?? [] as $a) {
            if (($a['name'] ?? '') === $nama) return $a['url'] ?? null;
        }
        return null;
    }

    public function buatQris(string $ref, int $nominal): array
    {
        $j = $this->charge([
            'payment_type'        => 'qris',
            'transaction_details' => ['order_id' => $ref, 'gross_amount' => $nominal],
            'qris'                => ['acquirer' => 'gopay'],
            'custom_expiry'       => ['expiry_duration' => 15, 'unit' => 'minute'],
        ]);

        return [
            'id' => $j['transaction_id'] ?? null,
            'qr_string' => $j['qr_string'] ?? null,
            'qr_url' => $this->aksi($j, 'generate-qr-code')
        ];
    }

    public function buatGopay(string $ref, int $nominal): array
    {
        $j = $this->charge([
            'payment_type'        => 'gopay',
            'transaction_details' => ['order_id' => $ref, 'gross_amount' => $nominal],
            'gopay'               => ['enable_callback' => false],
            'custom_expiry'       => ['expiry_duration' => 15, 'unit' => 'minute'],
        ]);

        return [
            'id' => $j['transaction_id'] ?? null,
            'qr_string' => null,
            'qr_url' => $this->aksi($j, 'generate-qr-code')
        ];
    }

    public function status(string $ref): array
    {
        return $this->http()->get('/v2/' . $ref . '/status')->json() ?? [];
    }

    public function batal(string $ref): void
    {
        $this->http()->post('/v2/' . $ref . '/cancel');
    }
}
