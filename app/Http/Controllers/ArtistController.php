<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetArtistRequest;
use App\Http\Requests\StoreArtistRequest;
use App\Http\Requests\UpdateArtistRequest;
use App\Http\Resources\ArtistResource;
use App\Models\Artist;

class ArtistController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create-artist', ['only' => ['store']]);
        $this->middleware('permission:update-artist', ['only' => ['update']]);
        $this->middleware('permission:view-artist', ['only' => ['show']]);
        $this->middleware('permission:show-all-artist', ['only' => ['index']]);
        $this->middleware('permission:delete-artist', ['only' => ['delete']]);
    }

    public function find($artistId)
    {
        $artist = Artist::where('id', $artistId)->first();
        if (! $artist) {
            return abort(404, 'Artist not found');
        }

        return $artist;
    }

    public function store(StoreArtistRequest $request) // admin
    {
        $artist = Artist::create($request->validated());

        return response()->json(['message' => 'Artist created successfully'], 201);
    }

    public function index(GetArtistRequest $request) // both
    {
        $artist = Artist::search($request->search)->paginate(10);

        return response()->json(['data' => ArtistResource::collection($artist)], 200);
    }

    public function update(UpdateArtistRequest $reques, $artistId) // admin
    {
        $this->find($artistId)->update($reques->validated());

        return response()->json(['message' => 'Artist updated successfully '], 200);
    }

    public function show($artistId) // both
    {
        $artist = Artist::with('songs')->find($artistId);
        if (! $artist) {
            return abort(404, 'Artist not found');
        }

        return response()->json(new ArtistResource($artist), 200);

    }

    public function delete($artistId) // admin
    {
        $this->find($artistId)->delete();

        return response()->json(['message' => 'Artist deleted successfully'], 200);
    }
}
