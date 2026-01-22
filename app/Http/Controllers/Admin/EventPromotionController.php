<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventNotification;
use App\Models\EventPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventPromotionController extends Controller
{
    public function index()
    {
        $promotions = EventPromotion::latest()->paginate(12);
        return view('admin.events.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin.events.promotions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'body_html' => 'required|string',
            'hero_image' => 'nullable|image|max:4096',
            'assets.*' => 'nullable|file|max:8192',
            'send_now' => 'boolean',
        ]);

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('promotions', 'public');
        }

        $attachments = [];
        if ($request->hasFile('assets')) {
            foreach ($request->file('assets') as $file) {
                $path = $file->store('promotions', 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        $bodyHtml = $this->normalizeBodyHtml($data['body_html']);

        $promotion = EventPromotion::create([
            'title' => $data['title'],
            'subject' => $data['subject'],
            'preview_text' => $data['preview_text'] ?? null,
            'body_html' => $bodyHtml,
            'hero_image' => $data['hero_image'] ?? null,
            'attachments' => $attachments,
            'status' => $request->boolean('send_now') ? 'sending' : 'draft',
        ]);

        if ($request->boolean('send_now')) {
            $this->dispatchSend($promotion);
        }

        return redirect()
            ->route('admin.events.promotions.index')
            ->with('success', $request->boolean('send_now') ? 'Campaña en proceso de envío.' : 'Campaña guardada como borrador.');
    }

    protected function dispatchSend(EventPromotion $promotion): void
    {
        $apiKey = config('services.sendgrid.key');
        $fromEmail = config('services.sendgrid.from_email');
        $fromName = config('services.sendgrid.from_name', config('app.name'));

        if (!$apiKey || !$fromEmail) {
            $promotion->update([
                'status' => 'failed',
                'send_error' => 'Falta SendGrid API key o remitente.',
            ]);
            return;
        }

        $notifications = EventNotification::select('name', 'email')->get();
        if ($notifications->isEmpty()) {
            $promotion->update([
                'status' => 'failed',
                'send_error' => 'No hay contactos registrados.',
            ]);
            return;
        }

        $attachments = [];
        foreach ($promotion->attachments ?? [] as $file) {
            $contents = Storage::disk('public')->get($file['path']);
            $attachments[] = [
                'content' => base64_encode($contents),
                'type' => $file['mime'],
                'filename' => $file['name'],
            ];
        }

        $body = view('emails.promotions.default', ['promotion' => $promotion])->render();

        $sent = 0;
        $errors = [];
        $chunks = $notifications->chunk(900);

        foreach ($chunks as $chunk) {
            $personalizations = $chunk->map(function ($contact) {
                return [
                    'to' => [
                        [
                            'email' => $contact->email,
                            'name' => $contact->name,
                        ],
                    ],
                ];
            })->values()->all();

            $payload = [
                'personalizations' => $personalizations,
                'from' => [
                    'email' => $fromEmail,
                    'name' => $fromName,
                ],
                'subject' => $promotion->subject,
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $body,
                    ],
                ],
            ];

            if ($promotion->preview_text) {
                $payload['custom_args'] = ['preview_text' => $promotion->preview_text];
            }

            if ($attachments) {
                $payload['attachments'] = $attachments;
            }

            try {
                Http::withToken($apiKey)
                    ->acceptJson()
                    ->post('https://api.sendgrid.com/v3/mail/send', $payload)
                    ->throw();
                $sent += $chunk->count();
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
                Log::error('SendGrid promo send failed', ['error' => $e->getMessage()]);
            }
        }

        $promotion->update([
            'status' => empty($errors) ? 'sent' : 'failed',
            'sent_at' => now(),
            'send_count' => $sent,
            'send_error' => empty($errors) ? null : implode('; ', $errors),
        ]);
    }

    protected function normalizeBodyHtml(string $content): string
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            return '<p></p>';
        }

        if (preg_match('/<[^>]+>/', $trimmed)) {
            return $trimmed;
        }

        $lines = preg_split("/\r\n|\r|\n/", $trimmed);
        $paragraphs = [];
        $buffer = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                if (!empty($buffer)) {
                    $paragraphs[] = implode(' ', $buffer);
                    $buffer = [];
                }
            } else {
                $buffer[] = trim($line);
            }
        }

        if (!empty($buffer)) {
            $paragraphs[] = implode(' ', $buffer);
        }

        if (empty($paragraphs)) {
            $paragraphs[] = $trimmed;
        }

        return collect($paragraphs)
            ->map(fn ($paragraph) => '<p>'.nl2br(e($paragraph)).'</p>')
            ->implode("\n");
    }
}
