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
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>

                    <!-- We added an ID here and removed the  -->
                    <tbody id="product-list" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                <p class="text-base font-medium">Loading products...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>
<script type="module">
    // 1. Import Firebase Core and Realtime Database
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
    import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-database.js";

    // 2. Paste your Web App config here (from Project Settings)
     const firebaseConfig = {
        apiKey: @json($firebaseConfig['api_key']),
        authDomain: @json($firebaseConfig['auth_domain']),
        databaseURL: @json($firebaseConfig['database_url']),
        projectId: @json($firebaseConfig['project_id']),
        storageBucket: @json($firebaseConfig['storage_bucket']),
        messagingSenderId: @json($firebaseConfig['messaging_sender_id']),
        appId: @json($firebaseConfig['app_id']),
    };


    // 3. Initialize Firebase and the Realtime Database
    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);

    const tbody = document.getElementById('product-list');
    if (!tbody) {
        // This page does not contain the product table.
    } else {
        const productsRef = ref(db, 'products');

        onValue(productsRef, (snapshot) => {
            // existing rendering code
        }, (error) => {
            console.error('Firebase read failed:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-red-500">
                        Unable to load products.
                    </td>
                </tr>`;
        });
    }

    // 4. Create a reference to the 'products' node (matches your controller)
    const productsRef = ref(db, 'products');

    // 5. Listen for changes in real-time
    onValue(productsRef, (snapshot) => {
        tbody.innerHTML = ""; // Clear current table contents

        const productsData = snapshot.val(); // Get the JSON object

        // If the database is empty or the 'products' node doesn't exist
        if (!productsData) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <p class="text-base font-medium">No products available.</p>
                        <p class="text-sm mt-1">Get started by creating a new product.</p>
                    </td>
                </tr>`;
            return;
        }

        // Realtime Database returns an object of objects, so we loop through the keys
        Object.entries(productsData).forEach(([productId, product]) => {

            // Format price to 2 decimal places
            const formattedPrice = Number(product.price).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Build the table row
            const row = `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-gray-800">${product.name}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${formattedPrice}</td>
                    <td class="px-6 py-4 text-sm flex gap-3">
                        <a href="/edit/${productId}" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-medium rounded-md transition-colors">
                            Update
                        </a>
                        <form method="POST" action="/delete/${productId}" class="inline-block">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 font-medium rounded-md transition-colors">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            `;

            tbody.innerHTML += row;
        });
    },
    (error) => {
        console.error('Firebase read failed:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-red-500">
                    Unable to load products.
                </td>
            </tr>`;
    });
</script>
