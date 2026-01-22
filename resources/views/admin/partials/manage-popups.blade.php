<section class="mt-5">
    <h3>Pop-ups</h3>
    <button class="btn btn-primary mb-3" onclick="toggleVisibility('popups-list')">Gestionar Pop-ups</button>
    <div id="popups-list" class="hidden">
        <input type="text" id="searchPopupsInput" onkeyup="filterPopups()" placeholder="Buscar pop-ups..." class="form-control mb-3">
        <a href="{{ route('admin.popups.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear nuevo Pop-up</a>
        <div id="popupsList">
            @foreach($popups as $popup)
                <div class="card mb-3 popup-item" data-name="{{ $popup->title }}" data-view="{{ $popup->view }}">
                    <div class="card-body">
                        <h3 class="card-title">{{ $popup->title }}</h3>
                        <img src="{{ asset('storage/' . $popup->image) }}" alt="{{ $popup->title }}" class="img-fluid">
                        <p class="card-text">{{ $popup->view }}</p>
                        <p class="card-text">{{ $popup->start_date }} - {{ $popup->end_date }}</p>
                        <a href="{{ route('admin.popups.edit', $popup) }}" class="btn btn-outline-primary">Editar</a>
                        <form id="delete-popup-{{ $popup->id }}" method="POST" action="{{ route('admin.popups.destroy', $popup) }}" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Eliminar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.popups.toggleVisibility', $popup) }}" style="display:inline-block;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $popup->active ? 'btn-success' : 'btn-warning' }}">
                                {{ $popup->active ? 'Activo' : 'Inactivo' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
    function filterPopups() {
        let input = document.getElementById('searchPopupsInput');
        let filter = input.value.toUpperCase();
        let popupsList = document.getElementById('popupsList');
        let popupItems = popupsList.getElementsByClassName('popup-item');

        for (let i = 0; i < popupItems.length; i++) {
            let name = popupItems[i].getAttribute('data-name');
            if (name.toUpperCase().indexOf(filter) > -1) {
                popupItems[i].style.display = "";
            } else {
                popupItems[i].style.display = "none";
            }
        }
    }
</script>
