<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private FirebaseService $firebaseService,
        private ActivityLogService $activityLogService,
    ) {}

    public function index()
    {
        $products = $this->firebaseService
            ->getDatabase()
            ->getReference('products')
            ->getValue() ?? [];

        /* return response()->json($products); */
        return view('index', [
            'products' => $products,
            'firebaseConfig' => [
                'api_key' => config('services.firebase.api_key'),
                'auth_domain' => config('services.firebase.auth_domain'),
                'database_url' => config('services.firebase.database_url'),
                'project_id' => config('services.firebase.project_id'),
                'storage_bucket' => config('services.firebase.storage_bucket'),
                'messaging_sender_id' => config('services.firebase.messaging_sender_id'),
                'app_id' => config('services.firebase.app_id'),
            ],
        ]);
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $product = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $product['price'] = (float) $product['price'];

        $reference = $this->firebaseService
            ->getDatabase()
            ->getReference('products')
            ->push($product);
        $this->recordAdminActivity($request, 'product.created', (string) $reference->getKey(), $product);

        return redirect('add');
    }

    public function edit(string $product)
    {
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
        ]);

        $productData['price'] = (float) $productData['price'];

        $reference = $this->firebaseService
            ->getDatabase()
            ->getReference("products/{$product}");

        if ($reference->getValue() === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $reference->update($productData);
        $this->recordAdminActivity($request, 'product.updated', $product, $productData);

        return to_route('index');
    }

    public function destroy(Request $request, string $product)
    {
        $reference = $this->firebaseService
            ->getDatabase()
            ->getReference("products/{$product}");

        if ($reference->getValue() === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $reference->remove();
        $this->recordAdminActivity($request, 'product.deleted', $product);

        return to_route('index');
    }

    private function recordAdminActivity(Request $request, string $action, string $entityId, array $details = []): void
    {
        if ($request->session()->get('admin_authenticated') !== true) {
            return;
        }

        $adminUsername = (string) $request->session()->get('admin_username');

        $this->activityLogService->record([
            'userUid' => $adminUsername,
            'userName' => $adminUsername,
            'userRole' => 'admin',
            'action' => $action,
            'entityType' => 'product',
            'entityId' => $entityId,
            'details' => $details,
            'ipAddress' => $request->ip(),
        ]);
    }
}
