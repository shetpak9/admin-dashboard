<x-layout>
    <div class="max-w-4xl mx-auto mt-8">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="p-6 pb-4 border-b border-gray-100 bg-white">
                <h2 class="text-xl font-bold text-gray-800">Product Inventory</h2>
                <p class="text-sm text-gray-500 mt-1">Manage your current products, prices, and stock levels.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">

                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Product Name</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($products as $productId => $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $product['name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($product['price'], 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $product['quantity'] }}</td>

                                <td class="px-6 py-4 text-sm flex gap-3">

                                    <a href="{{ route('edit', $productId) }}" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-medium rounded-md transition-colors">
                                        Update
                                    </a>
                                    <form method="POST" action="/delete/{{ $productId }}" class="inline-block">
                                        @csrf
                                        @method("DELETE")
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 font-medium rounded-md transition-colors">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <p class="text-base font-medium">No products available.</p>
                                    <p class="text-sm mt-1">Get started by creating a new product.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</x-layout>
