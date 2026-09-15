<?php

namespace App\Models\CustomModels;

class m_kary_det_gaji extends \App\Models\BasicModels\m_kary_det_gaji
{    
    private $helper;

    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $nomor = @$arrayData['nomor'] ?? $this->helper->generateNomor('KODE RIWAYAT GAJI');
        $newArrayData = array_merge($arrayData, [
            'nomor' => $nomor,
        ]);

        return [
            'model' => $model,
            'data'  => $newArrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        // Jika status aktif dan ini record terbaru, sinkronkan ke m_kary.m_standart_gaji_id
        if ($model->status && $model->m_karyawan_id && $model->m_standart_gaji_id) {
            \App\Models\BasicModels\m_kary::where('id', $model->m_karyawan_id)->update([
                'm_standart_gaji_id' => $model->m_standart_gaji_id
            ]);
        }
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        if ($model->status && $model->m_karyawan_id && $model->m_standart_gaji_id) {
            \App\Models\BasicModels\m_kary::where('id', $model->m_karyawan_id)->update([
                'm_standart_gaji_id' => $model->m_standart_gaji_id
            ]);
        }
    }

    /**
     * Helper untuk mengambil standar gaji yang aktif pada periode tertentu
     * Fallback ke m_kary.m_standart_gaji_id jika riwayat belum tersedia
     */
    public static function getActiveGaji($m_karyawan_id, $periode_awal = null, $periode_akhir = null)
    {
        $query = self::where('m_karyawan_id', $m_karyawan_id)
            ->where('status', true);

        if ($periode_akhir) {
            $query->where('tgl_awal', '<=', $periode_akhir);
        }
        if ($periode_awal) {
            $query->where(function ($q) use ($periode_awal) {
                $q->whereNull('tgl_akhir')
                  ->orWhere('tgl_akhir', '>=', $periode_awal);
            });
        }

        $activeDet = $query->orderBy('tgl_awal', 'desc')->first();

        if ($activeDet && $activeDet->m_standart_gaji_id) {
            return \App\Models\CustomModels\m_standart_gaji::find($activeDet->m_standart_gaji_id);
        }

        // Fallback untuk karyawan lama yang belum memiliki data di m_kary_det_gaji
        $kary = \App\Models\BasicModels\m_kary::find($m_karyawan_id);
        if ($kary && $kary->m_standart_gaji_id) {
            return \App\Models\CustomModels\m_standart_gaji::find($kary->m_standart_gaji_id);
        }

        return null;
    }
}