<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function terms()
    {
        $config = AppConfig::getSettings();
        $title = "Termos de Uso";
        $content = $config->terms_of_use;
        return view('pages.legal', compact('title', 'content'));
    }

    public function privacy()
    {
        $config = AppConfig::getSettings();
        $title = "Política de Privacidade";
        $content = $config->privacy_policy;
        return view('pages.legal', compact('title', 'content'));
    }
}
