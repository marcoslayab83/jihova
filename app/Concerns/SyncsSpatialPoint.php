<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait SyncsSpatialPoint
{
    protected static function bootSyncsSpatialPoint(): void
    {
        static::saving(function (Model $model): void {
            [$spatialColumn, $latColumn, $lngColumn] = $model->spatialPointColumns();
            $lat = $model->getAttribute($latColumn);
            $lng = $model->getAttribute($lngColumn);

            if ($lat === null || $lng === null) {
                return;
            }

            $driver = $model->getConnection()->getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $model->setAttribute(
                    $spatialColumn,
                    DB::raw(sprintf("ST_GeomFromText('POINT(%.7F %.7F)', 4326)", (float) $lat, (float) $lng))
                );
            } else {
                $model->setAttribute(
                    $spatialColumn,
                    sprintf('POINT(%.7F %.7F)', (float) $lng, (float) $lat)
                );
            }
        });
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    abstract protected function spatialPointColumns(): array;
}
