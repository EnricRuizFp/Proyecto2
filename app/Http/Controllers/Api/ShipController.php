<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\StoreShipRequest;
use App\Http\Resources\ShipResource;
use App\Models\Ship;

class ShipController extends Controller
{
    /**
     * LISTAR BARCOS
     * 
     * Devuelve un listado paginado y filtrable de todos los barcos.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados: (opcionales)
     * {
     *   "order_column": "id"|"name"|"size",
     *   "order_direction": "asc"|"desc",
     *   "search_id": int,
     *   "search_title": string,
     *   "search_global": string
     * }
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * Respuesta: Colección paginada de barcos.
     */
    public function index()
    {
        $orderColumn = request('order_column', 'id');
        if (!in_array($orderColumn, ['id', 'name', 'size'])) {
            $orderColumn = 'id';
        }
        $orderDirection = request('order_direction', 'desc');
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }
        $ships = Ship::when(request('search_id'), function ($query) {
            $query->where('id', request('search_id'));
        })
            ->when(request('search_title'), function ($query) {
                $query->where('name', 'like', '%' . request('search_title') . '%');
            })
            ->when(request('search_global'), function ($query) {
                $query->where(function ($q) {
                    $q->where('id', request('search_global'))
                        ->orWhere('name', 'like', '%' . request('search_global') . '%');
                });
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(50);

        return ShipResource::collection($ships);
    }

    /**
     * CREAR BARCO
     * 
     * Crea un nuevo barco con los datos proporcionados.
     * Se necesita el permiso de: 'ship-create'
     *
     * @param  \Illuminate\Http\Request  $request
     * Datos esperados del request:
     * {
     *   "name": string|required,
     *   "size": int|required
     * }
     * 
     * @return shipResource
     * Respuesta exitosa: Datos del barco creado.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function store(StoreShipRequest $request)
    {
        $this->authorize('ship-create');

        $ship = new Ship();
        $ship->name = $request->name;
        $ship->size = $request->size;

        if ($ship->save()) {
            return new ShipResource($ship);
        }

        return response()->json(['status' => 405, 'success' => false]);
    }

    /**
     * MOSTRAR BARCO
     * 
     * Devuelve los datos del barco especificado.
     * Se necesita el permiso de: 'ship-list'
     *
     * @param  int  $id
     * Datos esperados: ID del barco a mostrar.
     * 
     * @return ShipResource
     * Respuesta: Datos del barco especificado.
     */
    public function show(Ship $ship)
    {
        $this->authorize('ship-list');

        return new ShipResource($ship);
    }

    /**
     * ACTUALIZAR BARCO
     * 
     * Actualiza los datos del barco especificado.
     * Se necesita el permiso de: 'ship-edit'
     *
     * @param Ship $ship
     * Datos esperados: El barco a editar.
     * 
     * @param StoreShipRequest $request // Consider creating an UpdateShipRequest if validation rules differ
     * Datos esperados del request:
     * {
     *   "name": string|required,
     *   "size": int|required // Added size to expected request for clarity
     * }
     * 
     * @return ShipResource
     * Respuesta exitosa: Datos del barco.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function update(Ship $ship, StoreShipRequest $request) // Consider using a dedicated UpdateShipRequest
    {
        $this->authorize('ship-edit');

        $ship->name = $request->name;
        $ship->size = $request->size; // Add this line to update the size

        if ($ship->save()) {
            return new ShipResource($ship);
        }

        return response()->json(['status' => 405, 'success' => false]);
    }

    /**
     * ELIMINAR BARCO
     * 
     * Elimina el barco especificado.
     * Se necesita el permiso de: 'ship-delete'
     *
     * @param  int  $id
     * Datos esperados: ID del barco a eliminar.
     * 
     * @return \Illuminate\Http\Response
     * Respuesta: No devuelve nada.
     */
    public function destroy(Ship $ship)
    {
        $this->authorize('ship-delete');
        $ship->delete();

        return response()->noContent();
    }


    /*

        ///// FUNCIONES COMPLEJAS /////

    */

    /**
     * GET SHIP LIST
     * 
     * Devuelve el listado de los barcos
     * 
     * Sin parámetros de entrada.
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * Respuesta: Listado de los barcos de la DB.
     */
    public function getList()
    {
        return ShipResource::collection(Ship::all());
    }

    /**
     * GET GAME SHIPS
     * 
     * Devuelve los barcos del juego.
     * 
     * Sin parámetros de entrada.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Barcos para la partida en formato JSON.
     */
    public function getGameShips()
    {
        $ships = Ship::select('id', 'name', 'size')->get();
        $gameShips = $ships->toArray();

        return response()->json($gameShips);
    }
}
