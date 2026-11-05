<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mKaryDetKontrakTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_kary_det_kontrak', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        ff( $responseArr, 'dump data' );

        $this->assertTrue(true);
    }

    public function testCreatingData()
    {
        $user = User::where('username', 'USERNAME')->first();
        $this->assertNotEmpty( $user );
        
        Passport::actingAs($user);

        $payload = [
		    "id" => "bigint:optional:autocreate",
		    "m_karyawan_id" => "bigint:optional",
		    "m_divisi_id" => "bigint:optional",
		    "m_dir_id" => "bigint:optional",
		    "tipe_karyawan_id" => "integer:optional",
		    "status" => "boolean:optional",
		    "tgl_awal" => "date:optional",
		    "tgl_akhir" => "date:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "duration" => "integer:optional",
		    "contract" => "string:191:optional",
		    "nomor" => "string:optional"
		];

        $this->call('POST', '/operation/m_kary_det_kontrak', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_kary_det_kontrak', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}