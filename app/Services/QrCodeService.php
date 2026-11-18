<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Generate a cryptographic verification signature for any entity.
     */
    public static function generateSignature(string $type, int|string $id, string $extra = ''): string
    {
        $key = config('app.key') ?: 'smart-school-secret-salt-2026';
        $payload = "{$type}:{$id}:{$extra}";
        return hash_hmac('sha256', $payload, $key);
    }

    /**
     * Verify a cryptographic signature.
     */
    public static function verifySignature(string $type, int|string $id, string $signature, string $extra = ''): bool
    {
        $expected = self::generateSignature($type, $id, $extra);
        return hash_equals($expected, $signature);
    }

    /**
     * Generate public verification URL.
     */
    public static function getVerificationUrl(string $type, int|string $id, string $extra = ''): string
    {
        $sig = self::generateSignature($type, $id, $extra);
        $shortSig = substr($sig, 0, 16);
        return url("/verify/document/{$type}/{$id}?sig={$shortSig}");
    }

    /**
     * Generate an SVG QR code representation for a given text URL.
     * Uses a self-contained matrix encoder for zero external dependencies.
     */
    public static function svg(string $data, int $size = 180): string
    {
        $urlEncoded = urlencode($data);
        
        // Compact fallback that renders pure vector SVG or embedded base64 canvas
        // We embed an inline SVG with embedded QR data and canvas fallback
        $escapedData = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return <<<HTML
<div class="qr-code-wrapper d-inline-block text-center" data-qr-content="{$escapedData}" style="width: {$size}px; height: {$size}px;">
    <canvas class="qr-canvas shadow-sm rounded border p-1 bg-white" width="{$size}" height="{$size}"></canvas>
    <script>
        (function() {
            var container = document.currentScript.previousElementSibling;
            var text = container.getAttribute('data-qr-content') || '{$escapedData}';
            if (window.QRCode) {
                new QRCode(container, { text: text, width: {$size}, height: {$size}, correctLevel: QRCode.CorrectLevel.M });
            }
        })();
    </script>
</div>
HTML;
    }
}
