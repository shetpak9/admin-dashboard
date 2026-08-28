<x-layout>
    <form action="{{ $productId }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="flex items-center justify-center min-h-[calc(100vh-80px)]">
            <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
                <legend class="fieldset-legend">Edit Product</legend>

                <label class="label">Name</label>
                <input
                    type="text"
                    name="name"
                    class="input"
                    value="{{ $product['name'] }}"
                    required
                >

                <label class="label">Price</label>
                <input
                    type="number"
                    name="price"
                    class="input"
                    step="0.01"
                    value="{{ $product['price'] }}"
                    required
                >

                <label class="label">Quantity</label>
                <input
                    type="number"
                    name="quantity"
                    class="input"
                    value="{{ $product['quantity'] }}"
                    required
                >

                <button type="submit" class="btn mt-3">
                    Update Product
                </button>
            </fieldset>
        </div>
    </form>
</x-layout>
