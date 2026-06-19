<?php

namespace App\Http\Controllers;

use App\Support\PwaManifest;
use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()
            ->json(PwaManifest::data())
            ->header('Content-Type', 'application/manifest+json');
    }
}
