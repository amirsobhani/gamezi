<?php

namespace App\Services;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Infrastructure\Interfaces\UploadServiceInterface;
use Modules\Game\Entities\Media;

class UploadService
{
    /**
     * file uploader
     *
     * @param array $files list of files for upload
     * @param array|string $types The applied type to the file attributes.
     * @param int $entityId
     * @param string $file_path
     * @param string $bucket
     * @return array
     */
    public function upload(array $files, array|string $types, int $entityId = 0, string $file_path = '', string $bucket = '')
    {
        $uploaded = [];
        $bucketName = empty($bucket) ? config('filesystems.disks.s3.bucket') : $bucket;
        foreach ($files as $index => $file) {
            $type = is_array($types) ? $types[$index] : $types;
            $uploaded[] = $this->uploadFile($file, $type, $entityId, $bucketName, $file_path);
        }
        return $uploaded;
    }

    function uploadFile($file, string $type, int $clinicId, string $bucket, string $file_path = ''): array
    {
        $filePath = empty($file_path) ? "$clinicId/$type" : "$clinicId/$file_path/$type";
        $inputs = [];
        $filePath = $filePath . date("/ym");
        $path = $file->storeAs($filePath, \Illuminate\Support\Str::random(50) . '.' . $file->getClientOriginalExtension());
        $pathInfo = pathinfo($path);
        $inputs["path"] = $filePath;
        $inputs["bucket"] = $bucket;
        $inputs["user_id"] = Auth::id();
        $inputs["format"] = $file->extension();
        $inputs["mime_type"] = \Illuminate\Support\Str::limit($file->getMimeType(), 31, null);
        $inputs["type"] = $type;
        $inputs["size"] = $file->getSize();
        $inputs["width"] = 0;
        $inputs["height"] = 0;
        $inputs["name"] = $pathInfo["basename"];
        $inputs["file_name"] = $pathInfo["filename"];
        return $inputs;
    }

    function uploadFileByUrl(string $fileUrl, string $type, int $entityId, string $bucket, string $file_path = ''): array
    {
        $res = Http::get($fileUrl);
        // Get file content
        $fileContent = $res->body();

        $fileName = pathinfo($fileUrl, PATHINFO_BASENAME);

        $filePath = empty($file_path) ? "$entityId/$type" : "$file_path/$entityId/$type";
        $inputs = [];
        $filePath = $filePath . date("/ym/") . $fileName;

        Storage::put($filePath, $fileContent);

        $headers = get_headers($fileUrl, TRUE);
        $mimeType = $headers["Content-Type"];

        $pathInfo = pathinfo($filePath);
        $inputs["path"] = $filePath;
        $inputs["bucket"] = $bucket;
        $inputs["user_id"] = Auth::id();
        $inputs["format"] = pathinfo($fileUrl, PATHINFO_EXTENSION);
        $inputs["mime_type"] = \Illuminate\Support\Str::limit($mimeType, 31, null);
        $inputs["type"] = $type;
        $inputs["size"] = Storage::fileSize($filePath);
        $inputs["width"] = 0;
        $inputs["height"] = 0;
        $inputs["name"] = $pathInfo["basename"];
        $inputs["file_name"] = $pathInfo["filename"];
        return $inputs;
    }

    public function getFile(array $fileIds)
    {
        $data = [];

        $files = Media::query()->whereIn("id", $fileIds)->get();

        foreach ($files as $file) {
            $data[] = Storage::get($file->path . "/" . $file->name);
        }

        return $data;
    }

    protected function removeFile()
    {
    }

    public function url($path): string
    {
        return Storage::url(config('filesystems.disks.s3.bucket') . "/$path");
    }
}

