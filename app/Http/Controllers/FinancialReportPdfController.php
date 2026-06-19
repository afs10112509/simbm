<?php

namespace App\Http\Controllers;

use App\Support\AccessControl;
use App\Support\FinancialReportExport;
use Illuminate\Http\Request;

class FinancialReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(AccessControl::canViewFinancialReport(), 403);

        return FinancialReportExport::fromRequest($request)->downloadPdf();
    }
}
