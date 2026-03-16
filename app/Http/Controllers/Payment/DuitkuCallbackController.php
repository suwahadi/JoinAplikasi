<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\Duitku\DuitkuCallbackHandler;
use App\Services\Payment\Duitku\Exceptions\InvalidSignatureException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DuitkuCallbackController extends Controller
{
    public function __construct(protected DuitkuCallbackHandler $callbackHandler)
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $requestData = $request->all();
            $ok = $this->callbackHandler->handle($requestData);
            return response()->noContent($ok ? 200 : 400);
        } catch (InvalidSignatureException $e) {
            report($e);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 'error', 'message' => 'Callback processing failed'], 500);
        }
    }
}
