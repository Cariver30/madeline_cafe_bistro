<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Orders\PosReceiptBuilder;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function pos(Request $request, Order $order)
    {
        $stage = $request->query('stage') === 'pre' ? 'pre' : 'paid';
        $receipt = PosReceiptBuilder::build($order, $stage);

        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = app('dompdf.wrapper')
                ->loadView('receipts.pos-receipt', [
                    'receipt' => $receipt,
                ])
                ->setPaper('letter');

            return $pdf->download(sprintf('recibo-%s.pdf', $order->id));
        }

        return view('receipts.pos-receipt', [
            'receipt' => $receipt,
        ]);
    }
}
