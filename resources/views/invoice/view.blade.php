<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
            padding: 10px;
        }
        
        /* Main container */
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* Header section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }
        
        .header-left {
            flex: 1;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: 700;
            color: #000;
            margin-top: 5px;
        }
        
        .company-address {
            font-size: 12px;
            color: #000;
            margin-top: 2px;
            line-height: 1.3;
        }
        
        .header-right {
            text-align: right;
            flex: 1;
        }
        
        .contact-info {
            font-size: 12px;
            color: #000;
            line-height: 1.3;
        }
        
        .contact-info strong {
            font-weight: 600;
        }
        
        /* Horizontal line */
        .divider-line {
            height: 2px;
            background-color: #000;
            margin: 5px 0;
        }
        
        /* Bill to and Invoice details section */
        .details-section {
            display: flex;
            margin-bottom: 10px;
        }
        
        .bill-to-section {
            flex: 1;
            padding-right: 10px;
        }
        
        .invoice-details-section {
            flex: 1;
            padding-left: 10px;
        }
        
        .section-title {
            background-color: #000;
            color: #fff;
            padding: 6px 10px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .customer-name {
            font-weight: 600;
            font-size: 13px;
            padding: 0 10px;
        }
        
        .invoice-info {
            padding: 0 10px;
            font-size: 13px;
            line-height: 1.5;
        }
        
        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .items-table thead th {
            background-color: #000;
            color: #fff;
            padding: 8px 5px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #000;
            font-size: 13px;
        }
        
        .items-table tbody td {
            padding: 6px 5px;
            border: 1px solid #000;
            text-align: center;
            font-size: 12px;
        }
        
        .items-table tbody td:nth-child(2) {
            text-align: left;
            font-weight: 600;
            padding-left: 8px;
        }
        
        .items-table tbody td:last-child {
            text-align: right;
            padding-right: 8px;
            font-weight: 600;
        }
        
        /* Amounts section */
        .amounts-title {
            background-color: #000;
            color: #fff;
            padding: 8px;
            font-weight: 600;
            text-align: center;
            font-size: 14px;
            margin-top: 15px;
            text-transform: uppercase;
        }
        
        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .amounts-table td {
            padding: 6px 10px;
            border: none;
            font-size: 13px;
        }
        
        .amounts-table td:first-child {
            font-weight: 600;
            width: 70%;
        }
        
        .amounts-table td:last-child {
            text-align: right;
            font-weight: 600;
            width: 30%;
        }
        
        .amounts-table tr:last-child td {
            border-top: 1px solid #000;
            padding-top: 8px;
            font-size: 14px;
        }
        
        .amounts-table tr:nth-last-child(2) td {
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }
        
        /* Amount in words section */
        .amount-in-words-section {
            border: 2px solid #000;
            padding: 12px;
            margin-top: 15px;
            font-size: 13px;
        }
        
        .amount-in-words-title {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        /* Terms and conditions */
        .terms-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #000;
        }
        
        .terms-title {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        /* Authorized signatory */
        .signatory-section {
            text-align: right;
            margin-top: 50px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        
        <!-- Header Section (Left aligned company info) -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="company-logo">
                    <img src="{{ url('img/company_logo.jpeg') }}" alt="Company Logo" style="max-height: 150px;">
                </div>
                {{-- <div class="invoice-title">Invoice</div> --}}
            </div>
            
            <div class="header-right">
                <div class="company-name">ALHASEED TRADERS</div>
                <div class="company-address">Shop no 5, plaza 153-O, Adjacent Layers Bakery, Bharia Town Phase 4, Islamabad</div>
            
                <div class="contact-info">
                    Phone no.: +923093324637<br>
                    Website: alhaseebtraders.com
                </div>
            </div>
        </div>
        
        <div class="divider-line"></div>
        
        <!-- Bill To and Invoice Details -->
        <div class="details-section">
            <div class="bill-to-section">
                <div class="section-title">Bill To</div>
                <div class="customer-name">{{ $invoice->customer_name ?? 'Bahria Shop' }}</div>
            </div>
            
            <div class="invoice-details-section">
                <div class="section-title">Invoice Details</div>
                <div class="invoice-info">
                    Invoice No. : {{ $invoice->invoice_code ?? '490' }}<br>
                    Date : {{ $invoice->created_at->format('d-m-Y') ?? '30-12-2025' }}<br>
                    Time : {{ $invoice->created_at->format('h:i A') ?? '02:28 PM' }}
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item name</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Price/ Unit</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                
                @if(isset($invoice->items) && count($invoice->items) > 0)
                    @foreach($invoice->items as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->item_name }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->unit ?? 'Pcs' }}</td>
                            <td>Rs {{ number_format($product->per_item_price, 0) }}</td>
                            <td>Rs {{ number_format($product->per_item_price * $product->quantity, 0) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $invoice->invoice_reference }}</td>
                        <td>{{ $invoice }}</td>
                        <td>{{ $invoice }}</td>
                        <td>Rs {{ number_format($item['price'], 0) }}</td>
                        <td>Rs {{ number_format($item['amount'], 0) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Amounts Section -->
        <div class="amounts-title">Amounts</div>
        <table class="amounts-table">
            <tr>
                <td>Sub Total</td>
                <td>Rs {{ isset($invoice) ? number_format($invoice->sub_total ?? $invoice->grand_total + ($invoice->discount ?? 0), 0) : '167,361' }}</td>
            </tr>
            
            @if(isset($invoice) && $invoice->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td>{{ $invoice->discount_amount }}%</td>
            </tr>
            @endif
            
            <tr>
                <td>Total</td>
                <td>Rs {{ isset($invoice) ? number_format($invoice->grand_total, 0) : '167,361' }}</td>
            </tr>
            
            <tr>
                <td>Received</td>
                <td>Rs {{ isset($invoice) ? number_format($invoice->received ?? 0, 0) : '0' }}</td>
            </tr>
            
            <tr>
                <td>Balance</td>
                <td>Rs {{ isset($invoice) ? number_format($invoice->balance ?? $invoice->grand_total - ($invoice->received ?? 0), 0) : '167,361' }}</td>
            </tr>
            
            @if(isset($invoice) && ($invoice->previous_balance ?? 0) > 0)
            <tr>
                <td>Previous Balance</td>
                <td>Rs {{ number_format($invoice->previous_balance, 0) }}</td>
            </tr>
            @endif
            
            <tr>
                <td>Current Balance</td>
                <td>Rs {{ isset($invoice) ? number_format($invoice->current_balance ?? ($invoice->balance ?? $invoice->grand_total) + ($invoice->previous_balance ?? 0), 0) : '167,361' }}</td>
            </tr>
        </table>
        
        <!-- Invoice Amount in Words -->
        <div class="amount-in-words-section">
            <div class="amount-in-words-title">Invoice Amount In Words</div>
            <span id="amountInWords">
            </span>
        </div>
        
        <!-- Terms and Conditions -->
        <div class="terms-section">
            <div class="terms-title">Terms and Conditions</div>
            Thanks for doing business with us!
        </div>
        
        <!-- Authorized Signatory -->
        <div class="signatory-section">
            For : ALHASEED TRADERS<br>
            Authorized Signatory
        </div>
        
    </div>
     <script>
        // Function to convert number to words
        function numberToWords(amount) {
            const units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
            const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            
            function convertLessThanThousand(num) {
                let words = '';
                
                if (num >= 100) {
                    words += units[Math.floor(num / 100)] + ' Hundred ';
                    num %= 100;
                }
                
                if (num >= 20) {
                    words += tens[Math.floor(num / 10)] + ' ';
                    num %= 10;
                } else if (num >= 10) {
                    words += teens[num - 10] + ' ';
                    num = 0;
                }
                
                if (num > 0) {
                    words += units[num] + ' ';
                }
                
                return words.trim();
            }
            
            if (amount === 0) return '';
            
            let result = '';
            let amountStr = Math.floor(amount).toString();
            
            // Handle crore
            if (amountStr.length > 7) {
                const crore = parseInt(amountStr.substring(0, amountStr.length - 7));
                result += convertLessThanThousand(crore) + ' Crore ';
                amountStr = amountStr.substring(amountStr.length - 7);
            }
            
            // Handle lakh
            if (amountStr.length > 5) {
                const lakh = parseInt(amountStr.substring(0, amountStr.length - 5));
                result += convertLessThanThousand(lakh) + ' Lakh ';
                amountStr = amountStr.substring(amountStr.length - 5);
            }
            
            // Handle thousand
            if (amountStr.length > 3) {
                const thousand = parseInt(amountStr.substring(0, amountStr.length - 3));
                result += convertLessThanThousand(thousand) + ' Thousand ';
                amountStr = amountStr.substring(amountStr.length - 3);
            }
            
            // Handle hundreds and below
            if (amountStr.length > 0) {
                const remaining = parseInt(amountStr);
                if (remaining > 0) {
                    result += convertLessThanThousand(remaining) + ' ';
                }
            }
            
            // Add Rupees and only
            result = result.trim() + ' Rupees only';
            
            // Capitalize first letter
            return result.charAt(0).toUpperCase() + result.slice(1);
        }
        
        // Function to get the total amount from the invoice
        function getInvoiceTotal() {
            // Try to get total from the amounts table
            const totalCell = document.querySelector('.amounts-table tr:nth-child(3) td:last-child');
            if (totalCell) {
                const totalText = totalCell.textContent.trim();
                // Extract number from text like "Rs 167,361"
                const match = totalText.match(/[\d,]+/);
                if (match) {
                    // Remove commas and convert to number
                    return parseInt(match[0].replace(/,/g, ''));
                }
            }
            
            // Fallback: Get from grand total in items table
            const grandTotalCell = document.querySelector('.items-table tfoot td.amount');
            if (grandTotalCell) {
                const totalText = grandTotalCell.textContent.trim();
                const match = totalText.match(/[\d,]+/);
                if (match) {
                    return parseInt(match[0].replace(/,/g, ''));
                }
            }
            
            // Default fallback
            return 167361;
        }
        
        // Function to update amount in words
        function updateAmountInWords() {
            const totalAmount = getInvoiceTotal();
            const words = numberToWords(totalAmount);
            document.getElementById('amountInWords').textContent = words;
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateAmountInWords();
            
            // Optional: Update when total changes (if you have dynamic updates)
            // You can call updateAmountInWords() whenever the total amount changes
        });
        
        // Optional: Make function available globally
        window.updateAmountInWords = updateAmountInWords;
        window.numberToWords = numberToWords;
    </script>
</body>
</html>