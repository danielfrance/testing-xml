<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class FileService
{

    public function uploadFile($teamID, $file)
    {
        // TODO: make sure the file is no larger than 4mb. if so, compress it
        // TODO: make sure the file is a valid file type (jpg, jpeg, png, or pdf)
        // TODO: rename the file to a unique name with no spaces or special characters
        return Storage::disk('s3')->put('team_id_' . $teamID, $file);
    }

    public function deleteFile($path)
    {
        return Storage::disk('s3')->delete($path);
    }

    public function getFile()
    {
        // get file from S3 or GCP
    }

    public function getFiles()
    {
        // get files from S3 or GCP
    }

    public function getFilesByOwner()
    {
        // get files from S3 or GCP by owner
    }

    public function storeFileData($data)
    {
        // store file data in database
        return 1;
    }

    public function updateFileData($fileID, $data)
    {
        // update file data in database
        // the file would be uploaded to S3 or GCP
        // the file path would be stored in the database
        // the file ID would be returned

        return 2;
    }

    public function deleteFileData()
    {
        // delete file data from database
    }

    public function getFileData()
    {
        // get file data from database
    }

    public function downloadFile()
    {
        // download file from S3 or GCP
    }
}
