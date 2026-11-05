<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tsurat extends Migration
{
    protected $tableName = "t_surat";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);

            $table->bigInteger('m_karyawan_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->string('jenis_surat', 100)->nullable(); // PERINGATAN, PENGHARGAAN, SKORSING, dll
            $table->string('level_surat', 50)->nullable(); // SP 1, SP 2, dst (bisa kosong)
            $table->date('tanggal_terbit')->nullable();
            $table->text('alasan')->nullable();
            $table->string('file_dokumen', 255)->nullable();
            
            // Kolom Persetujuan (Digital Signature)
            $table->boolean('is_signed')->default(false)->nullable();
            $table->longText('signature_img')->nullable();
            $table->dateTime('signed_at')->nullable();

            $table->bigInteger('creator_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->bigInteger('last_editor_id')->comment('{"src":"default_users.id"}')->nullable();
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