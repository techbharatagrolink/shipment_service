# Shipment Service API

A comprehensive Laravel-based shipment management service integrating with **Shiprocket** and **Delhivery** courier platforms.

## 🚀 Features

- **Dual Courier Integration**: Shiprocket and Delhivery APIs
- **Complete Order Management**: Create, update, track, and cancel shipments
- **Webhook Support**: Real-time shipment status updates
- **Authentication**: Sanctum-based API authentication
- **High Performance**: Laravel Octane for concurrent request handling

---

## 📋 Table of Contents

- [Getting Started](#getting-started)
- [Authentication](#authentication)
- [Shiprocket APIs](#shiprocket-apis)
- [Delhivery APIs](#delhivery-apis)
- [Webhooks](#webhooks)

---

## 🔧 Getting Started

### Prerequisites

- PHP 8.4+
- Laravel 12
- MySQL
- Composer

### Installation

```bash
# Clone repository
git clone <repository-url>
cd ShipmentService

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan octane:start
```

### Environment Variables

```env
# Shiprocket
SHIPROCKET_API_EMAIL=your-email@example.com
SHIPROCKET_API_PASSWORD=your-password

# Delhivery
DELHIVERY_API_KEY=your-api-key

# WhatsApp (AISensy)
AISENSY_API_KEY=your-api-key
```

---

## 🔐 Authentication

All API endpoints (except webhooks) require authentication using Laravel Sanctum.

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|abcdefghijklmnopqrstuvwxyz",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com"
  }
}
```

### Using the Token

Include the token in the `Authorization` header for all subsequent requests:

```http
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz
```

---

## 📦 Shiprocket APIs

### Orders

#### Create Order

```http
POST /api/orders/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": "ORD123456",
  "order_date": "2024-12-11 10:30",
  "pickup_location": "Primary",
  "billing_customer_name": "John",
  "billing_last_name": "Doe",
  "billing_address": "123 Main St",
  "billing_city": "Mumbai",
  "billing_pincode": 400001,
  "billing_state": "Maharashtra",
  "billing_country": "India",
  "billing_email": "john@example.com",
  "billing_phone": 9876543210,
  "shipping_is_billing": true,
  "order_items": [
    {
      "name": "Product Name",
      "sku": "SKU123",
      "units": 1,
      "selling_price": 999,
      "discount": 0,
      "tax": 0,
      "hsn": 441122
    }
  ],
  "payment_method": "Prepaid",
  "shipping_charges": 0,
  "giftwrap_charges": 0,
  "transaction_charges": 0,
  "total_discount": 0,
  "sub_total": 999,
  "length": 10,
  "breadth": 10,
  "height": 10,
  "weight": 0.5
}
```

#### Update Order

```http
POST /api/orders/update
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": "ORD123456",
  "order_date": "2024-12-11",
  "pickup_location": "Primary",
  "billing_customer_name": "John",
  "billing_address": "Updated Address",
  ...
}
```

#### Create Return Order

```http
POST /api/orders/create/return
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": "RET123456",
  "order_date": "2024-12-11",
  "pickup_customer_name": "John Doe",
  "pickup_address": "123 Main St",
  "pickup_city": "Mumbai",
  "pickup_state": "Maharashtra",
  "pickup_country": "India",
  "pickup_pincode": 400001,
  "pickup_email": "john@example.com",
  "pickup_phone": "9876543210",
  "shipping_customer_name": "Warehouse",
  "shipping_address": "456 Warehouse St",
  "shipping_city": "Mumbai",
  "shipping_country": "India",
  "shipping_pincode": 400002,
  "shipping_state": "Maharashtra",
  "shipping_phone": 9876543211,
  "order_items": [
    {
      "name": "Product Name",
      "sku": "SKU123",
      "units": 1,
      "selling_price": 999
    }
  ],
  "payment_method": "Prepaid",
  "sub_total": 999,
  "length": 10,
  "breadth": 10,
  "height": 10,
  "weight": 0.5
}
```

#### Create Exchange Order

```http
POST /api/orders/create/exchange
Authorization: Bearer {token}
Content-Type: application/json

{
  "exchange_order_id": "EXC123456",
  "seller_pickup_location_id": "Primary",
  "seller_shipping_location_id": "Primary",
  "return_order_id": "RET123456",
  "order_date": "2024-12-11",
  "payment_method": "Prepaid",
  "buyer_shipping_first_name": "John",
  "buyer_shipping_email": "john@example.com",
  "buyer_shipping_address": "123 Main St",
  "buyer_shipping_city": "Mumbai",
  "buyer_shipping_state": "Maharashtra",
  "buyer_shipping_country": "India",
  "buyer_shipping_pincode": "400001",
  "buyer_shipping_phone": "9876543210",
  "order_items": [
    {
      "name": "New Product",
      "selling_price": 1299,
      "units": 1,
      "sku": "SKU456",
      "exchange_item_id": "ITEM123",
      "exchange_item_name": "Old Product",
      "exchange_item_sku": "SKU123"
    }
  ],
  "sub_total": 1299,
  "return_length": 10,
  "return_breadth": 10,
  "return_height": 10,
  "return_weight": 0.5,
  "exchange_length": 10,
  "exchange_breadth": 10,
  "exchange_height": 10,
  "exchange_weight": 0.5,
  "return_reason": "Size issue"
}
```

#### Get All Orders

```http
GET /api/shipments/orders?page=1&per_page=20
Authorization: Bearer {token}
```

#### Get Order by ID

```http
GET /api/shipments/orders/{orderId}
Authorization: Bearer {token}
```

#### Cancel Order

```http
POST /api/orders/cancel
Authorization: Bearer {token}
Content-Type: application/json

{
  "ids": [123456, 123457]
}
```

---

### Shipments

#### Create Forward Shipment

```http
POST /api/shipments/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": "ORD123456",
  "order_date": "2024-12-11",
  "pickup_location": "Primary",
  "billing_customer_name": "John",
  "billing_address": "123 Main St",
  "billing_city": "Mumbai",
  "billing_state": "Maharashtra",
  "billing_country": "India",
  "billing_pincode": 400001,
  "billing_email": "john@example.com",
  "billing_phone": 9876543210,
  "shipping_is_billing": true,
  "order_items": [
    {
      "name": "Product",
      "sku": "SKU123",
      "units": 1,
      "selling_price": 999
    }
  ],
  "payment_method": "Prepaid",
  "sub_total": 999,
  "length": 10,
  "breadth": 10,
  "height": 10,
  "weight": 0.5
}
```

#### Cancel Shipment

```http
POST /api/shipments/cancel
Authorization: Bearer {token}
Content-Type: application/json

{
  "awbs": ["AWB123456", "AWB123457"]
}
```

---

### Tracking

#### Track by Shipment ID

```http
GET /api/shipments/track/shipment/{shipmentId}
Authorization: Bearer {token}
```

#### Track by AWB

```http
GET /api/shipments/track/awb/{awb}
Authorization: Bearer {token}
```

#### Track Multiple AWBs

```http
POST /api/shipments/track/awbs
Authorization: Bearer {token}
Content-Type: application/json

{
  "awbs": ["AWB123456", "AWB123457", "AWB123458"]
}
```

#### Track by Order

```http
GET /api/shipments/track/order?order_id=ORD123456
Authorization: Bearer {token}
```

---

### Labels & Documents

#### Generate AWB

```http
POST /api/orders/generateAWB
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipment_id": "123456",
  "courier_id": "1",
  "is_return": "0"
}
```

#### Generate Manifest

```http
POST /api/orders/generateManifest
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipment_id": [123456, 123457]
}
```

#### Generate Label

```http
POST /api/orders/generateLabel
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipment_id": [123456]
}
```

#### Generate Invoice

```http
POST /api/orders/generateInvoice
Authorization: Bearer {token}
Content-Type: application/json

{
  "ids": [123456]
}
```

#### Generate Pickup

```http
POST /api/orders/generatePickup
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipment_id": [123456]
}
```

---

### Warehouse & Logistics

#### Get All Warehouses

```http
GET /api/warehouse
Authorization: Bearer {token}
```

#### Create Warehouse

```http
POST /api/warehouse/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "pickup_location": "My Warehouse",
  "name": "John Doe",
  "email": "warehouse@example.com",
  "phone": 9876543210,
  "address": "123 Warehouse St",
  "city": "Mumbai",
  "state": "Maharashtra",
  "country": "India",
  "pin_code": 400001
}
```

#### Check Serviceability

```http
GET /api/shipments/serviceability?pickup_postcode=400001&delivery_postcode=560001&weight=0.5&cod=0
Authorization: Bearer {token}
```

#### Get All Couriers

```http
GET /api/shipments/couriers
Authorization: Bearer {token}
```

---

### Products & Inventory

#### Get All Products

```http
GET /api/products?page=1&per_page=20
Authorization: Bearer {token}
```

#### Get Product Details

```http
GET /api/products/{product_id}
Authorization: Bearer {token}
```

#### Add New Product

```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "sku": "SKU123",
  "name": "Product Name",
  "type": "Single",
  "qty": 100,
  "category_code": "electronics"
}
```

#### Get Inventory

```http
POST /api/inventory
Authorization: Bearer {token}
Content-Type: application/json

{
  "page": 1,
  "per_page": 20
}
```

#### Update Inventory

```http
PUT /api/inventory/{product_id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "inventory": [
    {
      "pickup_location": "Primary",
      "qty": 50
    }
  ]
}
```

---

### Financial

#### Get Wallet Balance

```http
GET /api/wallet
Authorization: Bearer {token}
```

#### Get Statement

```http
GET /api/statement?from=2024-01-01&to=2024-12-31
Authorization: Bearer {token}
```

#### Get Discrepancy

```http
GET /api/discrepancy
Authorization: Bearer {token}
```

---

## 🚚 Delhivery APIs

### Orders

#### Create Order

```http
POST /api/delhivery/orders/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipments": [
    {
      "name": "John Doe",
      "add": "123 Main Street, Near Park",
      "pin": "110001",
      "city": "New Delhi",
      "state": "Delhi",
      "country": "India",
      "phone": "9876543210",
      "order": "ORD123456",
      "payment_mode": "Prepaid",
      "weight": 0.5,
      "products_desc": "Electronics",
      "hsn_code": "8517",
      "seller_name": "My Store",
      "seller_add": "Warehouse Address",
      "total_amount": 999
    }
  ]
}
```

#### Edit Shipment

```http
PUT /api/delhivery/orders/edit/{waybill}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "phone": "9876543211",
  "add": "Updated Address"
}
```

#### Cancel Shipment

```http
DELETE /api/delhivery/orders/cancel/{waybill}
Authorization: Bearer {token}
```

---

### Tracking

#### Track Shipment

```http
GET /api/delhivery/track/{waybill}
Authorization: Bearer {token}
```

#### Track Multiple Shipments

```http
POST /api/delhivery/track/multiple
Authorization: Bearer {token}
Content-Type: application/json

{
  "waybills": ["WB001", "WB002", "WB003"]
}
```

---

### Labels & Documents

#### Generate Label

```http
GET /api/delhivery/label/{waybill}
Authorization: Bearer {token}
```

**Response:** PDF label URL or content

#### Fetch Waybill

```http
GET /api/delhivery/waybill/{clientOrderId}
Authorization: Bearer {token}
```

---

### Pickup & Logistics

#### Create Pickup Request

```http
POST /api/delhivery/pickup/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "pickup_location": "Main Warehouse",
  "name": "Warehouse Manager",
  "add": "123 Warehouse St",
  "city": "Mumbai",
  "pin_code": "400001",
  "country": "India",
  "phone": "9876543210",
  "email": "warehouse@example.com"
}
```

#### Check Serviceability

```http
GET /api/delhivery/serviceability?from_pincode=110001&to_pincode=560001&payment_mode=Prepaid&weight=0.5
Authorization: Bearer {token}
```

#### Calculate Shipping Cost

```http
POST /api/delhivery/calculate-cost
Authorization: Bearer {token}
Content-Type: application/json

{
  "md": "Prepaid",
  "ss": "Delivered",
  "o_pin": "110001",
  "d_pin": "560001",
  "cgm": 0.5
}
```

#### Pincode Serviceability

```http
GET /api/delhivery/pincode-serviceability?pincode=110001
Authorization: Bearer {token}
```

---

### NDR Management

#### Update NDR Shipment

```http
PUT /api/delhivery/ndr/update/{waybill}
Authorization: Bearer {token}
Content-Type: application/json

{
  "action": "re_attempt",
  "remarks": "Customer requested re-delivery"
}
```

**Actions:**
- `re_attempt` - Schedule re-delivery
- `rto` - Return to origin

---

## 🔔 Webhooks

Webhooks receive real-time updates from courier partners. No authentication required.

### Shiprocket Webhook

```http
POST /api/webhook
Content-Type: application/json

{
  "order_id": "ORD123456",
  "shipment_id": "123456",
  "awb": "AWB123456",
  "current_status": "DELIVERED",
  "delivered_date": "2024-12-11 15:30:00"
}
```

### Delhivery Webhook

```http
POST /api/webhook/delhivery
Content-Type: application/json

{
  "waybill": "WB123456",
  "status": "Delivered",
  "scan_datetime": "2024-12-11 15:30:00",
  "location": "Mumbai"
}
```

### Order Sync Endpoint

```http
POST /api/syncorder/{order_id}
Content-Type: application/json
```

---

## 📊 Response Format

### Success Response

```json
{
  "success": true,
  "data": {
    ...
  }
}
```

### Error Response

```json
{
  "error": "Error message",
  "details": {
    ...
  }
}
```

---

## 🛠️ Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 500 | Internal Server Error |

---

## 📚 Additional Information

### Tech Stack

- **Framework**: Laravel 12
- **PHP**: 8.4.14
- **Server**: Laravel Octane (FrankenPHP)
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **Queue**: Redis
- **Testing**: Pest

### Key Packages

- `laravel/octane` - High-performance server
- `laravel/sanctum` - API authentication
- `laravel/breeze` - Auth scaffolding
- `pestphp/pest` - Testing framework

---

## 📝 License

This project is licensed under the MIT License.

---

## 🤝 Support

For support, email rp7098979@gmail.com or create an issue in the repository.
