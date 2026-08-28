<x-layout>
<form action="{{ route('store') }}" method="POST">
@csrf
    <div class="flex items-center justify-center min-h-[calc(100vh-80px)]">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
          <legend class="fieldset-legend">Create Product</legend>

          <label class="label">Product Name</label>
          <input type="text" name="name" class="input" />

          <label class="label">Price</label>
          <input type="number" name="price" class="input"/>

          <label class="label">Quantity</label>
          <input type="number" name="quantity" class="input"/>
            <button class="btn mt-3 block mx-auto">Create</button>
        </fieldset>

    </div>
</form>
</x-layout>
