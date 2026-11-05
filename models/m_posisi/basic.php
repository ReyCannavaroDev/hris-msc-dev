<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_posisi extends Model
{   
    use ModelTrait;

    protected $table    = 'm_posisi';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_comp_id","m_dir_id","kode","desc_kerja","desc_kerja_1","desc_kerja_2","min_pengalaman","min_pendidikan_id","min_gaji_pokok","max_gaji_pokok","biaya","tipe_bpjs_id","potongan_bpjs","desc","is_active","creator_id","last_editor_id","created_at","updated_at","is_head"];

    public $columns     = ["id","m_comp_id","m_dir_id","kode","desc_kerja","desc_kerja_1","desc_kerja_2","min_pengalaman","min_pendidikan_id","min_gaji_pokok","max_gaji_pokok","biaya","tipe_bpjs_id","potongan_bpjs","desc","is_active","creator_id","last_editor_id","created_at","updated_at","is_head"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","m_dir_id:bigint","kode:string:50","desc_kerja:text","desc_kerja_1:text","desc_kerja_2:text","min_pengalaman:string:191","min_pendidikan_id:bigint","min_gaji_pokok:decimal","max_gaji_pokok:decimal","biaya:decimal","tipe_bpjs_id:bigint","potongan_bpjs:decimal","desc:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","is_head:boolean"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = ["m_spd","m_standart_gaji","m_tunj_kemahalan","t_mutasi","t_mutasi","t_pelamar","t_spd","m_kary"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["desc_kerja","is_active"];
    public $createable  = ["m_comp_id","m_dir_id","kode","desc_kerja","desc_kerja_1","desc_kerja_2","min_pengalaman","min_pendidikan_id","min_gaji_pokok","max_gaji_pokok","biaya","tipe_bpjs_id","potongan_bpjs","desc","is_active","creator_id","last_editor_id","created_at","updated_at","is_head"];
    public $updateable  = ["m_comp_id","m_dir_id","kode","desc_kerja","desc_kerja_1","desc_kerja_2","min_pengalaman","min_pendidikan_id","min_gaji_pokok","max_gaji_pokok","biaya","tipe_bpjs_id","potongan_bpjs","desc","is_active","creator_id","last_editor_id","created_at","updated_at","is_head"];
    public $searchable  = ["m_comp_id","m_dir_id","kode","desc_kerja","desc_kerja_1","desc_kerja_2","min_pengalaman","min_pendidikan_id","min_gaji_pokok","max_gaji_pokok","biaya","tipe_bpjs_id","potongan_bpjs","desc","is_active","creator_id","last_editor_id","created_at","updated_at","is_head"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
