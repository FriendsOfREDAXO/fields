<?php

namespace FriendsOfRedaxo\Fields;

use rex_api_function;
use rex_response;

/**
 * API-Endpunkt für proxied IBAN-Validierung via openIBAN.com
 *
 * Verhindert direkte Anfragen von Client zu openIBAN.com (Datenschutz/CORS).
 *
 * Aufruf: index.php?rex-api-call=fields_iban_validate&iban=DE89370400440532013000
 *
 * @package fields
 */
class rex_api_fields_iban_validate extends rex_api_function
{
    protected $published = true;

    public function execute(): void
    {
        rex_response::cleanOutputBuffers();

        $iban = \rex_request::get('iban', 'string', '');
        $iban = strtoupper(preg_replace('/\s+/', '', $iban) ?? '');

        if ($iban === '') {
            rex_response::setStatus(rex_response::HTTP_BAD_REQUEST);
            rex_response::sendJson([
                'valid' => false,
                'error' => 'IBAN is required',
            ]);
            exit;
        }

        // Lokale Format-Validierung zuerst
        if (!\rex_yform_value_fields_iban::isValidFormat($iban)) {
            rex_response::sendJson([
                'valid' => false,
                'error' => 'Invalid IBAN format',
                'iban' => $iban,
            ]);
            exit;
        }

        // Anfrage an openIBAN.com proxyen
        $apiUrl = 'https://openiban.com/validate/' . urlencode($iban) . '?getBIC=true&validateBankCode=true';

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'GET',
                'header' => "Accept: application/json\r\nUser-Agent: REDAXO-Fields-Addon/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $response = @file_get_contents($apiUrl, false, $context);

        if ($response === false) {
            // API nicht erreichbar – nur lokale Validierung zurückgeben
            rex_response::sendJson([
                'valid' => true,
                'local_only' => true,
                'iban' => $iban,
                'message' => 'Local validation only (API unavailable)',
            ]);
            exit;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            rex_response::sendJson([
                'valid' => true,
                'local_only' => true,
                'iban' => $iban,
                'message' => 'Local validation only (invalid API response)',
            ]);
            exit;
        }

        // Antwort normalisieren und weiterleiten
        $result = [
            'valid' => $data['valid'] ?? false,
            'iban' => $iban,
        ];

        if (isset($data['bankData'])) {
            $result['bank'] = $data['bankData']['name'] ?? '';
            $result['bic'] = $data['bankData']['bic'] ?? '';
            $result['city'] = $data['bankData']['city'] ?? '';
        }

        if (isset($data['checkResults'])) {
            $result['checks'] = $data['checkResults'];
        }

        rex_response::sendJson($result);
        exit;
    }
}
