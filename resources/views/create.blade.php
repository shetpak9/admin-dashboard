<x-layout>

    <form action="{{ route('store') }}" method="POST" class="max-w-2xl mt-8">
    @csrf

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">

            <div class="mb-6 pb-4 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Product Details</h2>
                <p class="text-sm text-gray-500 mt-1">Enter the information below to add a new item to your inventory.</p>
            </div>

            <div class="space-y-5">

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="e.g., Wireless Keyboard" />
                </div>

                <div class="flex gap-6">
                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Price (₱)</label>
                        <input type="number" name="price" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="0.00" />
                    </div>

                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="0" />
                    </div>
                </div>

            </div>

            <div class="mt-8 pt-5">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-md">
                    Save Product
                </button>
            </div>

        </div>
    </form>
</x-layout>
