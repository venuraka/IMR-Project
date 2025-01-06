DELIMITER $$

CREATE PROCEDURE CalculateCustomerStats(IN customerId INT)
BEGIN
    DECLARE avg_spending DECIMAL(10,2);
    DECLARE visit_freq VARCHAR(50);
    DECLARE total_sales INT;
    DECLARE days_between_first_last DECIMAL(10,2);
    
    -- Calculate average spending
    SELECT COALESCE(AVG(TotalAmount), 0)
    INTO avg_spending
    FROM Sale
    WHERE CustomerID = customerId;

    -- Calculate visit frequency based on sales pattern
    SELECT 
        COUNT(*) as total_sales,
        DATEDIFF(MAX(SaleDate), MIN(SaleDate)) as date_range
    INTO total_sales, days_between_first_last
    FROM Sale
    WHERE CustomerID = customerId;

    -- Determine visit frequency
    SET visit_freq = CASE
        WHEN total_sales = 0 THEN 'New Customer'
        WHEN days_between_first_last = 0 THEN 'First Visit'
        WHEN (total_sales / GREATEST(days_between_first_last, 1)) >= 0.14 THEN 'Daily'  -- More than twice per week
        WHEN (total_sales / GREATEST(days_between_first_last, 1)) >= 0.07 THEN 'Weekly' -- At least once per week
        WHEN (total_sales / GREATEST(days_between_first_last, 1)) >= 0.03 THEN 'Monthly' -- At least once per month
        ELSE 'Quarterly'
    END;

    -- Update RegularCustomer table
    INSERT INTO RegularCustomer (CustomerID, AverageSpending, VisitFrequency)
    VALUES (customerId, avg_spending, visit_freq)
    ON DUPLICATE KEY UPDATE
        AverageSpending = avg_spending,
        VisitFrequency = visit_freq;
END$$

DELIMITER ;
