<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\PageBuilder\Settings\PageBuilderSettings;

class PageBuilderModuleSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:edit-settings');
    }

    public function index(PageBuilderSettings $settings): View
    {
        return view('pagebuilder::module-settings.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, PageBuilderSettings $settings): RedirectResponse
    {
        $settings->enabled = $request->boolean('enabled');
        $settings->save();

        flash()->success('Page Builder settings saved.');

        return redirect()->route('page-builder.module-settings.index');
    }
}
