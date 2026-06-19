<?php

namespace App\Http\Controllers;

use App\Support\AccessControl;
use App\Support\BrilinkReportExport;
use Illuminate\Http\Request;

class BrilinkReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(AccessControl::canAccessBrilink(), 403);

        return BrilinkReportExport::fromRequest($request)->downloadPdf();
    }
}
