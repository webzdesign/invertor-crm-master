<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function addedby()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    
    public function updatedby()
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault([
            'name' => '-',
        ]);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id', 'id');
    }

    public function total()
    {
        return $this->items()->sum('amount') ?? 0;
    }

    public function supplier()
    {
        return $this->belongsTo(User::class);
    }

    public static function setProductIsHot($productID, $productQty){
        
        $isHotProducts = Product::select(['id'])->where('is_hot',1)->where('status',1)->whereNull('deleted_at')->get();

        if($isHotProducts->count() < 15) {
            $product = Product::find($productID);
            if(!empty($product)) {
                $product->is_hot = 1;
                $product->update();
            }
        } else {
            $hotProductIds = $isHotProducts->pluck('id')->toArray();

            $minQty = PurchaseOrderItem::whereIn('product_id', $hotProductIds)->min('qty');

            if ($minQty !== null && $productQty > $minQty) {
                $lowQtyProducts = PurchaseOrderItem::whereIn('product_id', $hotProductIds)
                    ->where('qty', $minQty)->orderBy('product_id','ASC')
                    ->first();

                Product::where('id', $lowQtyProducts->product_id)->update(['is_hot' => 0]);

                Product::find($productID)?->update(['is_hot' => 1]);
            } else {
                if($isHotProducts->count() < 15) {
                    $product = Product::find($productID);
                    if(!empty($product)) {
                        $product->is_hot = 1;
                        $product->update();
                    }
                }       
            }
        }
    }
}
