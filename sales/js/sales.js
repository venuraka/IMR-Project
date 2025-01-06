window.updateSaleReceipt = function () {
    const selectedProductsTable = document.getElementById('selected_products_table').getElementsByTagName('tbody')[0];
    const billItems = document.getElementById('bill-items');
    let subtotal = 0;

    // Clear existing items
    billItems.innerHTML = '';

    // Process each product row
    const rows = selectedProductsTable.getElementsByTagName('tr');
    for (let row of rows) {
        const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('td:nth-child(2)').textContent.replace('$', ''));
        const rowSubtotal = quantity * price;

        if (quantity > 0) {
            // Add to bill items
            const itemRow = document.createElement('tr');
            itemRow.innerHTML = `
                <td>${row.querySelector('td:nth-child(1)').textContent}</td>
                <td class="text-center">${quantity}</td>
                <td class="text-end">$${price.toFixed(2)}</td>
                <td class="text-end">$${rowSubtotal.toFixed(2)}</td>
            `;
            billItems.appendChild(itemRow);
        }

        // Update row subtotal
        row.querySelector('td:nth-child(4)').textContent = `$${rowSubtotal.toFixed(2)}`;
        subtotal += rowSubtotal;
    }

    // Update totals
    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('total').textContent = `$${subtotal.toFixed(2)}`;
};

// Initialize update function when document is ready
document.addEventListener('DOMContentLoaded', function () {
    window.updateSaleReceipt();
});
