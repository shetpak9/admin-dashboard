<x-layout>
<div class="overflow-x-auto mt-6 max-w-3xl mx-auto">
    <table class="table-auto w-full border-collapse border border-base-300 text-left">
        <thead class="bg-base-200">
            <tr>
                <th class="border border-base-300 px-4 py-2">Product Name</th>
                <th class="border border-base-300 px-4 py-2">Price</th>
                <th class="border border-base-300 px-4 py-2">Quantity</th>
                <th class="border border-base-300 px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Iterate through the products -->
            @forelse ($products as $productId => $product)
                <tr class="hover:bg-base-100">
                    <td class="border border-base-300 px-4 py-2">{{ $product['name'] }}</td>
                    <td class="border border-base-300 px-4 py-2">{{ number_format($product['price'], 2) }}</td>
                    <td class="border border-base-300 px-4 py-2">{{ $product['quantity'] }}</td>
                    <td class="border border-base-300 px-4 py-2">
                        <a class="btn mr-2" href="{{ route('edit', $productId) }}">Update</a>
                        <button type="submit" class="btn background-red" form="delete-product-form-{{ $productId }}">Delete</button>
                    </td>
                </tr>
                <form id="delete-product-form-{{ $productId }}" method="POST" action="/delete/{{ $productId }}">
                    @csrf
                    @method("DELETE")
                </form>
            @empty
                <!-- Fallback if there are no products in the database -->
                <tr>
                    <td colspan="4" class="border border-base-300 px-4 py-2 text-center text-gray-500">
                        No products available.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</x-layout>
