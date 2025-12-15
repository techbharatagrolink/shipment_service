<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Invoice - {{ $systemName }}</title>
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-bold { font-weight: bold; }
    .border { border: 1px solid #ccc; }
    .border-bottom { border-bottom: 2px solid #000; }
    .padding-8 { padding: 8px; }
  </style>
</head>
<body>

<!-- ================= HEADER TABLE ================= -->
<table cellpadding="8">
  <tr>
    <td>
      <img src="https://ik.imagekit.io/h7mvzndkk/seller.bharatagrolink.com/logo%20(1).png" width="90" crossorigin="anonymous">
    </td>
    <td class="text-right" style="font-weight:bold; font-size:16px;">
      {{ $totals['invoice_title'] }} / Cash Memo
    </td>
  </tr>
</table>

<!-- ================= CONTACT TABLE ================= -->
<table cellpadding="4">
  <tr>
    <td class="text-center" style="color:#666; font-size:13px;">
      A-Grade Input Marketplace for Farmers |
      <a href="https://www.bharatagrolink.com">www.bharatagrolink.com</a> |
      support@bharatagrolink.com | +91-6232-037-333
    </td>
  </tr>
</table>

<br>

<!-- ================= SELLER & INVOICE DETAILS TABLE ================= -->
<table cellpadding="8" class="border">
  <tr>
    <td width="50%" class="text-right">
      <strong>Invoice Details:</strong><br>
      Invoice No: {{ $productData[0]['invoice_number'] ?? 'N/A' }}<br>
      Invoice Date: {{ $orderData['create_date'] }}<br>
      Order ID: {{ $orderData['order_id'] }}<br>
      Order Date: {{ $orderData['create_date'] }}<br>
      Payment Mode: <strong>{{ $orderData['payment_mode'] }}</strong><br>
    </td>
  </tr>
</table>

<!-- ================= BUYER INFO TABLE ================= -->
<table cellpadding="8" class="border-bottom">
  <tr>
    <td>
      <strong>Bill To:</strong><br>
      {{ $orderData['fullname'] }}<br>
      {{ $orderData['fulladdress'] }}<br>
      {{ $orderData['state'] }} - {{ $orderData['pincode'] }}, {{ $orderData['city'] }}<br>
    </td>
  </tr>
</table>

<br>

<!-- ================= ITEMS TABLE ================= -->
<table cellpadding="6" border="1" style="font-size:11px;">
  <tr style="background:#f5f5f5;">
    <th>Sl.</th>
    <th>Description</th>
    <th>SKU</th>
    <th>Unit Price</th>
    <th>Qty</th>
    <th>Taxable Amount</th>
    <th>Tax Rate</th>
    <th>Tax Type</th>
    <th>CGST/SGST</th>
    <th>IGST</th>
    <th>Total Amount</th>
  </tr>

  @foreach ($productData as $index => $product)
    @if ($product['igst'] > 0)
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>
         {{ $product['prod_name'] }}<br>{{ $product['product_hsn_code'] }}
        </td>
        <td class="text-right">{{ $product['prod_sku'] }}</td>
        <td class="text-right">{{ $product['taxable_amount'] }}</td>
        <td class="text-center">{{ $product['qty'] }}</td>
        <td class="text-right">₹{{ $product['taxable_amount'] * $product['qty'] }}</td>
        <td class="text-center">{{ $product['gst_percentage'] }}%</td>
        <td class="text-center">IGST</td>
        <td class="text-right">-</td>
        <td class="text-right">₹{{ $product['igst'] * $product['qty'] }}</td>
        <td class="text-right font-bold">₹{{ $product['prod_price'] * $product['qty'] }}</td>
      </tr>
    @else
      <tr>
        <td rowspan="2" class="text-center">{{ $index + 1 }}</td>
        <td rowspan="2">
         {{ $product['prod_name'] }}
        </td>
        <td rowspan="2" class="text-right">{{ $product['prod_sku'] }}</td>
        <td rowspan="2" class="text-right">{{ $product['taxable_amount'] }}</td>
        <td rowspan="2" class="text-center">{{ $product['qty'] }}</td>
        <td rowspan="2" class="text-right">₹{{ $product['taxable_amount'] * $product['qty'] }}</td>
        <td class="text-center">{{ $product['gst_percentage'] / 2 }}%</td>
        <td class="text-center">CGST</td>
        <td class="text-right">₹{{ $product['cgst'] }}</td>
        <td rowspan="2" class="text-right">-</td>
        <td rowspan="2" class="text-right font-bold">₹{{ $product['prod_price'] * $product['qty'] }}</td>
      </tr>
      <tr>
        <td class="text-center">{{ $product['gst_percentage'] / 2 }}%</td>
        <td class="text-center">SGST</td>
        <td class="text-right">₹{{ $product['sgst'] }}</td>
      </tr>
    @endif
  @endforeach

  <tr class="font-bold">
    <td colspan="5" class="text-right">TOTAL:</td>
    <td class="text-right">₹{{ $totals['total_taxable'] }}</td>
    <td></td>
    <td></td>
    <td class="text-right">₹{{ $totals['total_cgst'] + $totals['total_sgst'] }}</td>
    <td class="text-right">₹{{ $totals['total_igst'] }}</td> 
    <td class="text-right">₹{{ $totals['total_price'] }}</td>
  </tr>
</table>

<br>
           
<!-- ================= SUMMARY TABLE ================= -->
<table cellpadding="5">
  <tr><td></td><td class="text-right">Total Taxable</td><td class="text-right">₹{{ $totals['total_taxable'] }}</td></tr>
  <tr><td></td><td class="text-right">Prepaid Discount</td><td class="text-right" style="color:red;">-₹{{ $totals['prepaid_discount'] }}</td></tr>
  <tr><td></td><td class="text-right">CGST </td><td class="text-right">₹{{ $totals['total_cgst'] }}</td></tr>
  <tr><td></td><td class="text-right">SGST </td><td class="text-right">₹{{ $totals['total_sgst'] }}</td></tr>
  <tr><td></td><td class="text-right">IGST </td><td class="text-right">₹{{ $totals['total_igst'] }}</td></tr>
  <tr><td></td><td class="text-right">Shipping Charges</td><td class="text-right">₹{{ $totals['total_shipping'] }}</td></tr>
  <tr>
    <td></td>
    <td class="text-right" style="border-top:2px solid #000; font-weight:bold;">Grand Total</td>
    <td class="text-right" style="border-top:2px solid #000; font-weight:bold;">₹{{ $totals['grand_total'] }}</td>
  </tr>
</table>
             
             
<!-- ================= AMOUNT IN WORDS TABLE ================= -->
<table cellpadding="8" style="border-top:1px solid #ccc;">
  <tr>
    <td>
      <strong>Amount in Words:</strong> {{ $amountInWords }}.
    </td>
  </tr>
</table>

<!-- ================= SOLD BY & FULLFILLED BY ================= -->
<table cellpadding="7">
  <tr>
    <td class="text-center" style="font-weight:bold;">Sold by {{ $sellerData['company_name'] ?? '' }}</td>
  </tr>
  <tr>
    <td class="text-center" style="font-weight:bold;">Fulfilled By Bharat Agrolink Logistics Partner</td>
  </tr>
</table>

<!-- ================= TERMS TABLE ================= -->
<table cellpadding="8">
  <tr>
    <td style="font-size:10px;">
      <strong>Terms & Conditions</strong><br>
      • This is a computer generated invoice and doesnt require signature.<br>
      • For Returns/Support: help@bharatagrolink.com | +91-6232-037-333 <br>
      • Bharat Agrolink operates solely as an online intermediary under the Information Technology Act, 2000. <br>
      • We do not manufacture, own, or control any products listed by vendors. <br>
      • All Products, licenses, certifications, and claims are the sole responsibility of the vendor.
    </td>
  </tr>
</table>

</body>
</html>
