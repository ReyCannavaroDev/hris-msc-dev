<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_kary_det_gaji extends Model
{   
    use ModelTrait;

    protected $table    = 'm_kary_det_gaji';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_karyawan_id","m_standart_gaji_id","tipe_karyawan_id","tgl_awal","tgl_akhir","status","keterangan","nomor"];

    public $columns     = ["id","m_karyawan_id","m_standart_gaji_id","tipe_karyawan_id","tgl_awal","tgl_akhir","status","keterangan","nomor","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_karyawan_id:bigint","m_standart_gaji_id:bigint","tipe_karyawan_id:bigint","tgl_awal:date","tgl_akhir:date","status:boolean","keterangan:text","nomor:string:100","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=m_kary_det_gaji.m_karyawan_id","m_standart_gaji.id=m_kary_det_gaji.m_standart_gaji_id","m_general.id=m_kary_det_gaji.tipe_karyawan_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["m_karyawan_id","m_standart_gaji_id","tipe_karyawan_id","tgl_awal","tgl_akhir","status","keterangan","nomor"];
    public $updateable  = ["m_karyawan_id","m_standart_gaji_id","tipe_karyawan_id","tgl_awal","tgl_akhir","status","keterangan","nomor"];
    public $searchable  = ["id","m_karyawan_id","m_standart_gaji_id","tipe_karyawan_id","tgl_awal","tgl_akhir","status","keterangan","nomor","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_karyawan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_karyawan_id', 'id');
    }
    public function m_standart_gaji() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_standart_gaji', 'm_standart_gaji_id', 'id');
    }
    public function tipe_karyawan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'tipe_karyawan_id', 'id');
    }
}
