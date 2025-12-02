# API Documentation

This document shows example JSON responses for all API endpoints with realistic e-commerce data.

---

## API Routes Overview

All routes are defined in [`routes/api.php`](src/routes/api.php) and are automatically prefixed with `/api`.

### Available Endpoints

**Users** (`/api/users`)
- `GET /api/users` - List all users
- `POST /api/users` - Create a new user
- `GET /api/users/{id}` - Show a specific user with orders
- `PUT /api/users/{id}` - Update a user
- `DELETE /api/users/{id}` - Delete a user

**Categories** (`/api/categories`)
- `GET /api/categories` - List all categories with product count
- `POST /api/categories` - Create a new category
- `GET /api/categories/{id}` - Show a specific category with products
- `PUT /api/categories/{id}` - Update a category
- `DELETE /api/categories/{id}` - Delete a category

**Products** (`/api/products`)
- `GET /api/products` - List all products with category
- `POST /api/products` - Create a new product
- `GET /api/products/{id}` - Show a specific product with category
- `PUT /api/products/{id}` - Update a product
- `DELETE /api/products/{id}` - Delete a product

**Orders** (`/api/orders`)
- `GET /api/orders` - List all orders with user and items
- `POST /api/orders` - Create a new order
- `GET /api/orders/{id}` - Show a specific order with details
- `PUT /api/orders/{id}` - Update an order
- `DELETE /api/orders/{id}` - Delete an order

**Order Items** (`/api/order-items`)
- `GET /api/order-items` - List all order items
- `POST /api/order-items` - Create a new order item
- `GET /api/order-items/{id}` - Show a specific order item
- `PUT /api/order-items/{id}` - Update an order item
- `DELETE /api/order-items/{id}` - Delete an order item

> **Note**: All routes use Laravel's `apiResource` which automatically creates RESTful routes with proper HTTP methods (GET, POST, PUT/PATCH, DELETE).

---

## Categories

### GET `/api/categories` - List All Categories

```json
[
  {
    "id": 1,
    "category_name": "Electronics",
    "products_count": 15,
    "created_at": "2025-11-22T10:15:00.000000Z",
    "updated_at": "2025-11-22T10:15:00.000000Z"
  },
  {
    "id": 2,
    "category_name": "Clothing",
    "products_count": 42,
    "created_at": "2025-11-22T10:16:00.000000Z",
    "updated_at": "2025-11-22T10:16:00.000000Z"
  },
  {
    "id": 3,
    "category_name": "Home & Garden",
    "products_count": 28,
    "created_at": "2025-11-22T10:17:00.000000Z",
    "updated_at": "2025-11-22T10:17:00.000000Z"
  }
]
```

### GET `/api/categories/1` - Show Single Category with Products

```json
{
  "id": 1,
  "category_name": "Electronics",
  "created_at": "2025-11-22T10:15:00.000000Z",
  "updated_at": "2025-11-22T10:15:00.000000Z",
  "products": [
    {
      "id": 1,
      "category_id": 1,
      "name": "Wireless Headphones",
      "description": "Premium noise-cancelling wireless headphones with 30-hour battery life",
      "price": "149.99",
      "stock_quantity": 50,
      "image_url": "https://example.com/images/headphones.jpg",
      "created_at": "2025-11-22T10:20:00.000000Z",
      "updated_at": "2025-11-22T10:20:00.000000Z"
    },
    {
      "id": 2,
      "category_id": 1,
      "name": "Smart Watch",
      "description": "Fitness tracking smartwatch with heart rate monitor",
      "price": "299.99",
      "stock_quantity": 30,
      "image_url": "https://example.com/images/smartwatch.jpg",
      "created_at": "2025-11-22T10:21:00.000000Z",
      "updated_at": "2025-11-22T10:21:00.000000Z"
    }
  ]
}
```

---

## Users

### GET `/api/users` - List All Users

```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": "2025-11-20T08:30:00.000000Z",
    "role": "customer",
    "created_at": "2025-11-20T08:00:00.000000Z",
    "updated_at": "2025-11-20T08:00:00.000000Z"
  },
  {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "email_verified_at": "2025-11-21T09:15:00.000000Z",
    "role": "customer",
    "created_at": "2025-11-21T09:00:00.000000Z",
    "updated_at": "2025-11-21T09:00:00.000000Z"
  },
  {
    "id": 3,
    "name": "Admin User",
    "email": "admin@example.com",
    "email_verified_at": "2025-11-15T10:00:00.000000Z",
    "role": "admin",
    "created_at": "2025-11-15T10:00:00.000000Z",
    "updated_at": "2025-11-15T10:00:00.000000Z"
  }
]
```

### GET `/api/users/1` - Show Single User with Orders

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john.doe@example.com",
  "email_verified_at": "2025-11-20T08:30:00.000000Z",
  "role": "customer",
  "created_at": "2025-11-20T08:00:00.000000Z",
  "updated_at": "2025-11-20T08:00:00.000000Z",
  "orders": [
    {
      "id": 1,
      "user_id": 1,
      "total_amount": "449.98",
      "status": "completed",
      "payment_method": "credit_card",
      "delivery_method": "standard_shipping",
      "delivery_address": "123 Main St, Springfield, IL 62701",
      "ordered_at": "2025-11-21T14:30:00.000000Z",
      "created_at": "2025-11-21T14:30:00.000000Z",
      "updated_at": "2025-11-22T09:15:00.000000Z"
    },
    {
      "id": 5,
      "user_id": 1,
      "total_amount": "149.99",
      "status": "pending",
      "payment_method": "paypal",
      "delivery_method": "express_shipping",
      "delivery_address": "123 Main St, Springfield, IL 62701",
      "ordered_at": "2025-11-22T16:45:00.000000Z",
      "created_at": "2025-11-22T16:45:00.000000Z",
      "updated_at": "2025-11-22T16:45:00.000000Z"
    }
  ]
}
```

---

## Products

### GET `/api/products` - List All Products with Category

```json
[
  {
    "id": 1,
    "category_id": 1,
    "name": "Wireless Headphones",
    "description": "Premium noise-cancelling wireless headphones with 30-hour battery life",
    "price": "149.99",
    "stock_quantity": 50,
    "image_url": "https://example.com/images/headphones.jpg",
    "is_available": true,
    "created_at": "2025-11-22T10:20:00.000000Z",
    "updated_at": "2025-11-22T10:20:00.000000Z",
    "category": {
      "id": 1,
      "category_name": "Electronics",
      "created_at": "2025-11-22T10:15:00.000000Z",
      "updated_at": "2025-11-22T10:15:00.000000Z"
    }
  },
  {
    "id": 2,
    "category_id": 1,
    "name": "Smart Watch",
    "description": "Fitness tracking smartwatch with heart rate monitor",
    "price": "299.99",
    "stock_quantity": 30,
    "image_url": "https://example.com/images/smartwatch.jpg",
    "is_available": true,
    "created_at": "2025-11-22T10:21:00.000000Z",
    "updated_at": "2025-11-22T10:21:00.000000Z",
    "category": {
      "id": 1,
      "category_name": "Electronics",
      "created_at": "2025-11-22T10:15:00.000000Z",
      "updated_at": "2025-11-22T10:15:00.000000Z"
    }
  },
  {
    "id": 3,
    "category_id": 2,
    "name": "Cotton T-Shirt",
    "description": "100% organic cotton t-shirt, available in multiple colors",
    "price": "24.99",
    "stock_quantity": 200,
    "image_url": "https://example.com/images/tshirt.jpg",
    "is_available": false,
    "created_at": "2025-11-22T10:22:00.000000Z",
    "updated_at": "2025-11-22T10:22:00.000000Z",
    "category": {
      "id": 2,
      "category_name": "Clothing",
      "created_at": "2025-11-22T10:16:00.000000Z",
      "updated_at": "2025-11-22T10:16:00.000000Z"
    }
  }
]
```

### GET `/api/products/1` - Show Single Product

```json
{
  "id": 1,
  "category_id": 1,
  "name": "Wireless Headphones",
  "description": "Premium noise-cancelling wireless headphones with 30-hour battery life",
  "price": "149.99",
  "stock_quantity": 50,
  "image_url": "https://example.com/images/headphones.jpg",
  "is_available": true,
  "created_at": "2025-11-22T10:20:00.000000Z",
  "updated_at": "2025-11-22T10:20:00.000000Z",
  "category": {
    "id": 1,
    "category_name": "Electronics",
    "created_at": "2025-11-22T10:15:00.000000Z",
    "updated_at": "2025-11-22T10:15:00.000000Z"
  }
}
```

---

## Orders

### GET `/api/orders` - List All Orders with Full Relationships

```json
[
  {
    "id": 1,
    "user_id": 1,
    "total_amount": "449.98",
    "status": "completed",
    "payment_method": "credit_card",
    "delivery_method": "standard_shipping",
    "delivery_address": "123 Main St, Springfield, IL 62701",
    "ordered_at": "2025-11-21T14:30:00.000000Z",
    "created_at": "2025-11-21T14:30:00.000000Z",
    "updated_at": "2025-11-22T09:15:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "email_verified_at": "2025-11-20T08:30:00.000000Z",
      "role": "customer",
      "created_at": "2025-11-20T08:00:00.000000Z",
      "updated_at": "2025-11-20T08:00:00.000000Z"
    },
    "order_items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": "149.99",
        "created_at": "2025-11-21T14:30:00.000000Z",
        "updated_at": "2025-11-21T14:30:00.000000Z",
        "product": {
          "id": 1,
          "category_id": 1,
          "name": "Wireless Headphones",
          "description": "Premium noise-cancelling wireless headphones with 30-hour battery life",
          "price": "149.99",
          "stock_quantity": 50,
          "image_url": "https://example.com/images/headphones.jpg",
          "created_at": "2025-11-22T10:20:00.000000Z",
          "updated_at": "2025-11-22T10:20:00.000000Z"
        }
      },
      {
        "id": 2,
        "order_id": 1,
        "product_id": 2,
        "quantity": 1,
        "price": "149.99",
        "created_at": "2025-11-21T14:30:00.000000Z",
        "updated_at": "2025-11-21T14:30:00.000000Z",
        "product": {
          "id": 2,
          "category_id": 1,
          "name": "Smart Watch",
          "description": "Fitness tracking smartwatch with heart rate monitor",
          "price": "299.99",
          "stock_quantity": 30,
          "image_url": "https://example.com/images/smartwatch.jpg",
          "created_at": "2025-11-22T10:21:00.000000Z",
          "updated_at": "2025-11-22T10:21:00.000000Z"
        }
      }
    ]
  },
  {
    "id": 2,
    "user_id": 2,
    "total_amount": "74.97",
    "status": "processing",
    "payment_method": "debit_card",
    "delivery_method": "standard_shipping",
    "delivery_address": "456 Oak Ave, Chicago, IL 60601",
    "ordered_at": "2025-11-22T11:00:00.000000Z",
    "created_at": "2025-11-22T11:00:00.000000Z",
    "updated_at": "2025-11-22T11:30:00.000000Z",
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane.smith@example.com",
      "email_verified_at": "2025-11-21T09:15:00.000000Z",
      "role": "customer",
      "created_at": "2025-11-21T09:00:00.000000Z",
      "updated_at": "2025-11-21T09:00:00.000000Z"
    },
    "order_items": [
      {
        "id": 3,
        "order_id": 2,
        "product_id": 3,
        "quantity": 3,
        "price": "24.99",
        "created_at": "2025-11-22T11:00:00.000000Z",
        "updated_at": "2025-11-22T11:00:00.000000Z",
        "product": {
          "id": 3,
          "category_id": 2,
          "name": "Cotton T-Shirt",
          "description": "100% organic cotton t-shirt, available in multiple colors",
          "price": "24.99",
          "stock_quantity": 200,
          "image_url": "https://example.com/images/tshirt.jpg",
          "created_at": "2025-11-22T10:22:00.000000Z",
          "updated_at": "2025-11-22T10:22:00.000000Z"
        }
      }
    ]
  }
]
```

### GET `/api/orders/1` - Show Single Order

```json
{
  "id": 1,
  "user_id": 1,
  "total_amount": "449.98",
  "status": "completed",
  "payment_method": "credit_card",
  "delivery_method": "standard_shipping",
  "delivery_address": "123 Main St, Springfield, IL 62701",
  "ordered_at": "2025-11-21T14:30:00.000000Z",
  "created_at": "2025-11-21T14:30:00.000000Z",
  "updated_at": "2025-11-22T09:15:00.000000Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": "2025-11-20T08:30:00.000000Z",
    "role": "customer",
    "created_at": "2025-11-20T08:00:00.000000Z",
    "updated_at": "2025-11-20T08:00:00.000000Z"
  },
  "order_items": [
    {
      "id": 1,
      "order_id": 1,
      "product_id": 1,
      "quantity": 2,
      "price": "149.99",
      "created_at": "2025-11-21T14:30:00.000000Z",
      "updated_at": "2025-11-21T14:30:00.000000Z",
      "product": {
        "id": 1,
        "category_id": 1,
        "name": "Wireless Headphones",
        "description": "Premium noise-cancelling wireless headphones with 30-hour battery life",
        "price": "149.99",
        "stock_quantity": 50,
        "image_url": "https://example.com/images/headphones.jpg",
        "created_at": "2025-11-22T10:20:00.000000Z",
        "updated_at": "2025-11-22T10:20:00.000000Z"
      }
    },
    {
      "id": 2,
      "order_id": 1,
      "product_id": 2,
      "quantity": 1,
      "price": "149.99",
      "created_at": "2025-11-21T14:30:00.000000Z",
      "updated_at": "2025-11-21T14:30:00.000000Z",
      "product": {
        "id": 2,
        "category_id": 1,
        "name": "Smart Watch",
        "description": "Fitness tracking smartwatch with heart rate monitor",
        "price": "299.99",
        "stock_quantity": 30,
        "image_url": "https://example.com/images/smartwatch.jpg",
        "created_at": "2025-11-22T10:21:00.000000Z",
        "updated_at": "2025-11-22T10:21:00.000000Z"
      }
    }
  ]
}
```

---

## Order Items

### GET `/api/order-items` - List All Order Items

```json
[
  {
    "id": 1,
    "order_id": 1,
    "product_id": 1,
    "quantity": 2,
    "price": "149.99",
    "created_at": "2025-11-21T14:30:00.000000Z",
    "updated_at": "2025-11-21T14:30:00.000000Z",
    "order": {
      "id": 1,
      "user_id": 1,
      "total_amount": "449.98",
      "status": "completed",
      "payment_method": "credit_card",
      "delivery_method": "standard_shipping",
      "delivery_address": "123 Main St, Springfield, IL 62701",
      "ordered_at": "2025-11-21T14:30:00.000000Z",
      "created_at": "2025-11-21T14:30:00.000000Z",
      "updated_at": "2025-11-22T09:15:00.000000Z"
    },
    "product": {
      "id": 1,
      "category_id": 1,
      "name": "Wireless Headphones",
      "description": "Premium noise-cancelling wireless headphones with 30-hour battery life",
      "price": "149.99",
      "stock_quantity": 50,
      "image_url": "https://example.com/images/headphones.jpg",
      "created_at": "2025-11-22T10:20:00.000000Z",
      "updated_at": "2025-11-22T10:20:00.000000Z"
    }
  },
  {
    "id": 2,
    "order_id": 1,
    "product_id": 2,
    "quantity": 1,
    "price": "149.99",
    "created_at": "2025-11-21T14:30:00.000000Z",
    "updated_at": "2025-11-21T14:30:00.000000Z",
    "order": {
      "id": 1,
      "user_id": 1,
      "total_amount": "449.98",
      "status": "completed",
      "payment_method": "credit_card",
      "delivery_method": "standard_shipping",
      "delivery_address": "123 Main St, Springfield, IL 62701",
      "ordered_at": "2025-11-21T14:30:00.000000Z",
      "created_at": "2025-11-21T14:30:00.000000Z",
      "updated_at": "2025-11-22T09:15:00.000000Z"
    },
    "product": {
      "id": 2,
      "category_id": 1,
      "name": "Smart Watch",
      "description": "Fitness tracking smartwatch with heart rate monitor",
      "price": "299.99",
      "stock_quantity": 30,
      "image_url": "https://example.com/images/smartwatch.jpg",
      "created_at": "2025-11-22T10:21:00.000000Z",
      "updated_at": "2025-11-22T10:21:00.000000Z"
    }
  }
]
```

### GET `/api/order-items/1` - Show Single Order Item

```json
{
  "id": 1,
  "order_id": 1,
  "product_id": 1,
  "quantity": 2,
  "price": "149.99",
  "created_at": "2025-11-21T14:30:00.000000Z",
  "updated_at": "2025-11-21T14:30:00.000000Z",
  "order": {
    "id": 1,
    "user_id": 1,
    "total_amount": "449.98",
    "status": "completed",
    "payment_method": "credit_card",
    "delivery_method": "standard_shipping",
    "delivery_address": "123 Main St, Springfield, IL 62701",
    "ordered_at": "2025-11-21T14:30:00.000000Z",
    "created_at": "2025-11-21T14:30:00.000000Z",
    "updated_at": "2025-11-22T09:15:00.000000Z"
  },
  "product": {
    "id": 1,
    "category_id": 1,
    "name": "Wireless Headphones",
    "description": "Premium noise-cancelling wireless headphones with 30-hour battery life",
    "price": "149.99",
    "stock_quantity": 50,
    "image_url": "https://example.com/images/headphones.jpg",
    "created_at": "2025-11-22T10:20:00.000000Z",
    "updated_at": "2025-11-22T10:20:00.000000Z"
  }
}
```

---

## Create/Update Examples

### POST `/api/categories` - Create Category

**Request Body:**
```json
{
  "category_name": "Sports & Outdoors"
}
```

**Response (201 Created):**
```json
{
  "id": 4,
  "category_name": "Sports & Outdoors",
  "created_at": "2025-11-22T18:40:00.000000Z",
  "updated_at": "2025-11-22T18:40:00.000000Z"
}
```

### POST `/api/products` - Create Product

**Request Body:**
```json
{
  "category_id": 1,
  "name": "Bluetooth Speaker",
  "description": "Portable waterproof Bluetooth speaker with 20-hour battery",
  "price": 79.99,
  "stock_quantity": 100,
  "image_url": "https://example.com/images/speaker.jpg",
  "is_available": true
}
```

**Response (201 Created):**
```json
{
  "id": 4,
  "category_id": 1,
  "name": "Bluetooth Speaker",
  "description": "Portable waterproof Bluetooth speaker with 20-hour battery",
  "price": "79.99",
  "stock_quantity": 100,
  "image_url": "https://example.com/images/speaker.jpg",
  "is_available": true,
  "created_at": "2025-11-22T18:41:00.000000Z",
  "updated_at": "2025-11-22T18:41:00.000000Z",
  "category": {
    "id": 1,
    "category_name": "Electronics",
    "created_at": "2025-11-22T10:15:00.000000Z",
    "updated_at": "2025-11-22T10:15:00.000000Z"
  }
}
```

### POST `/api/orders` - Create Order

**Request Body:**
```json
{
  "user_id": 1,
  "total_amount": 179.98,
  "status": "pending",
  "payment_method": "credit_card",
  "delivery_method": "express_shipping",
  "delivery_address": "123 Main St, Springfield, IL 62701",
  "ordered_at": "2025-11-22T18:41:30.000000Z"
}
```

**Response (201 Created):**
```json
{
  "id": 6,
  "user_id": 1,
  "total_amount": "179.98",
  "status": "pending",
  "payment_method": "credit_card",
  "delivery_method": "express_shipping",
  "delivery_address": "123 Main St, Springfield, IL 62701",
  "ordered_at": "2025-11-22T18:41:30.000000Z",
  "created_at": "2025-11-22T18:41:30.000000Z",
  "updated_at": "2025-11-22T18:41:30.000000Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": "2025-11-20T08:30:00.000000Z",
    "role": "customer",
    "created_at": "2025-11-20T08:00:00.000000Z",
    "updated_at": "2025-11-20T08:00:00.000000Z"
  },
  "order_items": []
}
```

### PUT `/api/orders/6` - Update Order Status

**Request Body:**
```json
{
  "status": "processing"
}
```

**Response (200 OK):**
```json
{
  "id": 6,
  "user_id": 1,
  "total_amount": "179.98",
  "status": "processing",
  "payment_method": "credit_card",
  "delivery_method": "express_shipping",
  "delivery_address": "123 Main St, Springfield, IL 62701",
  "ordered_at": "2025-11-22T18:41:30.000000Z",
  "created_at": "2025-11-22T18:41:30.000000Z",
  "updated_at": "2025-11-22T18:42:00.000000Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": "2025-11-20T08:30:00.000000Z",
    "role": "customer",
    "created_at": "2025-11-20T08:00:00.000000Z",
    "updated_at": "2025-11-20T08:00:00.000000Z"
  },
  "order_items": []
}
```

### PUT `/api/products/3` - Toggle Product Availability (Admin)

**Request Body:**
```json
{
  "is_available": true
}
```

**Response (200 OK):**
```json
{
  "id": 3,
  "category_id": 2,
  "name": "Cotton T-Shirt",
  "description": "100% organic cotton t-shirt, available in multiple colors",
  "price": "24.99",
  "stock_quantity": 200,
  "image_url": "https://example.com/images/tshirt.jpg",
  "is_available": true,
  "created_at": "2025-11-22T10:22:00.000000Z",
  "updated_at": "2025-11-22T18:53:00.000000Z",
  "category": {
    "id": 2,
    "category_name": "Clothing",
    "created_at": "2025-11-22T10:16:00.000000Z",
    "updated_at": "2025-11-22T10:16:00.000000Z"
  }
}
```

---

## Delete Examples

### DELETE `/api/products/4` - Delete Product

**Response (200 OK):**
```json
{
  "message": "Product deleted successfully"
}
```

### DELETE `/api/orders/6` - Delete Order

**Response (200 OK):**
```json
{
  "message": "Order deleted successfully"
}
```

---

## Key Notes

1. **Password Field**: The `password` field is automatically excluded from JSON responses due to the `$hidden` property in the User model.

2. **Timestamps**: All timestamps are in ISO 8601 format with timezone (UTC).

3. **Decimal Fields**: Prices are returned as strings to maintain precision (e.g., `"149.99"` not `149.99`).

4. **Eager Loading**: Related data is automatically loaded based on the controller implementation:
   - Products include their category
   - Orders include user and order items with products
   - Categories can include product count or full products list
   - Users include their orders

5. **Cascade Deletes**: When you delete a parent record, related records are automatically deleted:
   - Deleting a category removes all its products
   - Deleting a user removes all their orders
   - Deleting an order removes all its order items

6. **Order Status Values**: Valid status values are: `pending`, `processing`, `completed`, `cancelled`

7. **Role Values**: Valid user role values are: `customer`, `admin`

8. **Product Availability**: 
   - The `is_available` field controls whether a product is currently available for purchase
   - Default value is `true` (available)
   - Admins can set this to `false` to temporarily hide products from customers
   - Use the `available()` scope in queries to filter only available products: `Product::available()->get()`

