# API Endpoints

Base URL: `http://localhost:8000/api`

## Auth

### POST `/login`
- Auth requerida: no
- Roles permitidos: publico
- Headers:
  - `Accept: application/json`
  - `Content-Type: application/json`
- Body ejemplo:

```json
{
  "email": "admin@api.com",
  "password": "password"
}
```

- Respuesta exitosa:

```json
{
  "message": "Login Correcto",
  "token": "jwt_token",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@api.com",
    "role": "admin"
  }
}
```

- Respuesta de error:

```json
{
  "message": "Credenciales invalidas"
}
```

### GET `/me`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
- Headers:
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo: no aplica
- Respuesta exitosa:

```json
{
  "id": 1,
  "name": "Administrador",
  "email": "admin@api.com",
  "role": "admin"
}
```

- Respuesta de error:

```json
{
  "message": "Unauthenticated."
}
```

### POST `/logout`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
- Headers:
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo: no aplica
- Respuesta exitosa:

```json
{
  "message": "Logout correcto"
}
```

- Respuesta de error:

```json
{
  "message": "No se pudo cerrar la sesion"
}
```

### POST `/refresh`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
- Headers:
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo: no aplica
- Respuesta exitosa:

```json
{
  "message": "Token renovado correctamente",
  "token": "nuevo_jwt_token",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@api.com",
    "role": "admin"
  }
}
```

- Respuesta de error:

```json
{
  "message": "No se pudo renovar el token"
}
```

## Productos

### GET `/productos`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
- Headers:
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo: no aplica
- Respuesta exitosa:

```json
{
  "message": "Listado de todos los productos",
  "data": [
    {
      "id": 1,
      "nombre": "Cafe",
      "precio": "12.50",
      "stock": 10,
      "descripcion": "Cafe premium"
    }
  ]
}
```

- Respuesta de error:

```json
{
  "message": "Unauthenticated."
}
```

### GET `/productos/{id}`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
- Headers:
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo: no aplica
- Respuesta exitosa:

```json
{
  "message": "Producto encontrado con id (1)",
  "data": {
    "id": 1,
    "nombre": "Cafe",
    "precio": "12.50",
    "stock": 10,
    "descripcion": "Cafe premium"
  }
}
```

- Respuesta de error:

```json
{
  "message": "No se encontro el producto solicitado con id (999)"
}
```

### POST `/productos`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`, `operador`
- Headers:
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo:

```json
{
  "nombre": "Arroz",
  "precio": 5.75,
  "stock": 40,
  "descripcion": "Arroz blanco x 1kg"
}
```

- Respuesta exitosa:

```json
{
  "message": "Producto creado correctamente",
  "data": {
    "id": 1,
    "nombre": "Arroz",
    "precio": "5.75",
    "stock": 40,
    "descripcion": "Arroz blanco x 1kg"
  }
}
```

- Respuesta de error:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "nombre": ["The nombre field is required."],
    "precio": ["The precio must be at least 0."],
    "stock": ["The stock field is required."]
  }
}
```

### PUT `/productos/{id}`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`, `operador`
- Headers:
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo:

```json
{
  "precio": 6.10,
  "stock": 35,
  "descripcion": "Arroz actualizado"
}
```

- Respuesta exitosa:

```json
{
  "message": "Producto con id (1) actualizado correctamente",
  "data": {
    "id": 1,
    "nombre": "Arroz",
    "precio": "6.10",
    "stock": 35,
    "descripcion": "Arroz actualizado"
  }
}
```

- Respuesta de error:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "precio": ["The precio must be at least 0."]
  }
}
```

### DELETE `/productos/{id}`
- Auth requerida: si, `Bearer token`
- Roles permitidos: `admin`
- Headers:
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- Body ejemplo: no aplica
- Respuesta exitosa:

```json
{
  "message": "Producto con id (1) ha sido eliminado"
}
```

- Respuesta de error:

```json
{
  "message": "No tienes permiso para esta accion"
}
```
