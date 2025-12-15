<?php

namespace App\Http\Controllers;

use App\Models\ContentDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webtechsolutions\ContentEngine\Events\ContentDownloadedEvent;
use Webtechsolutions\ContentEngine\Models\Content;
use ZipArchive;

class ContentDownloadController extends Controller
{
    public function download(Content $content)
    {
        // Check authorization
        if (! auth()->check()) {
            abort(403, 'You must be logged in to download content.');
        }

        // Record the download
        ContentDownload::recordDownload($content->id, auth()->id());

        // Increment download counter
        $content->incrementDownloads();

        // Fire event
        event(new ContentDownloadedEvent($content));

        // Handle different content types
        return match ($content->type) {
            Content::TYPE_DIGITAL_FILE => $this->downloadDigitalFile($content),
            Content::TYPE_IMAGE_GALLERY => $this->downloadImageGallery($content),
            Content::TYPE_MARKDOWN_POST => $this->downloadMarkdownPost($content),
            Content::TYPE_ARTICLE => $this->downloadArticle($content),
            Content::TYPE_RPG_MODULE => $this->downloadRpgModule($content),
            default => abort(404, 'Unknown content type'),
        };
    }

    protected function downloadDigitalFile(Content $content)
    {
        if (empty($content->file_path)) {
            abort(404, 'File not found');
        }

        $filePath = storage_path('app/public/'.$content->file_path);

        if (! file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath);
    }

    protected function downloadImageGallery(Content $content)
    {
        $files = [];

        // Add featured image
        if ($content->featured_image) {
            $files[] = storage_path('app/public/'.$content->featured_image);
        }

        // Add gallery images
        if ($content->gallery_images && is_array($content->gallery_images)) {
            foreach ($content->gallery_images as $image) {
                $files[] = storage_path('app/public/'.$image);
            }
        }

        if (empty($files)) {
            abort(404, 'No files found');
        }

        // Create ZIP
        return $this->createZip($content, $files);
    }

    protected function downloadMarkdownPost(Content $content)
    {
        return $this->downloadTextContent($content);
    }

    protected function downloadArticle(Content $content)
    {
        return $this->downloadTextContent($content);
    }

    protected function downloadRpgModule(Content $content)
    {
        return $this->downloadTextContent($content);
    }

    protected function downloadTextContent(Content $content)
    {
        $files = [];

        // Create markdown file
        $markdownContent = "# {$content->title}\n\n";

        if ($content->excerpt) {
            $markdownContent .= "## Excerpt\n\n{$content->excerpt}\n\n";
        }

        if ($content->body) {
            $markdownContent .= "## Content\n\n{$content->body}\n";
        }

        // Save temporary markdown file
        $tempMarkdown = storage_path('app/temp/'.uniqid().'_'.\Illuminate\Support\Str::slug($content->title).'.md');

        // Ensure temp directory exists
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        file_put_contents($tempMarkdown, $markdownContent);
        $files[] = $tempMarkdown;

        // Add featured image if exists
        if ($content->featured_image) {
            $featuredImagePath = storage_path('app/public/'.$content->featured_image);
            if (file_exists($featuredImagePath)) {
                $files[] = $featuredImagePath;
            }
        }

        // Create ZIP
        $zipPath = $this->createZip($content, $files);

        // Clean up temporary markdown file
        @unlink($tempMarkdown);

        return $zipPath;
    }

    protected function createZip(Content $content, array $files)
    {
        $zipFileName = \Illuminate\Support\Str::slug($content->title).'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        }

        if (! file_exists($zipPath)) {
            abort(500, 'Failed to create ZIP file');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
