# Shopping Cart System

## Overview

The shopping cart system allows authenticated users to add products to their cart, manage quantities, and proceed to checkout. The cart is persisted in the database and maintains state across sessions.

## Components

### Models

- **CartItem**: Represents an item in a user's cart with quantity
- **User**: Has many cart items relationship
- **Product**: Can be added to cart items

### Services

- **CartService**: Handles all cart business logic including:
  - Adding products to cart
  - Updating quantities
  - Removing items
  - Calculating totals
  - Stock validation

### Controllers

- **CartController**: Handles HTTP requests for cart operations:
  - `GET /cart` - Display cart page
  - `POST /cart/{product}` - Add product to cart
  - `PUT /cart/{cartItem}` - Update cart item quantity
  - `DELETE /cart/{cartItem}` - Remove cart item
  - `DELETE /cart` - Clear entire cart
  - `GET /cart/data` - Get cart data via AJAX

### Middleware

- **CartMiddleware**: Injects cart count into all views for navigation display

## Features

### Stock Validation
- Prevents adding more items than available in inventory
- Shows stock errors on cart page
- Validates stock before checkout

### AJAX Support
- Add to cart without page refresh
- Update quantities dynamically
- Real-time cart count updates
- Toast notifications for user feedback

### User Experience
- Persistent cart across sessions
- Visual stock indicators
- Responsive design
- Loading states for actions

## Usage

### Adding to Cart
```javascript
// From product pages
addToCart(productId, quantity)

// From product cards
addToCart(productId) // defaults to quantity 1
```

### Cart Management
- Users can view their cart at `/cart`
- Update quantities using +/- buttons or direct input
- Remove individual items or clear entire cart
- Stock validation prevents over-ordering

### Integration Points
- Product pages have "Add to Cart" buttons
- Navigation shows cart count badge
- Cart page shows detailed item list with totals
- Checkout process validates cart contents

## Database Schema

### cart_items table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `product_id` - Foreign key to products table
- `quantity` - Number of items
- `created_at` / `updated_at` - Timestamps
- Unique constraint on (user_id, product_id)

## Security

- Authentication required for all cart operations
- Users can only modify their own cart items
- CSRF protection on all forms
- Stock validation prevents overselling

## Testing

- Unit tests for CartService business logic
- Feature tests for CartController HTTP endpoints
- Stock validation scenarios
- User authorization tests