<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserThemeController extends Controller
{
    /**
     * Update user theme preference
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark,system'
        ]);

        $user = Auth::user();
        if ($user) {
            $user->theme_preference = $request->theme;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'theme' => $request->theme
        ]);
    }
}

