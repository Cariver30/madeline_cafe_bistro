<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudPrntController extends Controller
{
    public function poll(Request $request, Printer $printer)
    {
        if (!$printer->is_active) {
            return response()->json([
                'jobReady' => false,
                'message' => 'Printer disabled.',
            ], Response::HTTP_FORBIDDEN);
        }

        $printer->update([
            'last_seen_at' => now(),
            'device_id' => $request->get('device_id', $printer->device_id),
        ]);

        $job = PrintJob::where('printer_id', $printer->id)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->first();

        if (!$job) {
            return response()->json([
                'jobReady' => false,
            ]);
        }

        $job->update(['status' => 'printing']);

        return response()->json([
            'jobReady' => true,
            'jobId' => $job->id,
            'jobName' => "Order {$job->order_id}",
            'jobUrl' => route('cloudprnt.job', [
                'printer' => $printer->token,
                'job' => $job->id,
            ]),
            'contentType' => $job->content_type,
        ]);
    }

    public function job(Request $request, Printer $printer, PrintJob $job)
    {
        abort_unless($job->printer_id === $printer->id, Response::HTTP_NOT_FOUND);

        return response($job->payload, Response::HTTP_OK)
            ->header('Content-Type', $job->content_type)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function ack(Request $request, Printer $printer, PrintJob $job)
    {
        abort_unless($job->printer_id === $printer->id, Response::HTTP_NOT_FOUND);

        $status = $request->get('status', 'printed');
        $job->update([
            'status' => $status,
            'printed_at' => $status === 'printed' ? now() : null,
        ]);

        return response()->json([
            'message' => 'OK',
        ]);
    }
}
