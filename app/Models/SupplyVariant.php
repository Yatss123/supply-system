<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'variant_name',
        'attributes',
        'quantity',
        'sku',
        'price',
        'tin',
        'status'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2'
    ];

    /**
     * Get the supply that owns the variant.
     */
    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    /**
     * Get a formatted display name for the variant.
     */
    public function getDisplayNameAttribute(): string
    {
        // Safely retrieve the 'attributes' column, handling strings/JSON/arrays
        $attrs = $this->getAttribute('attributes');
        if (is_string($attrs)) {
            $decoded = json_decode($attrs, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $attrs = $decoded;
            } else {
                $attrs = $attrs !== '' ? ['' => $attrs] : [];
            }
        } elseif (!is_array($attrs)) {
            $attrs = [];
        }

        $attributesStr = collect($attrs)->map(function ($value, $key) {
            $val = is_array($value) ? implode(', ', $value) : (string) $value;
            return ($key !== '' ? ucfirst($key) . ': ' : '') . ucfirst($val);
        })->implode(', ');

        return $this->variant_name . ($attributesStr ? ' (' . $attributesStr . ')' : '');
    }

    /**
     * Check if the variant has sufficient stock.
     */
    public function hasStock(int $requestedQuantity = 1): bool
    {
        return ($this->status === 'active') && ($this->quantity >= $requestedQuantity);
    }

    /**
     * Reduce the variant stock.
     */
    public function reduceStock(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    /**
     * Increase the variant stock.
     */
    public function increaseStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Sum of active borrowed quantity for this variant via loan requests.
     */
    public function activeBorrowedQuantity(): int
    {
        // Borrowed items linked to loan requests carrying this variant id and not yet returned
        return (int) \App\Models\BorrowedItem::where('supply_id', $this->supply_id)
            ->whereNull('returned_at')
            ->whereHas('loanRequest', function ($q) {
                $q->where('supply_variant_id', $this->id);
            })
            ->sum('quantity');
    }

    /**
     * Currently available quantity for this variant.
     * For borrowable supplies, subtract active borrowed quantity per variant.
     * For consumable/grantable supplies, the variant's stored quantity is current stock.
     */
    public function availableQuantity(): int
    {
        $supply = $this->supply;
        if ($this->status !== 'active') {
            return 0;
        }
        $base = (int) ($this->quantity ?? 0);
        if ($supply && $supply->isBorrowable()) {
            $available = $base - $this->activeBorrowedQuantity();
            return max(0, $available);
        }
        return max(0, $base);
    }

    /**
     * Aggregated missing/damaged metrics for this variant based on verified returns.
     */
    public function totalMissingCount(): int
    {
        return (int) \App\Models\BorrowedItem::where('supply_id', $this->supply_id)
            ->whereNotNull('return_verified_at')
            ->where('returned_status', 'returned_with_missing')
            ->whereHas('loanRequest', function ($q) {
                $q->where('supply_variant_id', $this->id);
            })
            ->sum('missing_count');
    }

    public function totalDamagedCount(): int
    {
        return (int) \App\Models\BorrowedItem::where('supply_id', $this->supply_id)
            ->whereNotNull('return_verified_at')
            ->where('returned_status', 'returned_with_damage')
            ->whereHas('loanRequest', function ($q) {
                $q->where('supply_variant_id', $this->id);
            })
            ->sum('damaged_count');
    }

    /**
     * Status helpers
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function disable(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function enable(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Generate automatic SKU based on supply name, variant name, and attributes.
     * Format: [abbreviation of supply name] + [variant name] + [first letter of first attribute value] + [sequential number]
     * Example: UNI-M-MALE-001
     */
    public static function generateSku(Supply $supply, string $variantName, array $attributes = []): string
    {
        // Generate supply abbreviation (first 3 letters of each word, max 3 chars)
        $supplyWords = explode(' ', strtoupper($supply->name));
        $supplyAbbr = '';
        foreach ($supplyWords as $word) {
            $supplyAbbr .= substr($word, 0, 3);
            if (strlen($supplyAbbr) >= 3) {
                $supplyAbbr = substr($supplyAbbr, 0, 3);
                break;
            }
        }
        
        // Clean and format variant name (remove spaces, convert to uppercase)
        $variantPart = strtoupper(str_replace(' ', '-', trim($variantName)));
        
        // Get first letter of first attribute value if available
        $attributePart = '';
        if (!empty($attributes)) {
            $firstAttributeValue = reset($attributes);
            $attributePart = strtoupper(substr($firstAttributeValue, 0, 1));
        }
        
        // Generate base SKU
        $baseSku = $supplyAbbr . '-' . $variantPart;
        if ($attributePart) {
            $baseSku .= '-' . $attributePart;
        }
        
        // Find next sequential number
        $existingVariants = self::where('supply_id', $supply->id)
            ->where('sku', 'like', $baseSku . '%')
            ->pluck('sku')
            ->toArray();
        
        $sequentialNumber = 1;
        do {
            $proposedSku = $baseSku . '-' . str_pad($sequentialNumber, 3, '0', STR_PAD_LEFT);
            $sequentialNumber++;
        } while (in_array($proposedSku, $existingVariants));
        
        return $proposedSku;
    }

    /**
     * Build a QR target URL for this variant.
     * Defaults to the 'quick-issue' action with this variant preselected.
     */
    public function getQrCodeUrl(string $action = null): string
    {
        $routeName = $action ? ('qr.' . $action) : 'qr.actions';
        $base = route($routeName, ['supply' => $this->supply_id]);
        $separator = str_contains($base, '?') ? '&' : '?';
        return $base . $separator . 'supply_variant_id=' . $this->id;
    }

    /**
     * Get an external QR code image URL encoding the variant's QR target.
     */
    public function getQrCodeImageUrl(string $action = null, string $size = '200x200'): string
    {
        $url = $this->getQrCodeUrl($action);
        // Encode as: URL followed by SKU, to ensure scanners focus on the URL
        $payload = $url . "\n" . (string)($this->sku ?? '');
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . '&data=' . urlencode($payload);
    }
}
