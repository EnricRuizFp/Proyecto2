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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return shipResource
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ShipResource
     */
    public function show(Ship $ship)
    {
        $this->authorize('ship-edit');

        return new ShipResource($ship);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Ship $ship
     * @param StoreShipRequest $request
     * @return ShipResource
     * @throws AuthorizationException
     */
    public function update(Ship $ship, StoreShipRequest $request)
    {
        $this->authorize('ship-edit');

        $ship->name = $request->name;

        if ($ship->save()) {
            return new ShipResource($ship);
        }

        return response()->json(['status' => 405, 'success' => false]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ship $ship)
    {
        $this->authorize('ship-delete');
        $ship->delete();

        return response()->noContent();
    }

    public function getList()
    {
        return ShipResource::collection(Ship::all());
    }

    /**
     * Get a simple list of all ships for game placement
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGameShips()
    {
        $ships = Ship::select('id', 'name', 'size')->get();
        $gameShips = $ships->toArray();

        // // Buscar y duplicar el crucero
        // foreach ($ships as $ship) {
        //     if ($ship->name === 'Crucero') {
        //         $gameShips[] = [
        //             'id' => $ship->id . '_2', // Añadir sufijo para ID único
        //             'name' => $ship->name,
        //             'size' => $ship->size
        //         ];
        //         break;
        //     }
        // }

        return response()->json($gameShips);
    }
}
