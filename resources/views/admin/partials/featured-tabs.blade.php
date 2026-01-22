<div class="inner-panel space-y-6">
    <div>
        <h3 class="inner-title">Lo más vendido</h3>
        <p class="inner-text">Las pestañas de la portada se generan automáticamente a partir de las categorías que marques con “Mostrar en portada”. Usa esos formularios (platos, cócteles y café) para definir el título público y los artículos destacados.</p>
        <ul class="text-sm text-slate-600 list-disc pl-5">
            <li>Activa <strong>Mostrar en portada</strong> dentro de la categoría.</li>
            <li>Personaliza el nombre/subtítulo que verán tus invitados.</li>
            <li>En cada plato o bebida marca “Destacar en portada”. Solo aparecerán si pertenecen a una categoría activa.</li>
        </ul>
    </div>

    <div class="space-y-4">
        @forelse($featuredGroups as $group)
            <div class="border border-slate-200 rounded-2xl p-4 bg-white">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-slate-400">{{ $group['source_label'] }}</p>
                        <h4 class="text-xl font-semibold text-slate-900">{{ $group['title'] }}</h4>
                        @if(!empty($group['subtitle']))
                            <p class="text-sm text-slate-500">{{ $group['subtitle'] }}</p>
                        @endif
                    </div>
                </div>
                @if(collect($group['items'])->isNotEmpty())
                    <ul class="divide-y divide-slate-100">
                        @foreach($group['items'] as $item)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $item['title'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $item['subtitle'] }}</p>
                                </div>
                                @if(!empty($item['price']))
                                    <span class="text-sm font-semibold text-amber-600">${{ number_format($item['price'], 2) }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500">No hay artículos destacados en esta categoría. Edita los platos/bebidas y activa “Destacar en portada”.</p>
                @endif
            </div>
        @empty
            <div class="border border-dashed border-slate-300 rounded-2xl p-6 text-center bg-slate-50">
                <p class="text-sm text-slate-600">Aún no se ha activado ninguna categoría para la portada. Edita tus categorías y marca la opción correspondiente.</p>
            </div>
        @endforelse
    </div>
</div>
