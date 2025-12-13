<?php

namespace App\Exports;

use App\Models\Usaha;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PengerajinExport implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    public function collection()
    {
        $data = Usaha::with(['pengerajins', 'jenisUsahas', 'produks'])
            ->get()
            ->map(function ($usaha, $index) {
                return [
                    'No' => $index + 1,
                    'Nama Usaha' => $usaha->nama_usaha,
                    'Nama Pengerajin' => $usaha->pengerajins->pluck('nama_pengerajin')->unique()->implode(', '),
                    'Alamat Pengerajin' => $usaha->pengerajins->pluck('alamat_pengerajin')->unique()->implode(', '),
                    'Jenis Usaha' => $usaha->jenisUsahas->pluck('nama_jenis_usaha')->unique()->implode(', '),
                    'Kategori Produk' => $usaha->produks
                        ->map(fn($produk) => $produk->kategoriProduk->nama_kategori_produk ?? null)
                        ->filter()
                        ->unique()
                        ->implode(', '),
                ];
            });

        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Usaha',
            'Nama Pengerajin',
            'Alamat Pengerajin',
            'Jenis Usaha',
            'Kategori Produk',
        ];
    }

    // Nama tab sheet
    public function title(): string
    {
        return 'Data Pengerajin';
    }

    // Event untuk styling
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();

                // Sisipkan 3 baris kosong di atas headings
                $sheet->insertNewRowBefore(1, 3);

                // Judul utama di baris 1
                $sheet->setCellValue('A1', 'ASOSIASI WIRAUSAHA "SENOPATI"');
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Subjudul di baris 2
                $sheet->setCellValue('A2', 'DATA UMKM KERAJINAN PERAK, EMAS, PLATINA DAN LOGAM RW 04 KEMBANG BASEN');
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Heading tabel ada di baris 4 → tebal
                $sheet->getStyle("A4:{$lastColumn}4")->getFont()->setBold(true);

                // Border seluruh tabel (mulai baris 4)
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:{$lastColumn}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}
