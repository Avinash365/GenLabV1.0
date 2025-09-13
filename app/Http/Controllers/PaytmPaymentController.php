<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Anand\LaravelPaytmWallet\Facades\PaytmWallet;

class PaytmPaymentController extends Controller
{
    public function initiatePayment() {
        return view('paytm.payment');
    }
    
    public function pay(Request $request) {
        $payment = PaytmWallet::with('receive');
        $payment->prepare([
            'order' => 'ORDER_' . uniqid(),
            'user' => auth()->id(),
            'mobile_number' => '7777777777',
            'email' => 'test@example.com',
            'amount' => $request->amount,
            'callback_url' => route('paytm.callback')
        ]);
        return $payment->receive();
    }

    public function callback() {
        $transaction = PaytmWallet::with('receive');
        $response = $transaction->response();
        if ($transaction->isSuccessful()) {
            return redirect()->route('payment.success')->with('message', 'Payment Successful');
        } elseif ($transaction->isFailed()) {
            return redirect()->route('payment.failed')->with('message', 'Payment Failed');
        }
    }

    public function success() {
        return view('payment-success');
    }
    public function failed() {
        return view('payment-failed');
    }
}
