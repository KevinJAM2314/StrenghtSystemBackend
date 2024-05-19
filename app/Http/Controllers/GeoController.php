<?php

namespace App\Http\Controllers;

use App\Models\Geo;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    
    public function index(Request $request)
    {
        $geos = Geo::where('geo_id', $request->geo_id)->select('id', 'description')->get();

        return response()->json(['geos' => $geos]); 
    }
}
