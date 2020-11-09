<?php

namespace App\Helpers;

use App\Models\ProductLog;
use Illuminate\Support\Facades\DB;

class ProductLogHelper {

    public static function makeLog($oldProduct, $editedProduct, $user_id)
    {
        try{
            
            DB::beginTransaction();
    
            $message = '';

            if($editedProduct->name != $oldProduct->name) $message = $message . 'old name:' . $oldProduct->name . ' / new name: ' . $editedProduct->name . ', ';
            if($editedProduct->slug != $oldProduct->slug) $message = $message . 'old slug:' . $oldProduct->slug . ' / new slug: ' . $editedProduct->slug . ', ';
            if($editedProduct->sku != $oldProduct->sku) $message = $message . 'old sku:' . $oldProduct->sku . ' / new sku: ' . $editedProduct->sku . ', ';
            if($editedProduct->price != $oldProduct->price) $message = $message . 'old price:' . $oldProduct->price . ' / new price: ' . $editedProduct->price . ', ';
            if($editedProduct->quantity != $oldProduct->quantity) $message = $message . 'old quantity:' . $oldProduct->quantity . ' / new quantity: ' . $editedProduct->quantity . ', ';
            
            ProductLog::create([
                'user_id' => $user_id,
                'product_id' => $editedProduct->id,
                'desc' => "The product '" . $editedProduct->name . "' was successfully edited. " . $message,
            ]);

            DB::commit();

            return true;

        }catch(\Exception $e){
            throw $e;
        }
    }

}
