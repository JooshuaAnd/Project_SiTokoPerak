<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengerajinExport;

class ExportController extends Controller
{
    public function index()
    {
        return view('admin.export.index-export');
    }
    public function exportPengerajin()
    {
        return Excel::download(new PengerajinExport, 'pengerajin.xlsx');
    }
}
