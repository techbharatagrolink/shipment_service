<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate invoice PDF string for a given order ID
     */
    public function generatePdfString(string $orderId): ?string
    {
        // Fetch order data
        $orderData = $this->fetchOrderData($orderId);

        if (!$orderData) {
            return null;
        }

        // Fetch product data
        $productData = $this->fetchProductData($orderId);

        if (empty($productData)) {
            return null;
        }

        // Fetch seller data
        $sellerData = $this->fetchSellerData($productData[0]['vendor_id']);

        // Calculate totals
        $totals = $this->calculateTotals($productData, $orderData);

        // Get system settings
        $systemName = $this->getSystemSettings('system_name') ?: 'Bharat Agrolink';
        $currency = $this->getSystemSettings('system_currency_symbol') ?: '₹';

        // Generate PDF
        $pdf = Pdf::loadView('invoices.invoice', [
            'orderData' => $orderData,
            'productData' => $productData,
            'sellerData' => $sellerData,
            'totals' => $totals,
            'systemName' => $systemName,
            'currency' => $currency,
            'amountInWords' => $this->amountToWords($totals['net_payable']),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('compress', true);
        $pdf->setOption('dpi', 96); // Screen quality, smaller size

        return $pdf->output();
    }

    /**
     * Fetch order data from database
     */
    private function fetchOrderData(string $orderId): ?array
    {
        $order = DB::connection('mysql2')->select("
            SELECT 
                o.order_id, o.user_id, o.status, o.total_price, o.payment_orderid, 
                o.payment_id, o.payment_mode, o.qoute_id, DATE(o.create_date) as create_date,
                o.discount, o.total_qty, o.fullname, o.mobile, o.area, o.fulladdress, 
                o.city, o.state, o.country, o.addresstype, o.email, o.gstin, 
                o.company_name, o.pincode
            FROM orders o 
            WHERE o.order_id = ?
        ", [$orderId]);

        if (empty($order)) {
            return null;
        }

        $order = (array) $order[0];

        // Get country name
        $countryName = '';
        if (!empty($order['country'])) {
            $country = DB::connection('mysql2')->select("SELECT name FROM country WHERE id = ?", [$order['country']]);
            if (!empty($country)) {
                $countryName = $country[0]->name;
            }
        }

        return [
            'order_id' => $order['order_id'],
            'user_id' => $order['user_id'],
            'status' => $order['status'],
            'total_price' => $order['total_price'],
            'payment_orderid' => $order['payment_orderid'],
            'payment_id' => $order['payment_id'],
            'payment_mode' => $order['payment_mode'],
            'qoute_id' => $order['qoute_id'],
            'create_date' => date('d-m-Y', strtotime($order['create_date'])),
            'discount' => $order['discount'],
            'total_qty' => $order['total_qty'],
            'fullname' => $order['fullname'],
            'mobile' => $order['mobile'],
            'area' => $order['area'],
            'fulladdress' => $order['fulladdress'],
            'city' => $order['city'],
            'state' => $order['state'],
            'country' => $countryName,
            'addresstype' => $order['addresstype'],
            'email' => $order['email'],
            'gstin' => $order['gstin'],
            'company_name' => $order['company_name'],
            'pincode' => $order['pincode'],
        ];
    }

    /**
     * Fetch product data for an order
     */
    private function fetchProductData(string $orderId): array
    {
        $products = DB::connection('mysql2')->select("
            SELECT 
                op.prod_id, op.prod_sku, op.prod_name, op.prod_img, op.prod_attr, 
                op.qty, op.prod_price, op.shipping, op.discount, op.status, 
                sl.companyname as seller, op.invoice_number, op.vendor_id, 
                op.product_hsn_code, op.product_unique_code, op.taxable_amount, 
                op.cgst, op.sgst, op.igst, op.gst_percentage, op.seller_price, 
                op.gross_amount, op.net_amount, op.tracking_qr
            FROM order_product op
            JOIN sellerlogin sl ON sl.seller_unique_id = op.vendor_id
            WHERE op.order_id = ?
        ", [$orderId]);

        $productData = [];
        foreach ($products as $product) {
            $product = (array) $product;

            // Parse product attributes
            $prodAttr = '';
            if (!empty($product['prod_attr'])) {
                $attr = json_decode($product['prod_attr']);
                $attribute = '';
                if (is_array($attr) || is_object($attr)) {
                    foreach ($attr as $attrItem) {
                        $attribute .= $attrItem->attr_name . ': ' . $attrItem->item . ', ';
                    }
                }
                $prodAttr = rtrim($attribute, ', ');
            }

            $productData[] = [
                'prod_id' => $product['prod_id'],
                'prod_sku' => $product['prod_sku'],
                'prod_name' => $product['prod_name'],
                'prod_img' => $product['prod_img'],
                'prod_attr_text' => $prodAttr,
                'qty' => $product['qty'],
                'prod_price' => $product['prod_price'],
                'shipping' => $product['shipping'],
                'discount' => $product['discount'],
                'status' => $product['status'],
                'seller' => $product['seller'],
                'invoice_number' => $product['invoice_number'],
                'vendor_id' => $product['vendor_id'],
                'product_hsn_code' => $product['product_hsn_code'],
                'product_unique_code' => $product['product_unique_code'],
                'taxable_amount' => $product['taxable_amount'],
                'cgst' => $product['cgst'],
                'sgst' => $product['sgst'],
                'igst' => $product['igst'],
                'gst_percentage' => $product['gst_percentage'],
                'seller_price' => $product['seller_price'],
                'gross_amount' => $product['gross_amount'],
                'net_amount' => $product['net_amount'],
            ];
        }

        return $productData;
    }

    /**
     * Fetch seller/vendor data
     */
    private function fetchSellerData(string $vendorId): array
    {
        $seller = DB::connection('mysql2')->select("
            SELECT 
                companyname, fullname, address, city, pincode, state, country, 
                phone, email, logo, tax_number, pan_number, cin_number
            FROM sellerlogin 
            WHERE seller_unique_id = ?
        ", [$vendorId]);

        if (empty($seller)) {
            return [];
        }

        $seller = (array) $seller[0];

        // If tax number is empty, get from admin
        $taxNumber = $seller['tax_number'];
        if (empty($taxNumber)) {
            $admin = DB::connection('mysql2')->select("SELECT gst_no FROM admin_login WHERE seller_id = 1");
            if (!empty($admin)) {
                $taxNumber = $admin[0]->gst_no ?? '';
            }
        }

        return [
            'company_name' => $seller['companyname'],
            'fullname' => $seller['fullname'],
            'address' => $seller['address'],
            'city' => $seller['city'],
            'pincode' => $seller['pincode'],
            'state' => $seller['state'],
            'country' => $seller['country'],
            'phone' => $seller['phone'],
            'email' => $seller['email'],
            'logo' => $seller['logo'],
            'tax_number' => $taxNumber,
            'pan_number' => $seller['pan_number'] ?? '',
            'cin_number' => $seller['cin_number'] ?? '',
        ];
    }

    /**
     * Calculate totals, taxes, and discounts
     */
    private function calculateTotals(array $productData, array $orderData): array
    {
        $totalTaxable = 0;
        $totalPrice = 0;
        $totalTax = 0;
        $totalIgst = 0;
        $totalCgst = 0;
        $totalSgst = 0;
        $totalShipping = 0;

        foreach ($productData as $product) {
            $totalShipping += $product['shipping'];
            $totalTaxable += ($product['taxable_amount'] * $product['qty']);
            $totalPrice += $product['prod_price'] * $product['qty'];
            $totalTax += round($product['taxable_amount'] * $product['gst_percentage'] / 100, 2);
            $totalIgst += $product['igst'] * $product['qty'];
            $totalCgst += $product['cgst'] * $product['qty'];
            $totalSgst += $product['sgst'] * $product['qty'];
        }

        $gstTotal = $totalCgst + $totalSgst + $totalIgst;

        // Calculate invoice title based on GST
        $invoiceTitle = $gstTotal == 0 ? 'Bill Of Supply' : 'Tax Invoice';

        // Calculate prepaid discount (3% for prepaid orders)
        $prepaidDiscount = 0;
        if ($orderData['payment_mode'] === 'prepaid') {
            $prepaidDiscount = round($totalPrice * 0.03);
        }

        // Calculate net payable
        if ($totalPrice < 500) {
            $netPayable = ($orderData['payment_mode'] === 'prepaid' ? round($totalPrice * 0.97) : $totalPrice) + $totalShipping;
        } else {
            $netPayable = ($orderData['payment_mode'] === 'prepaid' ? round($totalPrice * 0.97) : round($totalPrice));
        }

        return [
            'total_taxable' => $totalTaxable,
            'total_price' => $totalPrice,
            'total_tax' => $totalTax,
            'total_igst' => $totalIgst,
            'total_cgst' => $totalCgst,
            'total_sgst' => $totalSgst,
            'total_shipping' => $totalShipping,
            'gst_total' => $gstTotal,
            'invoice_title' => $invoiceTitle,
            'prepaid_discount' => $prepaidDiscount,
            'net_payable' => $netPayable,
            'grand_total' => $totalPrice - $prepaidDiscount,
        ];
    }

    /**
     * Get system settings by type
     */
    private function getSystemSettings(string $type): string
    {
        $setting = DB::connection('mysql2')->select("
            SELECT description 
            FROM settings 
            WHERE type = ?
        ", [$type]);

        return !empty($setting) ? $setting[0]->description : '';
    }

    /**
     * Convert amount to words (Indian currency format)
     */
    private function amountToWords(float $number): string
    {
        $no = floor($number);
        $decimal = round($number - $no, 2) * 100;

        $words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety',
        ];

        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        $str = [];

        $i = 0;
        while ($no > 0) {
            $divider = ($i == 2) ? 10 : 100;
            $numberPart = $no % $divider;
            $no = intdiv($no, $divider);

            if ($numberPart) {
                $plural = (($counter = count($str)) && $numberPart > 9) ? 's' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';

                if ($numberPart < 21) {
                    $str[] = $words[$numberPart] . ' ' . $digits[$i] . $plural . $hundred;
                } else {
                    $unit = $numberPart % 10;
                    $ten = $numberPart - $unit;
                    $str[] = $words[$ten] . ' ' . $words[$unit] . ' ' . $digits[$i] . $plural . $hundred;
                }
            } else {
                $str[] = null;
            }
            $i++;
        }

        $rupees = implode(' ', array_reverse(array_filter($str)));
        $paise = ($decimal > 0) ? ' and ' . $words[($decimal - $decimal % 10)] . ' ' . $words[$decimal % 10] . ' Paise' : '';

        if ($rupees == '' && $paise == '') {
            return 'Zero Rupees Only';
        }

        return trim($rupees . ' Rupees' . $paise . ' Only');
    }
}
