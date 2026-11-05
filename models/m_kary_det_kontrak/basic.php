<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_kary_det_kontrak extends Model
{   
    use ModelTrait;

    protected $table    = 'm_kary_det_kontrak';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract","nomor"];

    public $columns     = ["id","m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","created_at","updated_at","duration","contract","nomor"];
    public $columnsFull = ["id:bigint","m_karyawan_id:bigint","m_divisi_id:bigint","m_dir_id:bigint","tipe_karyawan_id:integer","status:boolean","tgl_awal:date","tgl_akhir:date","created_at:datetime","updated_at:datetime","duration:integer","contract:string:191","nomor:string"];
    public $rules       = [];
    public $joins       = ["m_kary.id=m_kary_det_kontrak.m_karyawan_id","m_divisi.id=m_kary_det_kontrak.m_divisi_id","m_dir.id=m_kary_det_kontrak.m_dir_id","m_general.id=m_kary_det_kontrak.tipe_karyawan_id"];
    public $details     = [];
    public $heirs       = ["t_extend_kontrak"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract","nomor"];
    public $updateable  = ["m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","duration","contract","nomor"];
    public $searchable  = ["id","m_karyawan_id","m_divisi_id","m_dir_id","tipe_karyawan_id","status","tgl_awal","tgl_akhir","created_at","updated_at","duration","contract","nomor"];
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
}
