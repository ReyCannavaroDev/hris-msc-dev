<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_surat extends Model
{   
    use ModelTrait;

    protected $table    = 't_surat';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_karyawan_id","jenis_surat","level_surat","tanggal_terbit","alasan","file_dokumen","is_signed","signature_img","signed_at","creator_id","last_editor_id"];

    public $columns     = ["id","m_karyawan_id","jenis_surat","level_surat","tanggal_terbit","alasan","file_dokumen","is_signed","signature_img","signed_at","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_karyawan_id:bigint","jenis_surat:string:100","level_surat:string:50","tanggal_terbit:date","alasan:text","file_dokumen:string:255","is_signed:boolean","signature_img:text","signed_at:datetime","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_surat.m_karyawan_id","default_users.id=t_surat.creator_id","default_users.id=t_surat.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["m_karyawan_id","jenis_surat","level_surat","tanggal_terbit","alasan","file_dokumen","is_signed","signature_img","signed_at","creator_id","last_editor_id"];
    public $updateable  = ["m_karyawan_id","jenis_surat","level_surat","tanggal_terbit","alasan","file_dokumen","is_signed","signature_img","signed_at","creator_id","last_editor_id"];
    public $searchable  = ["id","m_karyawan_id","jenis_surat","level_surat","tanggal_terbit","alasan","file_dokumen","is_signed","signature_img","signed_at","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_karyawan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_karyawan_id', 'id');
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
