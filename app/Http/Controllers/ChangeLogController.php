<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;

class ChangeLogController extends Controller
{
    public function index()
    {
        $changelogPath = base_path('CHANGELOG.md');

        if (! File::exists($changelogPath)) {
            abort(404, 'Changelog not found');
        }

        $markdown = File::get($changelogPath);
        $converter = new CommonMarkConverter();
        $html = $converter->convert($markdown);

        return view('changelog', [
            'content' => $html,
        ]);
    }
}
