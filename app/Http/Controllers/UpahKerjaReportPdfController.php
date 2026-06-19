<?php

namespace App\Http\Controllers;

use App\Support\AccessControl;
use App\Support\UpahKerjaReportExport;
use Illuminate\Http\Request;

class UpahKerjaReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(AccessControl::canViewUpahKerjaReport(), 403);

        return UpahKerjaReportExport::fromRequest($request)->downloadPdf();
    }
}
