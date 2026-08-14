<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class textendkontrak extends Migration
{
    protected $tableName = "t_extend_kontrak";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
             $table->id()->from(1);

            $table->bigInteger('m_karyawan_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->bigInteger('m_divisi_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('m_dir_id')->comment('{"src":"m_dir.id"}')->nullable();
            $table->integer('tipe_karyawan_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->string('status')->nullable()->default('DRAFT');
            $table->date('tgl_awal')->nullable();
            $table->date('tgl_akhir')->nullable();
            $table->integer('duration')->nullable()->default(3);
            $table->string('contract_template')->nullable();
            $table->string('contract_signed')->nullable();
            $table->bigInteger('m_kary_det_kontrak_id')->comment('{"src":"m_kary_det_kontrak.id"}')->nullable();
            $table->string('nomor')->nullable();
            $table->string('catatan')->nullable();
            //$table->bigInteger('creator_id')->comment('{"src":"default_users.id"}')->nullable();
            //$table->bigInteger('last_editor_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->timestamps();
        });

        table_config($this->tableName, [
            "guarded"       => ["id"],
            "required"      => [],
            "!createable"   => ["id","created_at","updated_at"],
            "!updateable"   => ["id","created_at","updated_at"],
            "searchable"    => "all",
            "deleteable"    => "true",
            "deleteOnUse"   => "false",
            "extendable"    => "false",
            "casts"     => [
                'created_at' => 'datetime:d/m/Y H:i',
                'updated_at' => 'datetime:d/m/Y H:i'
            ]
        ]);

        // if( $data = \Cache::pull($this->tableName) ){
        //     $fixedData = json_decode( json_encode( $data ), true );
        //     \DB::table($this->tableName)->insert( $fixedData );
        // }
    }
    public function down()
    {
        // if( Schema::hasTable($this->tableName) ){
        //     \Cache::put($this->tableName, \DB::table($this->tableName)->get(), 60*30 );
        // }
        Schema::dropIfExists($this->tableName);
    }
}