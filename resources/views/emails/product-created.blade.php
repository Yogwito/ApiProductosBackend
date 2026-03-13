<h1>Nuevo producto creado</h1>

<p>El usuario {{ $actor->name }} creo un nuevo producto.</p>

<ul>
    <li>Nombre: {{ $producto->nombre }}</li>
    <li>Precio: {{ $producto->precio }}</li>
    <li>Stock: {{ $producto->stock }}</li>
    <li>Descripcion: {{ $producto->descripcion ?: 'Sin descripcion' }}</li>
</ul>
