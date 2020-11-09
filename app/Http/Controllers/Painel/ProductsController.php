<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Helpers\ProductLogHelper;

use App\Models\Product;
use App\Models\ProductLog;

use Auth;

class ProductsController extends Controller
{   

    public function view(){
        return view('painel.products.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            
            #QUERY THE PRODUCTS WITH THE LOGS ORDER BY ASC
            $products = Product::with(['logs.user' => function($query){
                $query->orderBy('created_at', 'asc');
            }])->orderBy('id', $request->input('order'));

            #FILTER TO ORDER THE PRODUCTS
            if($request->input('by')){
                $products->orderBy($request->input('by', $request->input('order')));
            }

            $products = $products->paginate(Product::PER_PAGE);


            return response()->json($products, 200);

        }catch(\Exception $e){
            
            return response()->json([
                'message' => 'It wasn’t possible to show the products.',
                'errors' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            
            DB::beginTransaction();

                #PASSES THROUGH THE PRODUCTS SENT IN THE REQUEST
                foreach($request->input('products') as $_product){

                    $_product = (object) $_product;

                    #MAKE THE PRODUCT SLUG
                    $slug = Str::slug($_product->name, '-');
                    
                    #VERIFY IF EXISTS A PRODUCT WITH THE SAME SLUG
                    $hasSlug = Product::where('slug', $slug)->first();

                    #IF HAS A PRODUCT WITH THE SAME SLUG, GENERATE A NEW ONE
                    if($hasSlug) $slug = $slug . Str::random(3);

                    #CREATE THE PRODUCTS
                    $product = Product::create([
                        'name' => $_product->name,
                        'slug' => $slug,
                        'sku' => $_product->sku,
                        'price' => $_product->price,
                        'quantity' => $_product->quantity,
                    ]);
                    
                    #CREATE THE PRODUCT LOG
                    ProductLog::create([
                        'user_id' => Auth::user()->id,
                        'product_id' => $product->id,
                        'desc' => "The product '" . $product->name . "' was successfully registered.",
                    ]);
                }

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);

        }catch(\Exception $e){
            
            #MAKE THE ERROR LOG
            ProductLog::create([
                'user_id' => Auth::user()->id,
                'product_id' => $product->id,
                'desc' => "The product '" . $product->name . "' wasn’t successfully registered. Error: " . $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'It wasn’t possible to register the product.',
                'errors' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request){
        try{
            
            DB::beginTransaction();

                #PASSES THROUGH THE PRODUCTS SENT IN THE REQUEST
                foreach($request->input('products.data') as $product){
                    
                    $product = (object) $product;
                    
                    #FIND THE OLD PRODUCT TO COMPARE WITH THE NEW ONE(EDITED)
                    $oldProduct = Product::find($product->id);
                    
                    #EDIT THE PRODUCT
                    $editedProduct = Product::find($product->id);
                    $editedProduct->name = $product->name;
                    $editedProduct->slug = $product->slug;
                    $editedProduct->sku = $product->sku;
                    $editedProduct->price = $product->price;
                    $editedProduct->quantity = $product->quantity;
                    $editedProduct->save();
                    
                    #CALLS A HELPER TO MAKE THE CHANGES COMPARATION
                    ProductLogHelper::makeLog($oldProduct, $editedProduct, Auth::user()->id);

                }

            DB::commit();

            return response()->json([
                'success' => true
            ], 200);

        }catch(\Exception $e){
            
            #MAKE THE ERROR LOG
            ProductLog::create([
                'user_id' => Auth::user()->id,
                'product_id' => $editedProduct->id,
                'desc' => "The product '" . $editedProduct->name . "' wasn’t successfully edited. Error: " . $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'It wasn’t possible to edited the product.',
                'errors' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{

            DB::beginTransaction();

                #FIND THE PRODUCT
                $product = Product::find($id);

                #FILL OUT THE DELETED_AT COLUMN
                $product->delete();

                #MAKE THE DELETE LOG
                ProductLog::create([
                    'user_id' => Auth::user()->id,
                    'product_id' => $id,
                    'desc' => "The product '" . $product->name . "' was successfully deleted.",
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);

        }catch(\Exception $e){

            ProductLog::create([
                'user_id' => Auth::user()->id,
                'product_id' => $id,
                'desc' => "The product '" . $product->name . "' wasn’t successfully deleted. Error: " . $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'It wasn’t possible to delete the product.',
                'errors' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
    }
}
