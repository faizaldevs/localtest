# Depot Management System

## Supplier Payment Calculations

### Payment Amount Entry
The supplier payment amount is calculated based on the following factors:
- Total quantity of products collected during the specified period
- Weighted average cost calculation (total amount / total quantity)
- Previous payments made within the period are tracked and considered
- The system maintains a running total of all transactions

### Loan Deduction Entry
The payment process handles deductions through the following steps:
1. You enter the full payment amount (e.g., 50)
2. You enter any loan deduction amount (e.g., 10)
3. The system then:
   - Stores the full payment amount (50) as 'paid_amount'
   - Stores the loan deduction (10) separately
   - Calculates the final amount paid by subtracting the loan deduction: 50 - 10 = 40
   - Records staff discrepancies with amounts and notes if any
   - Tracks staff deductions with a 'pending' status for review

The system ensures transparency by:
- Maintaining separate records for payments and deductions
- Tracking payment periods with clear start and end dates
- Recording detailed notes for both supplier payments and staff discrepancies
- Providing a complete audit trail of all financial transactions