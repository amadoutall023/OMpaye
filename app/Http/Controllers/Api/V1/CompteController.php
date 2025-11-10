<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CompteRequest;
use App\Models\Compte;
use App\Models\User; 
use App\Http\Resources\CompteResource;

class CompteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function show()
    {
        $compte = auth()->user()->compte;

        return response()->json([
            'success' => true,
            'data' => new CompteResource($compte),
        ]);
    }
}