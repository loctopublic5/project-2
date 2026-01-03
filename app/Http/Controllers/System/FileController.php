<?php

namespace App\Http\Controllers\System;

use App\Http\Requests\System\UploadFileRequest; 
use App\Services\System\FileService;           
use App\Http\Resources\System\FileResource;           
use Exception;
use App\Traits\apiResponse;
class FileController 
{
    use apiResponse;
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * API Upload File
     * Endpoint: POST /api/v1/upload
     */
    public function store(UploadFileRequest $request){
        try{
        $fileRecord =$this->fileService->upload(
            $request->file('file'),
            $request->input('target_type'),
            $request->input('target_id')
        );

        return $this->success(new FileResource($fileRecord), 'Upload file thành công!', 201 );
        }catch(Exception $e){
            return $this->error('Upload thất bại: ' . $e->getMessage(), 500);
        }
    }

    
}
