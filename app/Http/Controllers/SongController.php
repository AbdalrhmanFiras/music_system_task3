<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetSongRequest;
use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SongController extends Controller
{
    public function find($songId)
    {
        $song = Song::find($songId);
        if (! $song) {
            return abort(404, 'Song not found');
        }

        return $song;
    }

    protected function handleSong($request, ?Song $song = null)
    {
        $data = $request->validated();
        $cate = $data['categories'] ?? null;
        $artistId = $data['artist_id'] ?? null;
        unset($data['categories'], $data['artist_id']);
        DB::beginTransaction();
        try {
            if ($request->hasFile('cover')) {
                if ($song && $song->cover) {
                    Storage::disk('public')->delete($song->cover);
                }
                $data['cover'] = $request->file('cover')->store('covers', 'public');
            }
            if ($request->hasFile('file_path')) {

                if ($song && $song->file_path) {
                    Storage::disk('public')->delete($song->file_path);
                }
                $data['file_path'] = $request->file('file_path')->store('files', 'public');
            }

            if ($song) {
                $song->update($data);
            } else {
                $song = Song::create($data);
            }

            if ($cate !== null) {
                $song->categories()->sync($cate);
            }
            if ($artistId !== null) {
                $song->artists()->sync($artistId);
            }
            DB::commit();

            return $song;
        } catch (HttpException) {
            DB::rollBack();
            if (! empty($data['cover'])) {
                Storage::disk('public')->delete($data['cover']);
            }
            Storage::disk('public')->delete($data['file_path']);
            abort(500, 'Something went wrong');
        }
    }

    public function store(StoreSongRequest $request)
    {
        $this->handleSong($request, null);

        return response()->json(['message' => 'Song created successfully'], 201);
    }

    public function update(UpdateSongRequest $request, $songId)
    {
        $this->handleSong($request, $this->find($songId));

        return response()->json(['message' => 'Song updated successfully'], 200);

    }

    public function show($songId)
    {
        $song = Song::with(['artists', 'categories'])->find($songId);
        if (! $song) {
            return abort(404, 'Song not found');
        }

        return response()->json(new SongResource($song));

    }

    public function index(GetSongRequest $request)
    {
        $songs = Song::search($request->search)->paginate(10);

        return SongResource::collection($songs);
    }

    public function delete($songId)
    {
        $song = $this->find($songId);
        if ($song->cover) {
            Storage::disk('public')->delete($song->cover);
        }
        Storage::disk('public')->delete($song->file_path);
        $song->categories()->detach();
        $song->artists()->detach();
        $song->delete();

        return response()->json(['message' => 'Song deleted successfully']);
    }
}
