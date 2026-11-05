<?php

namespace App\Models\CustomModels;

class t_surat extends \App\Models\BasicModels\t_surat
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_signLetter($req)
    {
        \DB::beginTransaction();
        try {
            $data = $this->find($req->id);
            if (!$data) {
                return getCore('Helper')->customResponse("Data tidak ditemukan", 422);
            }

            if ($data->is_signed) {
                return getCore('Helper')->customResponse("Surat sudah ditandatangani sebelumnya", 422);
            }

            if (!$req->signature_img) {
                return getCore('Helper')->customResponse("Tanda tangan kosong!", 422);
            }

            $data->is_signed = true;
            $data->signature_img = $req->signature_img;
            $data->signed_at = \Carbon\Carbon::now();
            $data->save();

            \DB::commit();
            return getCore('Helper')->customResponse("Tanda tangan berhasil disimpan");
        } catch (\Exception $e) {
            \DB::rollback();
            return getCore('Helper')->responseCatch($e);
        }
    }
}