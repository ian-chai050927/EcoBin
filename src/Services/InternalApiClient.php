<?php
namespace EcoBin\Services;

class InternalApiClient
{
    public function __construct(
        private string $baseUrl,
        private string $serviceToken
    ) {}

    public function call(string $service, array $payload): array
    {
        $request = [
            'requestID' => bin2hex(random_bytes(8)),
            'timestamp' => date('c'),
            'service' => $service,
            'payload' => $payload,
        ];

        $ch = curl_init(rtrim($this->baseUrl, '/') . '/api.php');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Service-Token: ' . $this->serviceToken,
            ],
            CURLOPT_POSTFIELDS => json_encode($request),
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 5,
        ]);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            return [
                'requestID' => $request['requestID'],
                'timestamp' => date('c'),
                'status' => 'ERROR',
                'error' => $error ?: 'No response',
            ];
        }

        return json_decode($raw, true) ?: [
            'requestID' => $request['requestID'],
            'timestamp' => date('c'),
            'status' => 'ERROR',
            'error' => 'Invalid JSON response',
        ];
    }
}
