<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Add Products</h5>
        <div class="row">
            <div class="col-md-8 mb-3">
                <div class="input-group">
                    <input type="text" id="product_search" class="form-control" placeholder="Search products..." autocomplete="off">
                    <button type="button" class="btn btn-primary" id="add_product">Add Product</button>
                </div>
                <div id="product_search_results" class="dropdown-menu w-100"></div>
            </div>
        </div>

        <table class="table" id="selected_products_table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Selected products will be added here dynamically -->
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Product search and add functionality
        const productSearch = document.getElementById('product_search');
        const productResults = document.getElementById('product_search_results');
        const selectedProductsTable = document.getElementById('selected_products_table').getElementsByTagName('tbody')[0];
        const addProductBtn = document.getElementById('add_product');
        let selectedProduct = null;

        // Add product search event listener
        productSearch.addEventListener('input', function() {
            const search = this.value;
            if (search.length < 2) {
                productResults.classList.remove('show');
                return;
            }

            fetch(`sales/handlers/product_search_handler.php?search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    productResults.innerHTML = '';
                    data.forEach(product => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item';
                        div.textContent = `${product.ProductName} - $${product.Price}`;
                        div.onclick = function() {
                            selectedProduct = product;
                            productSearch.value = product.ProductName;
                            productResults.classList.remove('show');
                        };
                        productResults.appendChild(div);
                    });
                    productResults.classList.add('show');
                });
        });

        // Add product button click handler
        addProductBtn.addEventListener('click', function() {
            if (!selectedProduct) return;

            // Check if product already exists
            const existingRows = selectedProductsTable.getElementsByTagName('tr');
            for (let row of existingRows) {
                if (row.dataset.productId === selectedProduct.ProductID) {
                    alert('This product is already added!');
                    return;
                }
            }

            // Add new product row
            const row = selectedProductsTable.insertRow();
            row.dataset.productId = selectedProduct.ProductID;
            row.innerHTML = `
            <td>${selectedProduct.ProductName}</td>
            <td>$${selectedProduct.Price}</td>
            <td>
                <input type="number" name="products[${selectedProduct.ProductID}]" 
                    class="form-control quantity-input" value="1" min="1">
            </td>
            <td>$${selectedProduct.Price}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-product">Remove</button>
            </td>
        `;

            // Add event listeners
            const quantityInput = row.querySelector('.quantity-input');
            quantityInput.addEventListener('change', () => window.updateSaleReceipt());
            quantityInput.addEventListener('keyup', () => window.updateSaleReceipt());

            row.querySelector('.remove-product').addEventListener('click', function() {
                row.remove();
                window.updateSaleReceipt();
            });

            // Clear selection and update totals
            selectedProduct = null;
            productSearch.value = '';
            window.updateSaleReceipt();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!productSearch.contains(e.target) && !productResults.contains(e.target)) {
                productResults.classList.remove('show');
            }
        });
    });
</script>