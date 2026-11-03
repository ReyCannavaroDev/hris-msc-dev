<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tResignTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_resign', $payload);

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
		    "nomor" => "string:50:optional",
		    "m_kary_id" => "bigint:optional",
		    "m_dir_id" => "bigint:optional",
		    "m_divisi_id" => "bigint:optional",
		    "tipe_resign_id" => "bigint:optional",
		    "date" => "date:required",
		    "keterangan" => "text:optional",
		    "status" => "string:50:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "surat_resign" => "string:191:optional",
		    "surat_prakerin" => "string:191:optional"
		];

        $this->call('POST', '/operation/t_resign', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_resign', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}