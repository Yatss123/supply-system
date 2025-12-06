<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * Generate a QR code image for a given supply action.
     * Produces a PNG stream encoding a temporary signed URL to the target route.
     */
    public function generate(Request $request, Supply $supply, string $action)
    {
        // Only allow QR generation for known GET actions aligned with existing routes
        $allowedActions = [
            'actions' => 'qr.actions',
            'quick-issue' => 'qr.quick-issue',
            'quick-status-change' => 'qr.quick-status-change',
            'supply-request' => 'qr.supply-request',
            'borrow-request' => 'qr.borrow-request',
            'return' => 'qr.return',
            'borrowing-info' => 'qr.borrowing-info',
        ];

        if (!array_key_exists($action, $allowedActions)) {
            return response()->json(['error' => 'Unsupported QR action'], 400);
        }

        // Optional size parameter with sane bounds
        $size = (int)($request->input('size', 300));
        if ($size < 100) $size = 100;
        if ($size > 1000) $size = 1000;

        // Optional format parameter: png (default) or svg
        $format = strtolower($request->input('format', 'png'));
        if (!in_array($format, ['png', 'svg'])) {
            $format = 'png';
        }

        // Create a temporary signed URL to the action to prevent tampering
        // Signature expires in 30 minutes by default
        $expires = now()->addMinutes((int)($request->input('ttl', 30)));

        $routeName = $allowedActions[$action];

        // Build signed URL including any optional context passthrough from query
        $signedUrl = URL::temporarySignedRoute(
            $routeName,
            $expires,
            ['supply' => $supply->id] + $request->except(['size', 'ttl'])
        );

        // Build payload as: URL followed by SKU (URL-first for standard scanners)
        $variantId = $request->input('supply_variant_id');
        $variantSku = null;
        if ($variantId) {
            $variant = SupplyVariant::where('id', $variantId)->where('supply_id', $supply->id)->first();
            if ($variant) {
                $variantSku = $variant->sku;
            }
        }
        $skuValue = $variantSku ?? $supply->sku ?? '';
        $payload = $signedUrl . "\n" . (string)$skuValue;

        // Generate QR code stream in requested format using the plain-text payload
        $qr = QrCode::format($format)
            ->size($size)
            ->margin(1)
            ->generate($payload);

        $contentType = $format === 'svg' ? 'image/svg+xml' : 'image/png';
        return response($qr)->header('Content-Type', $contentType);
    }
}
