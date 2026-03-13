<?php

namespace App\Http\Controllers;

use App\Mail\ProductCreatedMail;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProductoBaseController extends BaseController
{
    public function index()
    {
        $productos = Producto::all();

        return response()->json([
            'message' => 'Listado de todos los productos',
            'data' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $producto = Producto::create($validated);
            $recipient = config('mail.product_notification_to') ?: $request->user()->email;

            Mail::to($recipient)->send(new ProductCreatedMail($producto, $request->user()));

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return response()->json([
                'message' => 'No se pudo crear el producto o enviar la notificacion',
            ], 500);
        }

        return response()->json([
            'message' => 'Producto creado correctamente',
            'data' => $producto,
        ], 201);
    }

    public function show(string $id)
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return response()->json([
                'message' => "No se encontro el producto solicitado con id ($id)",
            ], 404);
        }

        return response()->json([
            'message' => "Producto encontrado con id ($id)",
            'data' => $producto,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return response()->json([
                'message' => "No se encontro el producto solicitado con id ($id)",
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'precio' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'descripcion' => 'nullable|string',
        ]);

        $producto->update($validated);

        return response()->json([
            'message' => "Producto con id ($id) actualizado correctamente",
            'data' => $producto,
        ]);
    }

    public function destroy(string $id)
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return response()->json([
                'message' => "No se encontro el producto solicitado con id ($id)",
            ], 404);
        }

        $producto->delete();

        return response()->json([
            'message' => "Producto con id ($id) ha sido eliminado",
        ]);
    }
}
