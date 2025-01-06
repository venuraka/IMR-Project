<div class="col-md-6">
    <label class="form-label">Employee</label>
    <div class="input-group">
        <input type="text" id="employee_search" class="form-control" placeholder="Search employee..." autocomplete="off">
        <input type="hidden" name="employee_id" id="employee_id" required>
    </div>
    <div id="employee_search_results" class="dropdown-menu w-100"></div>
    <div id="selected_employee" class="form-text"></div>
</div>

<script>
    let employeeTimeout = null;
    const employeeSearch = document.getElementById('employee_search');
    const employeeResults = document.getElementById('employee_search_results');
    const employeeId = document.getElementById('employee_id');
    const selectedEmployee = document.getElementById('selected_employee');

    employeeSearch.addEventListener('input', function() {
        clearTimeout(employeeTimeout);
        employeeTimeout = setTimeout(() => {
            const search = this.value;
            if (search.length < 2) {
                employeeResults.classList.remove('show');
                return;
            }

            fetch(`sales/handlers/employee_search_handler.php?search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    employeeResults.innerHTML = '';
                    data.forEach(employee => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item';
                        div.textContent = `${employee.FirstName} ${employee.LastName}`;
                        div.onclick = function() {
                            employeeId.value = employee.EmployeeId;
                            employeeSearch.value = `${employee.FirstName} ${employee.LastName}`;
                            selectedEmployee.textContent = `Selected: ${employee.FirstName} ${employee.LastName}`;
                            employeeResults.classList.remove('show');
                            document.getElementById('bill-employee').textContent = `${employee.FirstName} ${employee.LastName}`;
                        };
                        employeeResults.appendChild(div);
                    });
                    employeeResults.classList.add('show');
                });
        }, 300);
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
            customerResults.classList.remove('show');
        }
        if (!employeeSearch.contains(e.target) && !employeeResults.contains(e.target)) {
            employeeResults.classList.remove('show');
        }
    });
</script>