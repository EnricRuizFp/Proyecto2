<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    /**
     * Muestra una lista paginada de avatares.
     */
    public function index(Request $request)
    {
        // Obtiene 10 avatares por página. Ajusta según tus necesidades.
        $avatars = Avatar::paginate(10);

        // Retorna la respuesta en formato JSON. Laravel incluirá el atributo "image_route" 
        // gracias al accessor y a la propiedad $appends en el modelo.
        return response()->json($avatars);
    }

    // Puedes definir los demás métodos (store, show, update, destroy) si los necesitas.
}
