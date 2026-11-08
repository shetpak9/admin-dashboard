<x-layout>
    <div class="max-w-6xl mx-auto mt-8">
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
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
{{--                             <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
 --}}                        </tr>
                    </thead>
                    <tbody id="product-list" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
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
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
    import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-database.js";
    // 1. Import Auth
    import { getAuth, signInAnonymously } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

    const firebaseConfig = {
        apiKey: @json($firebaseConfig['api_key']),
        authDomain: @json($firebaseConfig['auth_domain']),
        databaseURL: @json($firebaseConfig['database_url']),
        projectId: @json($firebaseConfig['project_id']),
        storageBucket: @json($firebaseConfig['storage_bucket']),
        messagingSenderId: @json($firebaseConfig['messaging_sender_id']),
        appId: @json($firebaseConfig['app_id']),
    };

    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);
    // 2. Initialize Auth
    const auth = getAuth(app);
    const tbody = document.getElementById('product-list');

    if (tbody) {
        // 3. Authenticate BEFORE reading the database
        signInAnonymously(auth).then(() => {
            console.log("Authenticated successfully!");

            const productsRef = ref(db, 'products');

            onValue(productsRef, (snapshot) => {
                tbody.innerHTML = "";
                const productsData = snapshot.val();

                if (!productsData) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <p class="text-base font-medium">No products available.</p>
                            </td>
                        </tr>`;
                    return;
                }

                Object.entries(productsData).forEach(([productId, product]) => {
                    const formattedPrice = Number(product.price).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    const category = product.category || '<span class="text-gray-400 italic">Uncategorized</span>';
                    const stock = product.stockQuantity ?? 0;
                    const statusBadge = product.isAvailable
                        ? `<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Available</span>`
                        : `<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">Hidden</span>`;

                    const row = `
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800">${product.name}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">${category}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">₱${formattedPrice}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">${stock}</td>
                            <td class="px-6 py-4 text-sm">${statusBadge}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }, (error) => {
                console.error('Firebase read failed:', error);
            });

        }).catch((error) => {
            console.error("Authentication failed:", error);
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-red-500">Auth failed: ${error.message}</td></tr>`;
        });
    }
</script>
