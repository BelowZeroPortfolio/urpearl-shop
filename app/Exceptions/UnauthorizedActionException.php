<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnauthorizedActionException extends Exception
{
    protected $action;
    protected $resource;

    public function __construct(string $action, string $resource = null, string $message = null)
    {
        $this->action = $action;
        $this->resource = $resource;
        
        $message = $message ?: $this->generateMessage();
        
        parent::__construct($message);
    }

    /**
     * Generate appropriate error message
     */
    private function generateMessage(): string
    {
        if ($this->resource) {
            return "You are not authorized to {$this->action} this {$this->resource}.";
        }
        
        return "You are not authorized to perform this action.";
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
                'error_type' => 'unauthorized_action',
                'action' => $this->action,
                'resource' => $this->resource
            ], 403);
        }

        return response()->view('errors.unauthorized', [
            'message' => $this->getMessage(),
            'action' => $this->action,
            'resource' => $this->resource
        ], 403);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }
}