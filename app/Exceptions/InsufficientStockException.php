<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InsufficientStockException extends Exception
{
    protected $availableStock;
    protected $requestedQuantity;

    public function __construct(int $availableStock, int $requestedQuantity, string $message = null)
    {
        $this->availableStock = $availableStock;
        $this->requestedQuantity = $requestedQuantity;
        
        $message = $message ?: $this->generateMessage();
        
        parent::__construct($message);
    }

    /**
     * Generate appropriate error message based on stock levels
     */
    private function generateMessage(): string
    {
        if ($this->availableStock === 0) {
            return 'This product is currently out of stock.';
        }
        
        return "Only {$this->availableStock} items available in stock. You requested {$this->requestedQuantity}.";
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_type' => 'insufficient_stock',
                'available_stock' => $this->availableStock,
                'requested_quantity' => $this->requestedQuantity
            ], 422);
        }

        return response()->view('errors.insufficient-stock', [
            'message' => $this->getMessage(),
            'availableStock' => $this->availableStock,
            'requestedQuantity' => $this->requestedQuantity
        ], 422);
    }

    public function getAvailableStock(): int
    {
        return $this->availableStock;
    }

    public function getRequestedQuantity(): int
    {
        return $this->requestedQuantity;
    }
}