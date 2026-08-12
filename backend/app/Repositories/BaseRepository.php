<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public function __construct(protected Model $model)
    {
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * Apply generic sorting to a query.
     */
    protected function applySort(Builder $query, string $sortBy, string $sortDir): Builder
    {
        $column = in_array($sortBy, $this->sortableColumns(), true) ? $sortBy : 'created_at';

        return $query->orderBy($column, $sortDir === 'desc' ? 'desc' : 'asc');
    }

    /**
     * Columns that are safe to sort by.
     *
     * @return list<string>
     */
    abstract protected function sortableColumns(): array;
}
