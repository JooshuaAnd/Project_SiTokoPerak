<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimpleCollectionExport implements FromCollection, WithHeadings
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected Collection $rows;

    /**
     * @var array
     */
    protected array $headings;

    /**
     * @param  \Illuminate\Support\Collection|array  $rows
     * @param  array  $headings
     */
    public function __construct($rows, array $headings = [])
    {
        // pastikan selalu Collection
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
        $this->headings = $headings;
    }

    /**
     * Data untuk di-export
     */
    public function collection()
    {
        return $this->rows;
    }

    /**
     * Heading (baris pertama Excel)
     */
    public function headings(): array
    {
        return $this->headings;
    }
}
