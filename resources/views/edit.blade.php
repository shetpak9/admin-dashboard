<x-layout>
    <form action="{{ $productId }}" method="POST" class="max-w-2xl w-full mt-8">
        @csrf
        @method('PATCH')

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">

            <div class="mb-6 pb-4 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Edit Product</h2>
                <p class="text-sm text-gray-500 mt-1">Update the details and pricing for this product.</p>
            </div>

            <div class="space-y-5">

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ $product['name'] }}" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" />
                </div>

                <div class="flex gap-6">
                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Price (₱)</label>
                        <input type="number" name="price" step="0.01" value="{{ $product['price'] }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" />
                    </div>

                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" value="{{ $product['quantity'] }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" />
                    </div>
                </div>

            </div>

            <div class="mt-8 pt-5 flex gap-4">
                <a href="/" class="w-1/3 flex justify-center items-center bg-gray-50 hover:bg-gray-100 text-gray-600 font-bold py-3 px-4 rounded-lg border border-gray-200 transition-colors">
                    Cancel
                </a>

                <button type="submit" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-md">
                    Update Product
                </button>
            </div>

        </div>
    </form>
</x-layout>
