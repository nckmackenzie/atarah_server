<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO - Fetch forms based on the user's permissions
        $forms = Form::where('active', true)->orderBy('module_id')->orderBy('order')->get();
        return response()->json(['data' => $forms], 200);
    }

    
}
