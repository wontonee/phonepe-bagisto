<?php

namespace Wontonee\Phonepe\Exceptions;

use Exception;

class InvalidLicenseException extends Exception
{
    /**
     * Error code
     *
     * @var string
     */
    protected $errorCode;

    /**
     * Create a new exception instance
     *
     * @param string $message
     * @param string $errorCode
     */
    public function __construct(string $message = 'Invalid or missing PhonePe license key', string $errorCode = 'INVALID_LICENSE')
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }

    /**
     * Get error code
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Render the exception
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => $this->errorCode,
            ], 403);
        }

        return redirect()->back()->with('error', $this->getMessage());
    }
}
