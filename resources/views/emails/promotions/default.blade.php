@php
    $attachments = collect($promotion->attachments ?? []);
    $primaryImageUrl = null;
    if ($promotion->hero_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($promotion->hero_image)) {
        $mime = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($promotion->hero_image) ?: 'image/jpeg';
        $data = base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($promotion->hero_image));
        $primaryImageUrl = "data:{$mime};base64,{$data}";
    }
    $bodyHtml = $promotion->body_html ?? '';
    $bodyHtml = preg_replace('/<img[^>]*>/i', '', $bodyHtml);
    $bodyHtml = preg_replace('/<figure[^>]*>.*?<\\/figure>/is', '', $bodyHtml);
    $assetLinks = $attachments->map(fn ($asset) => [
        'name' => $asset['name'] ?? 'Archivo',
        'mime' => $asset['mime'] ?? '',
        'url' => isset($asset['path'])
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($asset['path'])
            : null,
    ])->filter(fn ($asset) => !empty($asset['url']));
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $promotion->subject }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #111827;
        }
        .container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .content {
            padding: 32px;
            line-height: 1.6;
        }
        .cta {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 28px;
            background: linear-gradient(120deg,#fbbf24,#f97316);
            color: #111827;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            {!! $bodyHtml !!}

            @if($primaryImageUrl)
                <div style="margin-top: 20px; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden;">
                    <img src="{{ $primaryImageUrl }}" alt="Promoción" style="width: 100%; display: block;">
                </div>
            @endif

            @if($assetLinks->isNotEmpty())
                <div style="margin-top: 24px;">
                    <h3 style="margin:0 0 12px; font-size: 16px;">Archivos y recursos</h3>
                    <div style="display: grid; gap: 12px;">
                        @foreach($assetLinks as $asset)
                            <a href="{{ $asset['url'] }}" style="display: inline-block; padding: 10px 16px; border-radius: 999px; background: #111827; color: #fff; text-decoration: none;">
                                {{ $asset['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
