<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    use Importable;

    /**
     * Convert the imports file into collection
     * 
     * @param Illuminate\Support\Collection $collection //
     *
     * @return array
     */
    public function collection(Collection $collection)
    {
        return $collection;
    }
}
