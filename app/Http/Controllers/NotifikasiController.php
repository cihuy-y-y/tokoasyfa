<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function baca(Request $request)
    {
        $request->user()->forceFill(['notif_dibaca_at' => now()])->save();

        return back();
    }
}
