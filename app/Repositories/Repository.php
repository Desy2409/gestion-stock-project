<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Institution;
use App\Models\SalePoint;

class Repository
{

    public $columns = [];

    public function reportIncludeCode($model, $selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $elements = $model::all();
        } else {
            $elements = $model::select($selectedDefaultFields)->get();
            // if ($startDate && $endDate) {
            //     $elements->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $elements;
    }

    public function reportIncludeReference($model, $selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $elements = $model::all();
        } else {
            $elements = $model::select($selectedDefaultFields)->get();
            // if ($startDate && $endDate) {
            //     $elements->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $elements;
    }

    public function registerReport($model, $selectedDefaultFields)
    {
        if (empty($selectedDefaultFields) || sizeof($selectedDefaultFields) == 0) {
            $elements = $model::all();
        } else {
            $elements = $model::select($selectedDefaultFields)->get();
            // if ($startDate && $endDate) {
            //     $elements->whereBetween('created_at', [$startDate, $endDate]);
            // }
        }

        return $elements;
    }

    public function oneJoinReport($childModel , $childTableName, $parentTableName, $childAlias, $parentAlias, $parentIdColumnNameInChild, $childSelectedFields, $parentSelectedFields)
    {
        $columns = [];
        if (!empty($childSelectedFields) && $childSelectedFields != null) {
            array_push($columns, $childTableName . '.id as ' . $childAlias . '_id');
            foreach ($childSelectedFields as $key => $field) {
                $column = $childTableName . '.' . $field . ' as ' . $childAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($parentSelectedFields) && $parentSelectedFields != null) {
            array_push($columns,
             $parentTableName . '.id as ' . $parentAlias . '_id');
            foreach ($parentSelectedFields as $key => $field) {
                $column = $parentTableName . '.' . $field . ' as ' . $parentAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($columns) && $columns != null) {
            $resultsBasedOnSelectedColumns = $childModel::join($parentTableName, $parentTableName . '.id', '=', $childTableName . '.' . $parentIdColumnNameInChild)->get($columns);
        } else {
            $resultsBasedOnSelectedColumns = $childModel::all();
        }


        return $resultsBasedOnSelectedColumns;
    }

    public function twoJoinReport($childModel, $childTableName, $childAlias, $firstParentTableName, $firstParentAlias, $secondParentTableName, $secondParentAlias, $firstParentIdColumnNameInChild, $secondParentIdColumnNameInChild, $childSelectedFields, $firstParentSelectedFields, $secondParentSelectedFields)
    {
        $columns = [];
        if (!empty($childSelectedFields) && $childSelectedFields != null) {
            array_push($columns, $childTableName . '.id as ' . $childAlias . '_id');
            foreach ($childSelectedFields as $key => $field) {
                $column = $childTableName . '.' . $field . ' as ' . $childAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($firstParentSelectedFields) && $firstParentSelectedFields != null) {
            array_push($columns, $firstParentTableName . '.id as ' . $firstParentAlias . '_id');
            foreach ($firstParentSelectedFields as $key => $field) {
                $column = $firstParentTableName . '.' . $field . ' as ' . $firstParentAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($secondParentSelectedFields) && $secondParentSelectedFields != null) {
            array_push($columns, $secondParentTableName . '.id as ' . $secondParentAlias . '_id');
            foreach ($secondParentSelectedFields as $key => $field) {
                $column = $secondParentTableName . '.' . $field . ' as ' . $secondParentAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($columns) && $columns != null) {
            $resultsBasedOnSelectedColumns = $childModel::join($firstParentTableName, $firstParentTableName . '.id', '=', $childTableName . '.' . $firstParentIdColumnNameInChild)
                ->join($secondParentTableName, $secondParentTableName . '.id', '=', $childTableName . '.' . $secondParentIdColumnNameInChild)->get($columns);
        } else {
            $resultsBasedOnSelectedColumns = $childModel::all();
        }


        return $resultsBasedOnSelectedColumns;
    }

    public function threeJoinReport($childModel, $childTableName, $childAlias, $firstParentTableName, $firstParentAlias, $secondParentTableName, $secondParentAlias, $thirdParentTableName, $thirdParentAlias, $firstParentIdColumnNameInChild, $secondParentIdColumnNameInChild, $thirdParentIdColumnNameInChild, $childSelectedFields, $firstParentSelectedFields, $secondParentSelectedFields, $thirdParentSelectedFields)
    {
        $columns = [];
        if (!empty($childSelectedFields) && $childSelectedFields != null) {
            array_push($columns, $childTableName . '.id as ' . $childAlias . '_id');
            foreach ($childSelectedFields as $key => $field) {
                $column = $childTableName . '.' . $field . ' as ' . $childAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($parentSelectedFields) && $parentSelectedFields != null) {
            array_push($columns, $firstParentTableName . '.id as ' . $firstParentAlias . '_id');
            foreach ($parentSelectedFields as $key => $field) {
                $column = $firstParentTableName . '.' . $field . ' as ' . $firstParentAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($parentSelectedFields) && $parentSelectedFields != null) {
            array_push($columns, $secondParentTableName . '.id as ' . $secondParentAlias . '_id');
            foreach ($parentSelectedFields as $key => $field) {
                $column = $secondParentTableName . '.' . $field . ' as ' . $secondParentAlias . '_' . $field;
                array_push($columns, $column);
            }
        }

        if (!empty($columns) && $columns != null) {
            $resultsBasedOnSelectedColumns = $childModel::join($firstParentTableName, $firstParentTableName . '.id', '=', $childTableName . '.' . $firstParentIdColumnNameInChild)
                ->join($secondParentTableName, $secondParentTableName . '.id', '=', $childTableName . '.' . $secondParentIdColumnNameInChild)->get($columns);
        } else {
            $resultsBasedOnSelectedColumns = $childModel::all();
        }


        return $resultsBasedOnSelectedColumns;
    }
}
