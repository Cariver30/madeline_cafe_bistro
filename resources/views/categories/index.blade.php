@foreach ($categories as $category)
    <h2>{{ $category->name }}</h2>
    <p>{{ $category->description }}</p>
@endforeach