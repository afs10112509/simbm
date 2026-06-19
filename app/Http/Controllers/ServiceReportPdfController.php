<?php

namespace App\Http\Controllers;

use App\Support\AccessControl;
use App\Support\ServiceReportExport;
use Illuminate\Http\Request;

class ServiceReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(AccessControl::canViewServiceReport(), 403);

        return ServiceReportExport::fromRequest($request)->downloadPdf();
    }
}
