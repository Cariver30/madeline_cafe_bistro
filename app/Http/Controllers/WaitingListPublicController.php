<?php

namespace App\Http\Controllers;

use App\Events\HostDashboardUpdated;
use App\Models\Setting;
use App\Models\WaitingListEntry;
use App\Models\WaitingListSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WaitingListPublicController extends Controller
{
    public function show()
    {
        $settings = Setting::first();
        $waitSettings = WaitingListSetting::current();

        return view('waiting-list', [
            'settings' => $settings,
            'waitSettings' => $waitSettings,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:30'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['guest_phone'] = $this->normalizePhone($data['guest_phone']);
        $data['quoted_minutes'] = $data['quoted_minutes'] ?? null;
        $data['quoted_at'] = null;
        $data['cancel_token'] = Str::uuid()->toString();

        $entry = WaitingListEntry::create($data);

        event(new HostDashboardUpdated('waiting_list', $entry->id));

        return redirect()
            ->back()
            ->with('success', '¡Listo! Te agregamos a la lista de espera.');
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');
        if (! $digits) {
            return $phone;
        }

        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }

        return '+' . $digits;
    }
}
