<?php

namespace App\Repositories;

use App\Models\Acta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActaRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Acta::with(['usuario', 'rol', 'inmueble', 'productos', 'firmas'])
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Acta
    {
        return Acta::with(['usuario', 'rol', 'inmueble', 'productos', 'firmas'])->findOrFail($id);
    }

    public function create(array $data): Acta
    {
        return Acta::create($data);
    }

    public function update(Acta $acta, array $data): bool
    {
        return $acta->update($data);
    }

    public function delete(Acta $acta): bool
    {
        return $acta->delete();
    }
}
