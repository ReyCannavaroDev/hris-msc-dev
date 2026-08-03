<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;

class t_cuti extends \App\Models\BasicModels\t_cuti
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        'attachment'
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function transformRowData( array $row )
    {
        $approval = generate_approval_log::where('trx_table', 't_cuti')->where('trx_id', $row['id']) ->orderBy('created_at', 'desc')->value('action_note');
        return array_merge( $row, [
            'approval_note' => $approval ?? '-'
        ] );
    }

    private function hitungHari($from , $to) {
        $dateFrom = Carbon::parse($from);
        $dateTo = Carbon::parse($to);

        $period = CarbonPeriod::create($dateFrom, $dateTo);
        $tanggalMerah = \DB::table('m_libur_nasional')->pluck('tanggal')->toArray();
        $businessDays = 0;

        foreach ($period as $date) {
            if ($date->dayOfWeek != Carbon::SUNDAY && !in_array($date->format('Y-m-d'), $tanggalMerah)) {
                $businessDays++;
            }
        }
        return $businessDays;
    }

    public function custom_hitungDurasi($req) {
        $from = $req->date_from;
        $to = $req->date_to;
        if(app()->request->header("Source") == "mobile"){
            $dateFrom = Carbon::parse($from);
            $dateTo = Carbon::parse($to);
        }else{
            $dateFrom = Carbon::createFromFormat('d/m/Y', $from);
            $dateTo = Carbon::createFromFormat('d/m/Y', $to);
        }

        $period = CarbonPeriod::create($dateFrom, $dateTo);

        $tanggalMerah = \DB::table('m_libur_nasional')->pluck('tanggal')->toArray();
        $businessDays = 0;

        foreach ($period as $date) {
            if ($date->dayOfWeek != Carbon::SUNDAY && !in_array($date->format('Y-m-d'), $tanggalMerah)) {
                $businessDays++;
            }
        }
        
        return $businessDays;
    }

    private function hitungMenit($from, $to) {
        $dateFrom = Carbon::parse($from);
        $dateTo = Carbon::parse($to);

        // Calculate the difference in minutes for datetime range
        $minutesDifference = $dateFrom->diffInMinutes($dateTo);

        return $minutesDifference;
    }

    public function custom_durationOld($req)
    {
        $m_kary_id = $req->m_kary_id;
        $alasan_id = $req->alasan_id;
        $durasi = $req->total_bulan;
        $tgl_awal = Carbon::parse($req->tgl_awal);
        $tgl_akhir = $tgl_awal->copy();

        if (isset($alasan_id) && isset($m_kary_id) && isset($tgl_awal) && isset($durasi)) {
            $alasan = m_general::find($alasan_id);
            $m_kary = m_kary::find($m_kary_id);

            $check_durasi = $alasan->value_2 ?? 1;

            if (strtolower($alasan->value) == 'menikah' && $durasi >= 12) {
                $check_durasi = $alasan->value_3;
            }

            if (strtolower($alasan->value) == 'melahirkan') {
                $hari_kerja = 0;
                $tgl_akhir = $tgl_awal->copy();

                while ($hari_kerja < 45) {
                    if ($tgl_akhir->dayOfWeek !== Carbon::SUNDAY) {
                        $hari_kerja++;
                    }
                    if ($hari_kerja < 45) {
                        $tgl_akhir->addDay();
                    }
                }
            } else {
                // if ($check_durasi > 1) {
                //     $tgl_akhir = $tgl_awal->copy()->addDays($check_durasi - 1);
                // } else {
                //     $tgl_akhir = $tgl_awal->copy();
                // }

                $hari_kerja = 1;
                $tgl_akhir = $tgl_awal->copy();

                while ($hari_kerja < $check_durasi) {
                    $tgl_akhir->addDay();
                    if ($tgl_akhir->dayOfWeek !== Carbon::SUNDAY) {
                        $hari_kerja++;
                    }
                }
            }
        }

        if(isset($req->mobile)){
            if($req->mobile == true){
                return response()->json([
                    'tgl_akhir' => $tgl_akhir->format('Y-m-d'),
                ]);
            }
        }
        
        return $tgl_akhir->format('Y-m-d');
    }

    public function custom_duration($req)
    {
        $m_kary_id = $req->m_kary_id;
        $alasan_id = $req->alasan_id;
        $durasi = $req->total_bulan;
        $tgl_awal = Carbon::parse($req->tgl_awal);
        $tgl_akhir = $tgl_awal->copy();

        if (isset($alasan_id) && isset($m_kary_id) && isset($tgl_awal) && isset($durasi)) {
            $alasan = m_general::find($alasan_id);
            
            $check_durasi = $alasan->value_2 ?? 1;

            if (strtolower($alasan->value) == 'menikah' && $durasi >= 12) {
                $check_durasi = $alasan->value_3;
            }

            if (strtolower($alasan->value) == 'melahirkan') {
                $tgl_akhir = $tgl_awal->copy()->addDays(44); 
            } else {
                $tgl_akhir = $tgl_awal->copy()->addDays($check_durasi - 1);
            }
        }

        if (isset($req->mobile) && $req->mobile == true) {
            return response()->json([
                'tgl_akhir' => $tgl_akhir->format('Y-m-d'),
            ]);
        }

        return $tgl_akhir->format('Y-m-d');
    }


    public function createBefore($model, $arrayData, $metaData, $id = null)
    {   
        if(!isset($arrayData['m_kary_id'])){
            return $this->helper->customResponse(
                "Akun ini tidak Tersambung dengan data karyawan manapun !",
                422
            );
        }

        $interval = null;
        $interval_min = null;

        if (isset($arrayData['date_from']) && isset($arrayData['date_to']) && (!isset($arrayData['time_from']) || $arrayData['time_from'] === null || $arrayData['time_from'] === '') && (!isset($arrayData['time_to']) || $arrayData['time_to'] === null || $arrayData['time_to'] === '')) {
            $interval = @$this->hitungHari($arrayData['date_from'], $arrayData['date_to']) ?? 1;
        }

        if (isset($arrayData['time_from']) && isset($arrayData['time_to'])) {
            $interval_min = @$this->hitungMenit($arrayData['time_from'], $arrayData['time_to']) ?? 1;
        }

        $alasan = m_general::find($arrayData['alasan_id'])->value ?? null;

        if ($alasan && str_contains(strtolower($alasan), 'dispensasi')) {     
            $tanggalPengajuan = Carbon::parse($arrayData['date_to']);
            $sekarang = Carbon::now();

            $isBulanLalu = $tanggalPengajuan->month == $sekarang->copy()->subMonthNoOverflow()->month && 
            $tanggalPengajuan->year == $sekarang->copy()->subMonthNoOverflow()->year;

            if ($isBulanLalu) {
                if ($sekarang->day > 5) {
                    return [
                        //'status' => 'error',
                        //"model" => $model,
                        'errors' => 'Batas pengajuan dispensasi bulan lalu maksimal tanggal 5 bulan ini.'
                    ];
                    //abort(422, 'Batas pengajuan dispensasi bulan lalu maksimal tanggal 5 bulan ini.');
                }
            }
            
            if ($tanggalPengajuan->lt($sekarang->copy()->startOfMonth()->subMonth())) {
                return [
                    //'status' => 'error',
                    //"model" => $model,
                    'errors' => 'Tidak diperbolehkan mengajukan dispensasi lebih dari bulan lalu.'
                ];
            }        
        }
        
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE CUTI"),
            "interval" => $interval,
            "interval_min" => $interval_min
        ]);

        if (app()->request->header("Source") == "mobile") {
            $newArrayData = array_merge($newArrayData, [
                "status" => "IN APPROVAL",
                "interval" => @$interval,
                "interval_min" => @$interval_min
            ]);
        }

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {

        $interval = null;
        $interval_min = null;

        if (isset($arrayData['date_from']) && isset($arrayData['date_to']) && (!isset($arrayData['time_from']) || $arrayData['time_from'] === null || $arrayData['time_from'] === '') && (!isset($arrayData['time_to']) || $arrayData['time_to'] === null || $arrayData['time_to'] === '')) {
            $interval = @$this->hitungHari($arrayData['date_from'], $arrayData['date_to']) ?? 1;
        }

        if (isset($arrayData['time_from']) && isset($arrayData['time_to'])) {
            $interval_min = @$this->hitungMenit($arrayData['time_from'], $arrayData['time_to']) ?? 1;
        }

        if (app()->request->header("Source") == "mobile") {
            $data = t_cuti::where('id', $id)->first();
            if($data["status"] === 'REVISED'){
                $status = 'IN APPROVAL';
            }
        }
        $newArrayData  = array_merge( $arrayData,[
            'status' => @$status ?? @$arrayData['status'],
            "interval" =>@ $interval ?? @$arrayData['interval'],
            "interval_min" => @$interval_min ?? @$arrayData['interval_min'] 
        ]);


        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        if (app()->request->header("Source") == "mobile") {
            $app = $this->createAppTicket($model->id);
        }
    }

    public function updateAfterTransaction( $newdata, $olddata, $data, $meta )
    {
        if (app()->request->header("Source") == "mobile") {
            $app = $this->createAppTicket($newdata['id']);  
        }
    }

    public function custom_posted($req)
    {
        \DB::beginTransaction();
        try{
            $data = t_cuti::find($req->id);
            $data->status = 'POSTED';
            $data->save();

         \DB::commit();
         return $this->helper->customResponse("Data berhasil diposting");
        }catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_send_approval()
    {
        $app = $this->createAppTicket(req("id"));
        if (!$app) {
            return $this->helper->customResponse(
                "Terjadi kesalahan, coba kembali nanti",
                400
            );
        }

        if (app()->request->header("Source") != "mobile") {
            $spd = t_cuti::find(req("id"));
            if ($spd) {
                $spd->update([
                    "status" => "IN APPROVAL",
                ]);
            }
        }

        return $this->helper->customResponse(
            "Permintaan approval berhasil dibuat"
        );
    }

    public function public_tes($req)
    {
        $id = "4";
        $data = t_cuti::where('id', (int)$id)->first();
        return $data;
    }

    // public function updateAfter( $model, $arrayData, $metaData, $id=null )
    // {
    //     if (app()->request->header("Source") == "mobile") {
    //         $data = t_cuti::where('id', 4)->first();
    //         if(@$data) { 
    //             dd($data);
    //             }
    //         if(@$data->status === 'REVISED'){
    //             $status = 'IN APPROVAL';
    //             $app = $this->createAppTicket($id);
    //              if (!$app) {
    //                 return $this->helper->customResponse(
    //                     "Approval tidak tersedia untuk atribut user anda",
    //                     400
    //                 );
    //             }
    //         }
    //     }
    //     $newArrayData = $arrayData;
    //     if(@$status){
    //         $newArrayData  = array_merge( $newArrayData,[
    //             'status' => $status 
    //         ]);
    //     }

    //     return [
    //         "model"  => $model,
    //         "data"   => $newArrayData,
    //         // "errors" => ['error1']
    //     ];
    // }
    

    private function createAppTicket($id)
    {
        $trx = $this->find($id);

        $conf = [
            "app_name" => "APPROVAL IJIN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Ijin",
            "form_name" => "t_cuti",
            "trx_nomor" => $trx->nomor,
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id,
        ];

        $app = $this->helper->approvalCreateTicket($conf);
        if ($app) {
            return true;
        } else {
            return false;
        }
    }

    // public function custom_post ($request) {
    //     $data = t_cuti::find($request->$id);
    //     if (!$data) {
    //         return response()->json(['message' => 'Data not found'], 404);
    //     }
    //     if ($data->status === 'DRAFT') {
    //         // Change the status to post
    //         $data->update([
    //             "status" => "POSTED"
    //             ]);
    //         // $data->status = 'POSTED';
    //         // $data->save();
    //         return response()->json(['message' => 'DRAFT status changed to "POSTED"']);
    //     } else {
    //         // If the status is not draft, return a message
    //         return response()->json(['message' => 'POSTED status is not "DRAFT"'], 400);
    //     }
    // }

    

    public function custom_progress($req)
    {
        // Start a database transaction
        \DB::beginTransaction();

        try {
            $conf = [
                "app_id" => $req->id,
                "app_type" => $req->type, // APPROVED, REVISED, REJECTED,
                "app_note" => $req->note, // alasan approve
            ];

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    $data->update([
                        "status" => $req->type
                    ]);
                   
                } else {
                    $data->update([
                        "status" => "IN APPROVAL",
                    ]);
                }
            }

            \DB::commit();

            return $this->helper->customResponse("Proses approval berhasil");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }


    public function custom_detail($req)
    {
        $id = $req->id ?? 66;
        $data = $this->helper->approvalDetail($id);
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_log($req)
    {
        $conf = [
            "trx_id" => $req->id ?? 0,
            "trx_table" => $this->getTable(),
        ];
        $data = $this->helper->approvalLog($conf);
        return response($data);
    }

    public function public_exportCuti()
    {
        try {
            $fileName = 'data_cuti_' . Carbon::now()->format('Ymd_His') . '.xlsx';

            // Ambil data cuti + relasi yang relevan
            $data = t_cuti::with([
                    'm_kary.m_divisi',
                    'm_dir',
                    'alasan' => function ($q) { $q->select('id', 'value'); },
                ])
                ->get()
                ->map(function ($cuti) {
                    return [
                        'NOMOR' => $cuti->nomor ?? '',
                        'NAMA_KARYAWAN' => $cuti->m_kary?->nama_lengkap ?? '',
                        'JABATAN' => $cuti->m_kary?->m_divisi?->nama ?? '',
                        'UNIT' => $cuti->m_kary?->m_dir?->nama ?? '',
                        'ALASAN' => $cuti->alasan?->value ?? '',
                        'TANGGAL_DARI' => $cuti->date_from ? Carbon::parse($cuti->date_from)->format('Y-m-d') : '',
                        'TANGGAL_SAMPAI' => $cuti->date_to ? Carbon::parse($cuti->date_to)->format('Y-m-d') : '',
                        'KETERANGAN' => $cuti->keterangan ?? '',
                        'STATUS' => $cuti->status ?? '',
                        'DIBUAT_OLEH' => $cuti->creator?->name ?? '-',
                        'DIBUAT_PADA' => $cuti->created_at ? $cuti->created_at->format('Y-m-d H:i:s') : '',
                    ];
                });

            $export = new class($data) implements FromCollection, WithHeadings {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array
                {
                    return [
                        'NOMOR',
                        'NAMA KARYAWAN',
                        'JABATAN',
                        'UNIT',
                        'TIPE CUTI',
                        'TANGGAL DARI',
                        'TANGGAL SAMPAI',
                        'KETERANGAN',
                        'STATUS',
                        'DIBUAT OLEH',
                        'DIBUAT PADA',
                    ];
                }
            };

            return Excel::download($export, $fileName, ExcelType::XLSX);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat export: ' . $e->getMessage(),
            ], 500);
        }
    }
}
