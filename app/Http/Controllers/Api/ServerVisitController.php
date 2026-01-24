<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyCustomer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyVisit;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ServerVisitController extends Controller
{
    public function summary(Request $request)
    {
        $server = $request->user();
        $settings = Setting::first();
        $points = optional($settings)->loyalty_points_per_visit ?? 0;
        $terms = optional($settings)->loyalty_terms;

        $rewards = LoyaltyReward::where('active', true)
            ->orderBy('points_required')
            ->get()
            ->map(fn (LoyaltyReward $reward) => [
                'id' => $reward->id,
                'title' => $reward->title,
                'description' => $reward->description,
                'points_required' => $reward->points_required,
            ]);

        $activeVisit = LoyaltyVisit::where('server_id', $server->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $recentVisits = LoyaltyVisit::where('server_id', $server->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (LoyaltyVisit $visit) => [
                'id' => $visit->id,
                'name' => $visit->expected_name,
                'email' => $visit->expected_email,
                'phone' => $visit->expected_phone,
                'status' => $visit->status,
                'points' => $visit->points_awarded,
                'created_at' => $visit->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'points_per_visit' => $points,
            'terms' => $terms,
            'rewards' => $rewards,
            'qr_url' => $activeVisit ? route('loyalty.visit.show', $activeVisit->qr_token) : null,
            'active_visit' => $activeVisit ? [
                'id' => $activeVisit->id,
                'name' => $activeVisit->expected_name,
                'email' => $activeVisit->expected_email,
                'phone' => $activeVisit->expected_phone,
                'status' => $activeVisit->status,
                'points' => $activeVisit->points_awarded,
                'created_at' => $activeVisit->created_at?->toIso8601String(),
            ] : null,
            'recent_visits' => $recentVisits,
        ]);
    }

    public function store(Request $request)
    {
        $server = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El formato del correo no es válido.',
            'phone.required' => 'El teléfono es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos enviados.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $settings = Setting::first();
        $points = optional($settings)->loyalty_points_per_visit ?? 0;

        $visit = LoyaltyVisit::create([
            'server_id' => $server->id,
            'expected_name' => $validator->validated()['name'],
            'expected_email' => strtolower(trim($validator->validated()['email'])),
            'expected_phone' => $validator->validated()['phone'],
            'points_awarded' => $points,
            'qr_token' => Str::uuid()->toString(),
        ]);

        return response()->json([
            'message' => 'Visita creada correctamente.',
            'qr_url' => route('loyalty.visit.show', $visit->qr_token),
            'visit' => [
                'id' => $visit->id,
                'name' => $visit->expected_name,
                'email' => $visit->expected_email,
                'phone' => $visit->expected_phone,
                'status' => $visit->status,
                'points' => $visit->points_awarded,
            ],
        ], Response::HTTP_CREATED);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));
        $customer = LoyaltyCustomer::where('email', $email)->first();

        if (! $customer) {
            return response()->json([
                'found' => false,
                'message' => 'No encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'found' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'points' => $customer->points,
            ],
        ]);
    }
}
