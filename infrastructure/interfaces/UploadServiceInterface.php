<?php

namespace Infrastructure\Interfaces;

interface UploadServiceInterface
{
    public function upload(array $files, array $types);

    public function getFile(array $files);
}
