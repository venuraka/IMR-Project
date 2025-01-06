-- Create Database
CREATE DATABASE possystem;
USE possystem;

-- Employee Table
CREATE TABLE Employee (
    EmployeeId INT AUTO_INCREMENT PRIMARY KEY,
    DOB DATE,
    JoinDate DATE,
    Address VARCHAR(255),
    FirstName VARCHAR(100),
    LastName VARCHAR(100),
    Contact VARCHAR(15),
    Email VARCHAR(100) UNIQUE
);

-- Customer Table
CREATE TABLE Customer (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(100),
    LastName VARCHAR(100),
    Contact VARCHAR(15),
    Address VARCHAR(255),
    Email VARCHAR(100) UNIQUE
);

-- Supplier Table
CREATE TABLE Supplier (
    SupplierID INT AUTO_INCREMENT PRIMARY KEY,
    Address VARCHAR(255),
    SupplierName VARCHAR(100),
    Contact VARCHAR(15),
    Email VARCHAR(100) UNIQUE
);

-- Category Table
CREATE TABLE Category (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100)
);

-- Product Table
CREATE TABLE Product (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    ProductName VARCHAR(100),
    Price DECIMAL(10, 2),
    CostPrice DECIMAL(10, 2),
    SupplierID INT,
    CategoryID INT,
    FOREIGN KEY (SupplierID) REFERENCES Supplier(SupplierID),
    FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID)
);

-- Sale Table
CREATE TABLE Sale (
    SaleID INT AUTO_INCREMENT PRIMARY KEY,
    TotalAmount DECIMAL(10, 2),
    SaleDate DATE,
    CustomerID INT,
    EmployeeID INT,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID),
    FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeId)
);

-- SaleDetail Table
CREATE TABLE SaleDetail (
    SaleID INT,
    ProductID INT,
    Quantity INT,
    Subtotal DECIMAL(10, 2),
    PRIMARY KEY (SaleID, ProductID),
    FOREIGN KEY (SaleID) REFERENCES Sale(SaleID),
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
);

-- ProductSale Table
CREATE TABLE ProductSale (
    SaleID INT,
    ProductID INT,
    PRIMARY KEY (SaleID, ProductID),
    FOREIGN KEY (SaleID) REFERENCES Sale(SaleID),
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
);

-- Discount Table
CREATE TABLE Discount (
    ProductID INT PRIMARY KEY,
    DiscountName VARCHAR(100),
    DiscountPercentage DECIMAL(5, 2),
    ValidFrom DATE,
    ValidTo DATE,
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
);

-- Payment Table
CREATE TABLE Payment (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    PaymentType VARCHAR(50),
    PaymentDate DATE,
    PaymentAmount DECIMAL(10, 2),
    SaleID INT,
    FOREIGN KEY (SaleID) REFERENCES Sale(SaleID)
);

-- Stock Table
CREATE TABLE Stock (
    StockID INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE,
    Quantity INT,
    EmployeeID INT,
    FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeId)
);

-- StockProduct Table
CREATE TABLE StockProduct (
    StockID INT,
    ProductID INT,
    Quantity INT NOT NULL DEFAULT 0,
    PRIMARY KEY (StockID, ProductID),
    FOREIGN KEY (StockID) REFERENCES Stock(StockID),
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
);

-- Report Table
CREATE TABLE Report (
    ReportID INT AUTO_INCREMENT PRIMARY KEY,
    ReportType VARCHAR(50),
    Recipient VARCHAR(100),
    Date DATE,
    Content TEXT,
    EmployeeID INT,
    FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeId)
);

-- StockSaleReport Table
CREATE TABLE StockSaleReport (
    StockID INT,
    ReportID INT,
    SaleID INT,
    PRIMARY KEY (StockID, ReportID, SaleID),
    FOREIGN KEY (StockID) REFERENCES Stock(StockID),
    FOREIGN KEY (ReportID) REFERENCES Report(ReportID),
    FOREIGN KEY (SaleID) REFERENCES Sale(SaleID)
);

-- Cashier Table
CREATE TABLE Cashier (
    POS INT,
    EmployeeID INT,
    PRIMARY KEY (POS, EmployeeID),
    FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeId)
);

-- Manager Table
CREATE TABLE Manager (
    Privileges TEXT,
    EmployeeID INT PRIMARY KEY,
    FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeId)
);

-- RegularCustomer Table
CREATE TABLE RegularCustomer (
    AverageSpending DECIMAL(10, 2),
    VisitFrequency VARCHAR(50),
    CustomerID INT PRIMARY KEY,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
);

-- LoyaltyCustomer Table
CREATE TABLE LoyaltyCustomer (
    LoyaltyCardNo INT,
    PointsEarned INT,
    CustomerID INT PRIMARY KEY,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
);

-- Loyalty Table
CREATE TABLE Loyalty (
    LoyaltyPoints INT,
    CustomerID INT PRIMARY KEY,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
);


-- Trigger to Update SaleDetail and Sale TotalAmount

DELIMITER $$

DROP TRIGGER IF EXISTS AfterInsertProductSale$$

CREATE TRIGGER AfterInsertProductSale
AFTER INSERT ON ProductSale
FOR EACH ROW
BEGIN
    DECLARE product_price DECIMAL(10,2);
    DECLARE sale_quantity INT;
    DECLARE sale_subtotal DECIMAL(10,2);
    DECLARE sale_total DECIMAL(10,2);

    -- Get the product price and quantity
    SELECT 
        p.Price,
        sd.Quantity 
    INTO product_price, sale_quantity
    FROM Product p
    JOIN SaleDetail sd ON sd.ProductID = p.ProductID
    WHERE p.ProductID = NEW.ProductID 
    AND sd.SaleID = NEW.SaleID;

    -- Calculate and update subtotal
    SET sale_subtotal = product_price * sale_quantity;
    
    UPDATE SaleDetail 
    SET Subtotal = sale_subtotal
    WHERE SaleID = NEW.SaleID 
    AND ProductID = NEW.ProductID;

    -- Update Sale total
    SELECT COALESCE(SUM(Subtotal), 0) 
    INTO sale_total
    FROM SaleDetail
    WHERE SaleID = NEW.SaleID;

    UPDATE Sale
    SET TotalAmount = sale_total
    WHERE SaleID = NEW.SaleID;
END$$

DELIMITER ;

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
