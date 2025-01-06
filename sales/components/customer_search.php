<div class="col-md-6">
    <label class="form-label">Customer</label>
    <div class="input-group">
        <input type="text" id="customer_search" class="form-control" placeholder="Search customer..." autocomplete="off">
        <input type="hidden" name="customer_id" id="customer_id" required>
    </div>
    <div id="customer_search_results" class="dropdown-menu w-100"></div>
    <div id="selected_customer" class="form-text"></div>
</div>

<script>
    let customerTimeout = null;
    const customerSearch = document.getElementById('customer_search');
    const customerResults = document.getElementById('customer_search_results');
    const customerId = document.getElementById('customer_id');
    const selectedCustomer = document.getElementById('selected_customer');

    customerSearch.addEventListener('input', function() {
        clearTimeout(customerTimeout);
        customerTimeout = setTimeout(() => {
            const search = this.value;
            if (search.length < 2) {
                customerResults.classList.remove('show');
                return;
            }

            fetch(`sales/handlers/customer_search_handler.php?search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    customerResults.innerHTML = '';
                    data.forEach(customer => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item';
                        div.textContent = `${customer.FirstName} ${customer.LastName}`;
                        div.onclick = function() {
                            customerId.value = customer.CustomerID;
                            customerSearch.value = `${customer.FirstName} ${customer.LastName}`;
                            selectedCustomer.textContent = `Selected: ${customer.FirstName} ${customer.LastName}`;
                            customerResults.classList.remove('show');
                            document.getElementById('bill-customer').textContent = `${customer.FirstName} ${customer.LastName}`;
                        };
                        customerResults.appendChild(div);
                    });
                    customerResults.classList.add('show');
                });
        }, 300);
    });
</script>