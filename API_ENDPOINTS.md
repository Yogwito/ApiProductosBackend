# API Endpoints

Base URL: `http://localhost:8000/api`

## Configuracion previa de correo

Variables relevantes en `.env`:

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `PRODUCT_NOTIFICATION_TO` opcional. Si esta vacia, el correo de nuevo producto se envia al usuario autenticado que lo crea.
- `JWT_TTL=5`
- `AUTH_VERIFICATION_CODE_TTL=10`

## Auth

### POST `/login`
- Autenticacion requerida: no
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
  "message": "Codigo de verificacion enviado al correo"
}
```

- Respuesta de error:

```json
{
  "message": "Credenciales invalidas"
}
```

### POST `/verify-code`
- Autenticacion requerida: no
- Body ejemplo:

```json
{
  "email": "admin@api.com",
  "code": "123456"
}
```

- Respuesta exitosa:

```json
{
  "message": "Codigo verificado correctamente",
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
  "message": "Codigo de verificacion invalido o expirado"
}
```

### GET `/me`
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
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
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
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

### POST `/refresh-token`
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
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

### POST `/refresh`
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
- Nota: se mantiene por compatibilidad, pero la ruta prioritaria es `/refresh-token`.

## Productos

### GET `/productos`
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
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

### POST `/productos`
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `operador`
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
  "message": "No se pudo crear el producto o enviar la notificacion"
}
```

### GET `/productos/{id}`
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `usuario`, `operador`
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

### PUT `/productos/{id}`
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`, `operador`
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
- Autenticacion requerida: si, `Bearer token`
- Roles permitidos: `admin`
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
