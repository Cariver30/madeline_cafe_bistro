<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cantina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 0;
            background-color: #ffffff;
            color: #333;
        }
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('/images/takmenuback.png') no-repeat center center fixed;
            background-size: cover;
            overflow-x: hidden;
        }
        #logo {
            display: block;
            max-width: 300px;
            max-height: 200px;
            width: 100%;
            height: auto;
            margin: 20px auto;
            position: relative;
            z-index: 10;
        }
        .container {
            padding: 5 15px;
        }
        #toggleMenu {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1050;
            cursor: pointer;
            background-color: black;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: none;
            font-size: 24px;
        }
        #categoryMenu {
            position: fixed;
            left: -300px;
            top: 0;
            width: 300px;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            transition: left 0.3s;
            overflow-y: auto;
            padding-top: 70px;
            z-index: 2000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.5);
        }
        #categoryMenu.active {
            left: 0;
        }
        .category-name-container {
            background-color: rgba(254, 90, 90, 0.8); /* Color #FE5A5A con mayor transparencia */
            padding: 10px 20px;
            border-radius: 10px;
            margin: 20px auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .category-name {
            font-size: 30px;
            font-weight: bold;
            color: #f9f9f9;
            text-align: center;
        }
        .dish-card {
            position: relative;
            z-index: 10;
            background-color: #191919;
            opacity: 0.9;
            color: white;
            margin: 30px auto;
            padding: 10px;
            width: 90%;
            display: flex;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .dish-card:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.5);
            z-index: 20;
        }
        .dish-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
        }
        .card-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: start;
        }
        .price {
            margin-bottom: 5px;
        }
        .hidden-details {
            display: none;
            text-align: justify;
            padding-top: 10px;
        }
        .description {
            margin-top: 5px;
        }
        .modal-content {
            background-color: white;
            color: #333;
        }
        @keyframes blinker {
            50% {
                opacity: 0.5;
            }
        }
        .btn-custom:hover {
            background-color: #F44336;
        }
        .btn-home {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
            background-color: #FF5722;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-home i {
            font-size: 24px;
        }
        .btn-home:hover {
            background-color: #E64A19;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="text-center mb-4">
        <img id="logo" src="https://takbronpr.com/images/takalogo.png">
        <button id="toggleMenu"><i class="fas fa-beer"></i></button>
        <div id="categoryMenu">
            @foreach ($cantinaCategories as $category)
                <a href="#category{{ $category->id }}" class="btn btn-light w-100 mt-2">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>

    @foreach ($cantinaCategories as $category)
        <div id="category{{ $category->id }}" class="category-section">
            <div class="category-name-container">
                <h2 class="category-name">{{ $category->name }}</h2>
            </div>
            <div class="d-flex flex-wrap justify-content-center">
                @foreach ($category->items as $item)
                    <div class="dish-card" onclick="showDetailsModal('{{ addslashes($item->name) }}', '{{ addslashes($item->description) }}', '{{ number_format($item->price, 2) }}', '{{ asset('storage/' . $item->image) }}')">
                        <img src="{{ asset('storage/' . $item->image) }}" class="dish-img" alt="{{ $item->name }}">
                        <div class="card-content">
                            <h5 class="card-title">{{ $item->name }}</h5>
                            <p class="card-text price">${{ $item->price }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<button class="btn-home" onclick="window.location.href='/'"><i class="fas fa-home"></i></button>

<!-- Modal para Detalles del Plato -->
<div class="modal fade" id="dishDetailsModal" tabindex="-1" aria-labelledby="dishModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dishModalLabel">Detalles del Plato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Dish Image" class="img-fluid mb-2">
                <h3 id="modalTitle"></h3>
                <p id="modalDescription"></p>
                <p id="modalPrice"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleMenuButton = document.getElementById('toggleMenu');
    toggleMenuButton.addEventListener('click', function() {
        const menu = document.getElementById('categoryMenu');
        menu.style.left = menu.style.left === '0px' ? '-300px' : '0px';
    });

    const categoryLinks = document.querySelectorAll('#categoryMenu a');
    categoryLinks.forEach(link => {
        link.addEventListener('click', function() {
            const menu = document.getElementById('categoryMenu');
            menu.style.left = '-300px';

            const categoryID = this.getAttribute('href');
            const categorySection = document.querySelector(categoryID);
            if (categorySection) {
                categorySection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    const cards = document.querySelectorAll('.dish-card');
    cards.forEach(card => {
        card.addEventListener('click', function(event) {
            event.stopPropagation();
            toggleDetails(card);
        });
    });
});

function showDetailsModal(name, description, price, imageSrc) {
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const modalPrice = document.getElementById('modalPrice');
    const modalImage = document.getElementById('modalImage');

    modalTitle.textContent = name;
    modalDescription.textContent = description;
    modalPrice.textContent = `$${price}`;
    modalImage.src = imageSrc;
    modalImage.alt = name;

    var myModal = new bootstrap.Modal(document.getElementById('dishDetailsModal'));
    myModal.show();
}
</script>
</body>
</html>
