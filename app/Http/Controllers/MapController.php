<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        $churches = Church::active()->orderBy('name')->get();

        return view('map', [
            'churches'   => $churches,
            'categories' => ['All', 'Basilica', 'Cathedral', 'Shrine', 'Church', 'Chapel', 'Heritage'],
        ]);
    }
}
