<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_potongan_presensi extends Model
{   
    use ModelTrait;

    protected $table    = 't_potongan_presensi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["nomor","m_kary_id","m_kary_dir_id","m_kary_divisi_id","m_kary_dept_id","periode","periode_in_date","total_potongan","periode_id","deskripsi","status","creator_id","last_editor_id","detail_potongan"];

    public $columns     = ["id","nomor","m_kary_id","m_kary_dir_id","m_kary_divisi_id","m_kary_dept_id","periode","periode_in_date","total_potongan","periode_id","deskripsi","status","creator_id","last_editor_id","created_at","updated_at","detail_potongan"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_kary_id:bigint","m_kary_dir_id:bigint","m_kary_divisi_id:bigint","m_kary_dept_id:bigint","periode:string:191","periode_in_date:date","total_potongan:decimal","periode_id:bigint","deskripsi:text","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","detail_potongan:json"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_potongan_presensi.m_kary_id","m_dir.id=t_potongan_presensi.m_kary_dir_id","m_divisi.id=t_potongan_presensi.m_kary_divisi_id","m_dept.id=t_potongan_presensi.m_kary_dept_id","m_general.id=t_potongan_presensi.periode_id","default_users.id=t_potongan_presensi.creator_id","default_users.id=t_potongan_presensi.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id","periode","total_potongan"];
    public $createable  = ["nomor","m_kary_id","m_kary_dir_id","m_kary_divisi_id","m_kary_dept_id","periode","periode_in_date","total_potongan","periode_id","deskripsi","status","creator_id","last_editor_id","detail_potongan"];
    public $updateable  = ["nomor","m_kary_id","m_kary_dir_id","m_kary_divisi_id","m_kary_dept_id","periode","periode_in_date","total_potongan","periode_id","deskripsi","status","creator_id","last_editor_id","detail_potongan"];
    public $searchable  = ["id","nomor","m_kary_id","m_kary_dir_id","m_kary_divisi_id","m_kary_dept_id","periode","periode_in_date","total_potongan","periode_id","deskripsi","status","creator_id","last_editor_id","created_at","updated_at","detail_potongan"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function m_kary_dir() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dir', 'm_kary_dir_id', 'id');
    }
    public function m_kary_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_kary_divisi_id', 'id');
    }
    public function m_kary_dept() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dept', 'm_kary_dept_id', 'id');
    }
    public function periode() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'periode_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
}
