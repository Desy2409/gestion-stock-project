<?php

namespace App\Http\Traits;

use App\Models\FileType;
use App\Models\UploadFile;

trait FileTrait
{

    function checkFileType($entity){
        // $code = substr($entity->code,0,2);
        $types = explode(',',FileType::where('code','=',substr($entity->code,0,2))->get('authorized_files'));
        
        $check=false;
        if(!empty($types) && sizeof($types)>0){
            $check=true;
        }

        return $check;
    }

    function fileName($user,$entity,$file){
        $fileName="";
        if ($entity->personalized_name!=null) {
            $fileName = substr($entity->code,0,2) . ' - ' . $entity->personalized_name .'.' . $file->getClientOriginalExtension();
        }else{
            $fileName = substr($entity->code,0,2) . ' - ' . $user->last_name . ' ' . $user->first_name . '.' . $file->getClientOriginalExtension();
        }

        return $fileName;
    }

    function storePath($user,$entity,$file,$folder){
        if ($folder) {
            
        }else{
            $path = $file->storeAs(substr($entity->code,0,2).'/' . $user->last_name . ' ' . $user->first_name, $this->fileName($user,$entity,$file), 'public');
        }

        return $path;
    }

    function storeFile($user,$entity,$folder,$files)
    {
        $fileType=FileType::where('code','=',substr($entity->code,0,2))->first();
        if (!empty($files)&&sizeof($files)>0) {
            foreach ($files as $key => $file) {
                $uploadFile = UploadFile::create([
                    // 'code'=>$file->,
                    'name'=>$file->$file->getClientOriginalName(),
                    'personalized_name'=>$file->personalized_name,
                    'original_file_name'=>$file->getClientOriginalName(),
                    'path'=>$this->storePath($user,$entity,$file,$folder),
                    'size'=>$file->personalized_name,
                    'extension'=>$file->getClientOriginalExtension(),
                    'file_type_id'=>$fileType->id,
                ]);
            }  
        }
        
        return $uploadFile;
    }
}
