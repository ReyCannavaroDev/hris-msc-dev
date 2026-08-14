<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_extend_kontrak extends Model
{   
    use ModelTrait;

    protected $table    = 't_extend_kontrak';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract_template","contract_signed","m_kary_det_kontrak_id","nomor","catatan"];

    public $columns     = ["id","m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract_template","contract_signed","m_kary_det_kontrak_id","created_at","updated_at","nomor","catatan"];
    public $columnsFull = ["id:bigint","m_karyawan_id:bigint","m_divisi_id:bigint","m_dir_id:bigint","tipe_karyawan_id:integer","status:string:191","tgl_awal:date","tgl_akhir:date","duration:integer","contract_template:string:191","contract_signed:string:191","m_kary_det_kontrak_id:bigint","created_at:datetime","updated_at:datetime","nomor:string:191","catatan:string:191"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_extend_kontrak.m_karyawan_id","m_divisi.id=t_extend_kontrak.m_divisi_id","m_dir.id=t_extend_kontrak.m_dir_id","m_general.id=t_extend_kontrak.tipe_karyawan_id","m_kary_det_kontrak.id=t_extend_kontrak.m_kary_det_kontrak_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract_template","contract_signed","m_kary_det_kontrak_id","nomor","catatan"];
    public $updateable  = ["m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract_template","contract_signed","m_kary_det_kontrak_id","nomor","catatan"];
    public $searchable  = ["id","m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract_template","contract_signed","m_kary_det_kontrak_id","created_at","updated_at","nomor","catatan"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_karyawan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_karyawan_id', 'id');
    }
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
    public function m_dir() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dir', 'm_dir_id', 'id');
    }
    public function tipe_karyawan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'tipe_karyawan_id', 'id');
    }
    public function m_kary_det_kontrak() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary_det_kontrak', 'm_kary_det_kontrak_id', 'id');
    }
}