<?php

namespace App\Utils;


use App\Models\FileUpload;
use Exception;
use Illuminate\Support\Facades\File as FileB;
use Illuminate\Support\Facades\Storage;

class FileUtil
{
    public $storage;
    public $path;
    public $entity;

    public function __construct($path)
    {
        $this->storage = Storage::disk('local');
        $this->path =  $path;
    }

    public function storePath($id, $name = '')
    {
        //        return storage_path('app/'.$this->path.'/'.$id.'/'.$name);
        return $this->path . '/' . $id . '/' . $name;
    }

    public function filePath($entity, $name)
    {
        $entityFolder = $this->path . $entity->id;
        return storage_path('app/' . $entityFolder . '/' . $name);
    }

    public function createFile($entity, $file, $personalizedFilename = null)
    {
        try {
            $ext = $file->getClientOriginalExtension();
            $name = rand() . '.' . $ext;
            if ($personalizedFilename) {
                $this->moveUploadEntityFile($entity, $file, $personalizedFilename);
            } else {
                $this->moveUploadEntityFile($entity, $file, $name);
            }

            $fileSaved = FileUpload::create([
                "mime" => $file->getClientMimeType(),
                "original_filename" => $file->getClientOriginalName(),
                "filename" => $name,
                "link" => $this->storePath($entity->id, $name),
                "personalized_filename" => $personalizedFilename,
                "fileable_type" => get_class($entity),
                "fileable_id" => $entity->id
            ]);

            return $fileSaved;
        } catch (Exception $e) {
            return null;
        }
    }

    public function moveUploadEntityFile($entity, $file, $name)
    {
        $entityFolder = $this->path;
        if ($this->addId) {
            $entityFolder .= '/' . $entity->id;
        }

        $extension = $file->getClientOriginalExtension();
        if ($this->storage->exists($entityFolder)) {
            $this->storage->put($entityFolder . '/' . $name,  FileB::get($file));
        } else {

            $this->storage->makeDirectory($entityFolder);
            $this->storage->put($entityFolder . '/' . $name,  FileB::get($file));
        }

        return true;
    }

    public function unsetFile($entity, $id)
    {

        if ($entity && $this->storage->exists($this->path . $id . '/' . $entity->filename)) {
            if ($this->storage->delete($this->path . $id . '/' . $entity->filename)) {
                return true;
            }
        }

        return false;
    }

    // public function createFileInPersonalizedFolder($folder, $file, $personalizedFilename)
    // {
    //     try {
    //         $ext = $file->getClientOriginalExtension();
    //         $name = rand() . '.' . $ext;
    //         if ($personalizedFilename) {
    //             $this->moveUploadEntityFile($folder, $file, $personalizedFilename);
    //         } else {
    //             $this->moveUploadEntityFile($folder, $file, $name);
    //         }

    //         $fileSaved = FileUpload::create([
    //             "mime" => $file->getClientMimeType(),
    //             "original_filename" => $file->getClientOriginalName(),
    //             "filename" => $name,
    //             "link" => $this->storePath($folder, $name),
    //             "personalized_filename" => $personalizedFilename,
    //             "fileable_type" => get_class($folder),
    //             "fileable_id" => $folder->id
    //         ]);

    //         return $fileSaved;
    //     } catch (Exception $e) {
    //         return null;
    //     }
    // }
}
