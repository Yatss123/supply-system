<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'tin', 'quantity', 'unit', 'minimum_stock_level', 'unit_price', 'status', 'damage_severity', 'supply_type', 'has_variants', 'sku', 'location_id'
    ];

    // Supply type constants
    const TYPE_GRANTABLE = 'grantable';
    const TYPE_BORROWABLE = 'borrowable';
    const TYPE_CONSUMABLE = 'consumable';

    // Get all supply types
    public static function getSupplyTypes()
    {
        return [
            self::TYPE_GRANTABLE => 'Grantable (Given away permanently)',
            self::TYPE_BORROWABLE => 'Borrowable (Must be returned after use)',
            self::TYPE_CONSUMABLE => 'Consumable (Used up during use)',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'supply_category');
    }
    
    public function suppliers()
     { 
        return $this->belongsToMany(Supplier::class, 'supply_supplier'); 
    }
    
    public function borrowedItems() 
    { 
        return $this->hasMany(BorrowedItem::class);
    }

    /**
     * Build a 3-letter uppercase abbreviation from a text.
     * Joins first up-to-3 letters across words to reach length 3.
     */
    public static function makeAbbr(?string $text): string
    {
        if (!$text) { return ''; }
        $words = preg_split('/[\s\-]+/', strtoupper(trim($text))) ?: [];
        $abbr = '';
        foreach ($words as $w) {
            if ($w === '') { continue; }
            $abbr .= substr($w, 0, 3);
            if (strlen($abbr) >= 3) {
                $abbr = substr($abbr, 0, 3);
                break;
            }
        }
        return str_pad($abbr, 3, 'X');
    }

    /**
     * Generate supply-level SKU using name, unit, and category name.
     * Format: NAMEABBR-UNITABBR-CATABBR-###
     */
    public static function generateSku(string $name, string $unit, string $categoryName): string
    {
        $nameAbbr = self::makeAbbr($name);
        $unitAbbr = self::makeAbbr($unit);
        $categoryAbbr = self::makeAbbr($categoryName);
        $base = $nameAbbr . '-' . $unitAbbr . '-' . $categoryAbbr;

        // Collect existing SKUs with the same base
        $existing = self::where('sku', 'like', $base . '-%')
            ->pluck('sku')
            ->toArray();

        $seq = 1;
        do {
            $proposal = $base . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
            $seq++;
        } while (in_array($proposal, $existing));

        return $proposal;
    }

    public function variants()
    {
        return $this->hasMany(SupplyVariant::class);
    }

    // Status scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeDamaged($query)
    {
        return $query->where('status', 'damaged');
    }

    // Supply type scopes
    public function scopeGrantable($query)
    {
        return $query->where('supply_type', self::TYPE_GRANTABLE);
    }

    public function scopeBorrowable($query)
    {
        return $query->where('supply_type', self::TYPE_BORROWABLE);
    }

    public function scopeConsumable($query)
    {
        return $query->where('supply_type', self::TYPE_CONSUMABLE);
    }

    // Status helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    public function isDamaged()
    {
        return $this->status === 'damaged';
    }

    // Status update methods
    public function markAsActive()
    {
        $this->update(['status' => 'active']);
    }

    public function markAsInactive()
    {
        $this->update(['status' => 'inactive']);
    }

    public function markAsDamaged(?string $severity = null)
    {
        $data = [];
        if ($severity === 'severe') {
            $data['status'] = 'inactive';
            $data['damage_severity'] = 'severe';
        } else {
            $data['status'] = 'damaged';
            if (!is_null($severity)) {
                $data['damage_severity'] = $severity;
            }
        }
        $this->update($data);
    }

    // Supply type helper methods
    public function isGrantable()
    {
        return $this->supply_type === self::TYPE_GRANTABLE;
    }

    public function isBorrowable()
    {
        return $this->supply_type === self::TYPE_BORROWABLE;
    }

    public function isConsumable()
    {
        return $this->supply_type === self::TYPE_CONSUMABLE;
    }

    public function canBeBorrowed()
    {
        return $this->isBorrowable() && $this->isActive() && $this->availableQuantity() > 0;
    }

    public function getSupplyTypeLabel()
    {
        $types = self::getSupplyTypes();
        return $types[$this->supply_type] ?? 'Unknown';
    }

    // QR Code generation
    public function getQrCodeUrl($action = null)
    {
        if ($action) {
            return route('qr.' . $action, $this->id);
        }
        return route('qr.actions', $this->id);
    }

    public function getQrCodeImageUrl($action = null)
    {
        $url = $this->getQrCodeUrl($action);
        // Encode as: URL followed by SKU, to ensure scanners focus on the URL
        $payload = $url . "\n" . (string)($this->sku ?? '');
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($payload);
    }

    // Variant helper methods
    public function hasVariants()
    {
        return $this->has_variants && $this->variants()->count() > 0;
    }

    public function getTotalVariantQuantity()
    {
        if (!$this->hasVariants()) {
            return $this->quantity;
        }
        
        return $this->variants()->sum('quantity');
    }

    public function getAvailableVariants()
    {
        return $this->variants()->where('quantity', '>', 0)->get();
    }

    public function createVariant(array $data)
    {
        return $this->variants()->create($data);
    }

    public function enableVariants()
    {
        $this->update(['has_variants' => true]);
    }

    public function disableVariants()
    {
        $this->variants()->delete();
        $this->update(['has_variants' => false]);
    }

    // Inventory availability helpers
    public function activeBorrowedQuantity(): int
    {
        return (int) $this->borrowedItems()
            ->whereNull('returned_at')
            ->sum('quantity');
    }

    public function activeInterDepartmentBorrowedQuantity(): int
    {
        // Sum quantities of inter-department borrows for this supply (active and overdue)
        return (int) \App\Models\InterDepartmentBorrowedItem::whereHas('issuedItem', function ($q) {
                $q->where('supply_id', $this->id);
            })
            ->whereIn('status', ['active', 'overdue'])
            ->sum('quantity_borrowed');
    }

    public function availableQuantity(): int
    {
        // If this supply has variants, the supply-level availability should be
        // the sum of the variants' currently available quantities.
        if ($this->hasVariants()) {
            $totalAvailable = 0;
            foreach ($this->variants as $variant) {
                // Prefer computed availability if available; otherwise use raw quantity
                if (method_exists($variant, 'availableQuantity')) {
                    $totalAvailable += (int) $variant->availableQuantity();
                } else {
                    $totalAvailable += (int) ($variant->quantity ?? 0);
                }
            }
            return max(0, $totalAvailable);
        }

        // Fallback for non-variant supplies: base quantity minus active borrows
        $available = (int) ($this->quantity ?? 0)
            - $this->activeBorrowedQuantity()
            - $this->activeInterDepartmentBorrowedQuantity();
        return max(0, $available);
    }

    // Aggregated missing/damaged metrics based on verified returns
    public function totalMissingCount(): int
    {
        // Sum of missing counts from verified returns for this supply (supply-level loans)
        return (int) BorrowedItem::where('supply_id', $this->id)
            ->whereNotNull('return_verified_at')
            ->where('returned_status', 'returned_with_missing')
            ->sum('missing_count');
    }

    public function totalDamagedCount(): int
    {
        // Sum of damaged counts from verified returns for this supply (supply-level loans)
        return (int) BorrowedItem::where('supply_id', $this->id)
            ->whereNotNull('return_verified_at')
            ->where('returned_status', 'returned_with_damage')
            ->sum('damaged_count');
    }
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
