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

            if($editedProduct->name != $oldProduct->name) $message = $message . 'Old name: ' . $oldProduct->name . ' / New name: ' . $editedProduct->name . ', ';
            if($editedProduct->slug != $oldProduct->slug) $message = $message . 'Old slug: ' . $oldProduct->slug . ' / New slug: ' . $editedProduct->slug . ', ';
            if($editedProduct->sku != $oldProduct->sku) $message = $message . 'Old sku: ' . $oldProduct->sku . ' / New sku: ' . $editedProduct->sku . ', ';
            if($editedProduct->price != $oldProduct->price) $message = $message . 'Old price: ' . $oldProduct->price . ' / New price: ' . $editedProduct->price . ', ';
            if($editedProduct->quantity != $oldProduct->quantity) $message = $message . 'Old quantity: ' . $oldProduct->quantity . ' / New quantity: ' . $editedProduct->quantity . ', ';
            
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
