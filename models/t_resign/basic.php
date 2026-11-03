<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_resign extends Model
{   
    use ModelTrait;

    protected $table    = 't_resign';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["nomor","m_kary_id","m_dir_id","m_divisi_id","tipe_resign_id","date","keterangan","status","creator_id","last_editor_id","surat_resign","surat_prakerin"];

    public $columns     = ["id","nomor","m_kary_id","m_dir_id","m_divisi_id","tipe_resign_id","date","keterangan","status","creator_id","last_editor_id","created_at","updated_at","surat_resign","surat_prakerin"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_kary_id:bigint","m_dir_id:bigint","m_divisi_id:bigint","tipe_resign_id:bigint","date:date","keterangan:text","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","surat_resign:string:191","surat_prakerin:string:191"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_resign.m_kary_id","m_dir.id=t_resign.m_dir_id","m_divisi.id=t_resign.m_divisi_id","m_general.id=t_resign.tipe_resign_id","default_users.id=t_resign.creator_id","default_users.id=t_resign.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["date"];
    public $createable  = ["nomor","m_kary_id","m_dir_id","m_divisi_id","tipe_resign_id","date","keterangan","status","creator_id","last_editor_id","surat_resign","surat_prakerin"];
    public $updateable  = ["nomor","m_kary_id","m_dir_id","m_divisi_id","tipe_resign_id","date","keterangan","status","creator_id","last_editor_id","surat_resign","surat_prakerin"];
    public $searchable  = ["id","nomor","m_kary_id","m_dir_id","m_divisi_id","tipe_resign_id","date","keterangan","status","creator_id","last_editor_id","created_at","updated_at","surat_resign","surat_prakerin"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function m_dir() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dir', 'm_dir_id', 'id');
    }
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
    public function tipe_resign() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'tipe_resign_id', 'id');
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
