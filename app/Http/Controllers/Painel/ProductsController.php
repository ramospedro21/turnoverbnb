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
            
            $products = Product::with(['logs.user' => function($query){
                $query->orderBy('created_at', 'asc');
            }])->orderBy('id', $request->input('order'));

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

                #MAKE THE PRODUCT SLUG
                $slug = Str::slug($request->input('product.name'), '-');
                
                #VERIFY IF EXISTS A PRODUCT WITH THE SAME SLUG
                $hasSlug = Product::where('slug', $slug)->first();

                #IF HAS A PRODUCT WITH THE SAME SLUG, GENERATE A NEW ONE
                if($hasSlug) $slug = $slug . Str::random(3);

                $product = Product::create([
                    'name' => $request->input('product.name'),
                    'slug' => $slug,
                    'sku' => $request->input('product.sku'),
                    'price' => $request->input('product.price'),
                    'quantity' => $request->input('product.quantity'),
                ]);

                ProductLog::create([
                    'user_id' => Auth::user()->id,
                    'product_id' => $product->id,
                    'desc' => "The product '" . $product->name . "' was successfully registered.",
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);

        }catch(\Exception $e){

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
    public function update(Request $request, $id)
    {
        try{

            DB::beginTransaction();

                $oldProduct = Product::find($id);

                $editedProduct = Product::find($id);
                $editedProduct->name = $request->input('product.name');
                $editedProduct->slug = $request->input('product.slug');
                $editedProduct->sku = $request->input('product.sku');
                $editedProduct->price = $request->input('product.price');
                $editedProduct->quantity = $request->input('product.quantity');
                $editedProduct->save();

                ProductLogHelper::makeLog($oldProduct, $editedProduct, Auth::user()->id);

            DB::commit();

            return response()->json([
                'success' => true
            ], 200);

        }catch(\Exception $e){
            
            ProductLog::create([
                'user_id' => Auth::user()->id,
                'product_id' => $id,
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

                $product = Product::find($id);

                $product->delete();

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

    public function bulkEdit(Request $request){
        try{
            
            DB::beginTransaction();

                foreach($request->input('products') as $product){
                    
                    $product = (object) $product;
                    
                    $oldProduct = Product::find($product->id);
                    
                    $editedProduct = Product::find($product->id);
                    $editedProduct->name = $product->name;
                    $editedProduct->slug = $product->slug;
                    $editedProduct->sku = $product->sku;
                    $editedProduct->price = $product->price;
                    $editedProduct->quantity = $product->quantity;
                    $editedProduct->save();
                    
                    ProductLogHelper::makeLog($oldProduct, $editedProduct, Auth::user()->id);

                }

            DB::commit();

            return response()->json([
                'success' => true
            ], 200);

        }catch(\Exception $e){
            
            ProductLog::create([
                'user_id' => Auth::user()->id,
                'product_id' => $id,
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
}
