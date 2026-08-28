<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private FirebaseService $firebaseService)
    {
    }

    public function index()
    {
        $products = $this->firebaseService
            ->getDatabase()
            ->getReference('products')
            ->getValue() ?? [];

         /* return response()->json($products);  */
         return view('index', [
            'products' => $products
         ]);
    }

    public function create(){
        return view('create');
    }

    public function store(Request $request)
    {
        $product = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $reference = $this->firebaseService
            ->getDatabase()
            ->getReference('products')
            ->push($product);

        return redirect('add');
    }

    public function edit(string $product){
        $productData = $this->firebaseService
            ->getDatabase()
            ->getReference("products/{$product}")
            ->getValue();

        if ($productData === null) {
            abort(404, 'Product not found.');
        }

        return view('edit', [
            'productId' => $product,
            'product' => $productData,
        ]);
    }

    public function show(string $product): JsonResponse
    {
        $productData = $this->firebaseService
            ->getDatabase()
            ->getReference("products/{$product}")
            ->getValue();

        if ($productData === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json([
            'id' => $product,
            ...$productData,
        ]);
    }

    public function update(Request $request, string $product)
    {
        $productData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $reference = $this->firebaseService
            ->getDatabase()
            ->getReference("products/{$product}");

        if ($reference->getValue() === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $reference->update($productData);

        return to_route('index');
    }

    public function destroy(string $product)
    {
        $reference = $this->firebaseService
            ->getDatabase()
            ->getReference("products/{$product}");

        if ($reference->getValue() === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $reference->remove();

        return to_route('index');
    }
}
