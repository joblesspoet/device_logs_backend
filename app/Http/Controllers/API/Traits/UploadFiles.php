<?php

namespace App\Http\Controllers\API\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait UploadFiles {

    public function uploadVideos($request, $logBook, $folder = 'logbook/videos/') {

        if ($request->delete_videos) {
            $logBook->videos()->whereIn('id', $request->delete_videos)->delete();
        }
        if ($request->videos) {
            $getVideos = $this->createFilesFromBase64($request, $request->videos, $folder);

            foreach ($getVideos as $value) {

                $video = $logBook->videos()->create(['file' => $value]);
                \App\Jobs\ProcessVideo::dispatch($video, $folder . 'thumbnails/' . $request->user()->id);
            }
        }
    }

    public function uploadPhotos($request, $logBook, $folder = 'logbook/photos/') {
        if ($request->delete_photos) {
            $logBook->photos()->whereIn('id', $request->delete_photos)->delete();
        }
        if ($request->photos) {
            $getPhotos = $this->createFilesFromBase64($request, $request->photos, $folder);
            foreach ($getPhotos as $value) {
                $logBook->photos()->create(['url' => $value]);
            }
        }
    }

    public function uploadAudios($request, $logBook, $folder = 'logbook/audios/') {

        if ($request->delete_audios) {
            $logBook->audios()->whereIn('id', $request->delete_audios)->delete();
        }

        if ($request->audios) {
            $getAudios = $this->createFilesFromBase64($request, $request->audios, $folder,'mp3');
            foreach ($getAudios as $value) {
                $logBook->audios()->create(['file' => $value]);
            }
        }
    }

    function createFilesFromBase64($request, $files, $folder,$extension='') {

        $filesArray = [];

        foreach ($files as $key => $file_data) {
            if (
                    is_string($file_data) &&
                    (preg_match('#http?://#', $file_data) or preg_match('#https?://#', $file_data))
            ) {

                continue;
            }
            $count = $key + 1;
            $name = md5('file_' . $count . time());
            $file_name = $folder . $request->user()->id . '/' . $name; //generating unique file name;

            if ($file_data instanceof UploadedFile) {
                $fileObj = $file_data;
                $mime_type = $fileObj->getMimeType();
            } else {
                $file = explode("base64,", $file_data);
                $file = str_replace(' ', '+', $file[1]);
                $fileObj = base64_decode($file);
                $f = finfo_open();
                $mime_type = finfo_buffer($f, $fileObj, FILEINFO_MIME_TYPE);
            }

            $type = ($extension)?$extension:$this->mime2ext($mime_type);

            if ($type and $fileObj != "") {
                if ($file_data instanceof UploadedFile) {
                    Storage::disk('public')->putFileAs($folder . $request->user()->id , $fileObj,$name. "." . $type);
                } else {
                    Storage::disk('public')->put($file_name . "." . $type, $fileObj);
                }
              
                $filesArray[] = "storage/" . $file_name . "." . $type;
            }
        }

        return $filesArray;
    }

    public function mime2ext($mime) {
        $mime_map = [
            'video/3gpp2' => '3g2',
            'video/3gp' => '3gp',
            'video/3gpp' => '3gp',
            'audio/x-acc' => 'aac',
            'audio/ac3' => 'ac3',
            'audio/x-aiff' => 'aif',
            'audio/aiff' => 'aif',
            'audio/x-au' => 'au',
            'video/x-msvideo' => 'avi',
            'video/msvideo' => 'avi',
            'video/avi' => 'avi',
            'application/x-troff-msvideo' => 'avi',
            'video/x-f4v' => 'f4v',
            'audio/x-flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'image/jp2' => 'jp2',
            'video/mj2' => 'jp2',
            'image/jpx' => 'jp2',
            'image/jpm' => 'jp2',
            'image/jpeg' => 'jpeg',
            'image/pjpeg' => 'jpeg',
            'audio/midi' => 'mid',
            'application/vnd.mif' => 'mif',
            'video/quicktime' => 'mov',
            'video/x-sgi-movie' => 'movie',
            'audio/mpeg' => 'mp3',
            'audio/mpg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'audio/x-realaudio' => 'ra',
            'audio/x-pn-realaudio' => 'ram',
            'text/srt' => 'srt',
            'image/svg+xml' => 'svg',
            'image/tiff' => 'tiff',
            'text/plain' => 'txt',
            'text/x-vcard' => 'vcf',
            'application/videolan' => 'vlc',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/wav' => 'wav',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
        ];

        return isset($mime_map[$mime]) ? $mime_map[$mime] : false;
    }

}
