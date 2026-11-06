<?php

namespace App\Http\Controllers;

use App\Http\Requests\PJ\DropdownPJRequest;
use App\Http\Requests\PJ\IndexPJRequest;
use App\Http\Requests\PJ\StorePJRequest;
use App\Http\Requests\PJ\UpdatePJRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiResourceCollection;
use App\Http\Resources\PenanggungJawabResource;
use App\Models\PenanggungJawab;
use Illuminate\Support\Facades\Log;

class PenanggungJawabController extends Controller
{
    /**
     * Display a listing of the resource.
     * Get /api/penanggung-jawab
     */
    public function index(IndexPJRequest $request): ApiResourceCollection
    {
        $validated = $request->validated();

        $query = PenanggungJawab::filter(
            search: $validated['search'] ?? null,
        )
            ->orderBy('nama')
            ->paginate($validated['per_page']);

        return PenanggungJawabResource::collection($query)
            ->message('Data penanggung jawab berhasil diambil');
    }

    /**
     * Display the specified resource.
     * Get /api/penanggung-jawab/{id}
     */
    public function show(string $id): PenanggungJawabResource
    {
        $pj = PenanggungJawab::with(['transaksi', 'transaksi.akun:id,nama_akun'])->findOrFail($id);

        return (new PenanggungJawabResource($pj))
            ->message('Data transaksi penanggung jawab berhasil diambil');
    }

    /**
     * Get list penanggung jawab untuk dropdown (hanya id dan nama).
     * Get /api/penanggung-jawab/dropdown
     */
    public function dropdown(DropdownPJRequest $request): ApiResourceCollection
    {
        $validated = $request->validated();

        $pj = PenanggungJawab::filter(
            search: $validated['search'] ?? null,
        )
            ->orderBy('nama')->get(['id', 'nama']);

        return PenanggungJawabResource::collection($pj)
            ->message('Data penanggung jawab untuk dropdown berhasil diambil');
    }

    /**
     * Store a newly created resource in storage.
     * Post /api/penanggung-jawab
     */
    public function store(StorePJRequest $request): PenanggungJawabResource
    {
        $validated = $request->validated();

        $pj = PenanggungJawab::create([
            'nama' => $validated['nama'],
        ]);

        Log::info('Berhasil menyimpan data penanggung jawab', [
            'who' => [
                'user_id' => auth()->id(),
                'role' => auth()->user()?->role,
            ],
            'action' => 'POST /api/penanggung-jawab',
            'reason' => 'Penambahan entitas penanggung jawab baru (ID: '.$pj->id.')',
        ]);

        return (new PenanggungJawabResource($pj))
            ->message('Penanggung jawab berhasil dibuat');
    }

    /**
     * Update the specified resource in storage.
     * Put /api/penanggung-jawab/{id}
     */
    public function update(UpdatePJRequest $request, string $id): PenanggungJawabResource
    {
        $validated = $request->validated();

        $pj = PenanggungJawab::findOrFail($id);

        $pj->update([
            'nama' => $validated['nama'],
        ]);

        Log::info('Berhasil memperbarui data penanggung jawab', [
            'who' => [
                'user_id' => auth()->id(),
                'role' => auth()->user()?->role,
            ],
            'action' => 'PUT /api/penanggung-jawab/'.$id,
            'reason' => 'Pembaruan data penanggung jawab (ID: '.$pj->id.')',
        ]);

        return (new PenanggungJawabResource($pj))
            ->message('Penanggung jawab berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     * Delete /api/penanggung-jawab/{id}
     */
    public function destroy(string $id): ApiResource
    {
        $pj = PenanggungJawab::findOrFail($id);

        $pj->delete();

        Log::info('Berhasil menghapus data penanggung jawab', [
            'who' => [
                'user_id' => auth()->id(),
                'role' => auth()->user()?->role,
            ],
            'action' => 'DELETE /api/penanggung-jawab/'.$id,
            'reason' => 'Penghapusan data penanggung jawab (ID: '.$id.')',
        ]);

        return (new ApiResource(null))
            ->message('Penanggung jawab berhasil dihapus');
    }
}
