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

    public function custom_laporan_dispensasi($req)
    {
        try {
            $start = null;
            $end = null;

            // Flexible Period handling: Date Range or Month
            if ($req->date_start && $req->date_end) {
                $start = Carbon::parse($req->date_start)->format('Y-m-d');
                $end = Carbon::parse($req->date_end)->format('Y-m-d');
            } elseif ($req->date_start) {
                $start = Carbon::parse($req->date_start)->format('Y-m-d');
                $end = Carbon::parse($req->date_start)->format('Y-m-d');
            } else {
                $month = $req->month ?? $req->periode ?? Carbon::now()->format('Y-m');
                $start = Carbon::parse($month . '-01')->startOfMonth()->format('Y-m-d');
                $end = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');
            }

            $query = t_cuti::with([
                'm_kary.m_divisi',
                'm_dir',
                'alasan' => function ($q) { $q->select('id', 'value'); },
                'creator' => function ($q) { $q->select('id', 'name'); },
            ])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('date_from', [$start, $end])
                  ->orWhereBetween('date_to', [$start, $end])
                  ->orWhere(function ($sub) use ($start, $end) {
                      $sub->where('date_from', '<=', $start)
                          ->where('date_to', '>=', $end);
                  });
            })
            ->whereHas('alasan', function ($q) {
                $q->whereRaw('LOWER(value) LIKE ?', ['%dispensasi%']);
            });

            if ($req->m_dir_id) {
                $query->where('m_dir_id', $req->m_dir_id);
            }
            if ($req->m_divisi_id) {
                $query->whereHas('m_kary', function ($kQ) use ($req) {
                    $kQ->where('m_divisi_id', $req->m_divisi_id);
                });
            }
            if ($req->m_kary_id) {
                if (is_array($req->m_kary_id)) {
                    $query->whereIn('m_kary_id', $req->m_kary_id);
                } elseif (strpos($req->m_kary_id, ',') !== false) {
                    $query->whereIn('m_kary_id', explode(',', $req->m_kary_id));
                } else {
                    $query->where('m_kary_id', $req->m_kary_id);
                }
            }
            if ($req->status && $req->status !== 'Semua') {
                $query->where('status', $req->status);
            }

            // Filter jenis dispensasi (in, out, in & out)
            if ($req->jenis_dispensasi && $req->jenis_dispensasi !== 'Semua') {
                if ($req->jenis_dispensasi === 'Lupa In') {
                    $query->whereNotNull('time_from')->whereNull('time_to');
                } elseif ($req->jenis_dispensasi === 'Lupa Out') {
                    $query->whereNull('time_from')->whereNotNull('time_to');
                } elseif ($req->jenis_dispensasi === 'Lupa In & Out') {
                    $query->whereNotNull('time_from')->whereNotNull('time_to');
                }
            }

            $data = $query->orderBy('date_from', 'desc')->get();

            // Ambil log approval note jika ada
            $trxIds = $data->pluck('id')->toArray();
            $approvalLogs = [];
            if (!empty($trxIds)) {
                $logs = \DB::table('generate_approval_log')
                    ->where('trx_table', 't_cuti')
                    ->whereIn('trx_id', $trxIds)
                    ->orderBy('created_at', 'desc')
                    ->get();
                foreach ($logs as $l) {
                    if (!isset($approvalLogs[$l->trx_id])) {
                        $approvalLogs[$l->trx_id] = $l->action_note ?? $l->note ?? '-';
                    }
                }
            }

            $rows = $data->map(function ($cuti) use ($approvalLogs) {
                $jenis = 'Lupa Absen';
                if ($cuti->time_from && $cuti->time_to) {
                    $jenis = 'Lupa In & Out';
                } elseif ($cuti->time_from) {
                    $jenis = 'Lupa In';
                } elseif ($cuti->time_to) {
                    $jenis = 'Lupa Out';
                }

                $tgl = Carbon::parse($cuti->date_from)->format('d-m-Y');
                if ($cuti->date_to && $cuti->date_to !== $cuti->date_from) {
                    $tgl .= ' s/d ' . Carbon::parse($cuti->date_to)->format('d-m-Y');
                }

                return [
                    'id' => $cuti->id,
                    'nomor' => $cuti->nomor ?? '-',
                    'nik' => $cuti->m_kary?->kode ?? '-',
                    'nama' => $cuti->m_kary?->nama_lengkap ?? '-',
                    'unit' => $cuti->m_dir?->nama ?? $cuti->m_kary?->m_dir?->nama ?? '-',
                    'jabatan' => $cuti->m_kary?->m_divisi?->nama ?? '-',
                    'tanggal' => $tgl,
                    'time_from' => $cuti->time_from ? substr($cuti->time_from, 0, 5) : '-',
                    'time_to' => $cuti->time_to ? substr($cuti->time_to, 0, 5) : '-',
                    'jenis_dispensasi' => $jenis,
                    'keterangan' => $cuti->keterangan ?? '-',
                    'status' => $cuti->status ?? 'DRAFT',
                    'catatan_approval' => $approvalLogs[$cuti->id] ?? '-',
                    'dibuat_pada' => $cuti->created_at ? Carbon::parse($cuti->created_at)->format('d-m-Y H:i') : '-'
                ];
            });

            return $this->helper->customResponse('OK', 200, $rows);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function public_exportDispensasi()
    {
        try {
            $req = request();
            $start = null;
            $end = null;

            if ($req->date_start && $req->date_end) {
                $start = Carbon::parse($req->date_start)->format('Y-m-d');
                $end = Carbon::parse($req->date_end)->format('Y-m-d');
                $filenamePeriod = $start . '_sd_' . $end;
            } elseif ($req->date_start) {
                $start = Carbon::parse($req->date_start)->format('Y-m-d');
                $end = Carbon::parse($req->date_start)->format('Y-m-d');
                $filenamePeriod = $start;
            } else {
                $month = $req->month ?? $req->periode ?? Carbon::now()->format('Y-m');
                $start = Carbon::parse($month . '-01')->startOfMonth()->format('Y-m-d');
                $end = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');
                $filenamePeriod = $month;
            }

            $query = t_cuti::with([
                'm_kary.m_divisi',
                'm_dir',
                'alasan' => function ($q) { $q->select('id', 'value'); },
                'creator' => function ($q) { $q->select('id', 'name'); },
            ])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('date_from', [$start, $end])
                  ->orWhereBetween('date_to', [$start, $end])
                  ->orWhere(function ($sub) use ($start, $end) {
                      $sub->where('date_from', '<=', $start)
                          ->where('date_to', '>=', $end);
                  });
            })
            ->whereHas('alasan', function ($q) {
                $q->whereRaw('LOWER(value) LIKE ?', ['%dispensasi%']);
            });

            if ($req->m_dir_id) {
                $query->where('m_dir_id', $req->m_dir_id);
            }
            if ($req->m_divisi_id) {
                $query->whereHas('m_kary', function ($kQ) use ($req) {
                    $kQ->where('m_divisi_id', $req->m_divisi_id);
                });
            }
            if ($req->m_kary_id) {
                if (is_array($req->m_kary_id)) {
                    $query->whereIn('m_kary_id', $req->m_kary_id);
                } elseif (strpos($req->m_kary_id, ',') !== false) {
                    $query->whereIn('m_kary_id', explode(',', $req->m_kary_id));
                } else {
                    $query->where('m_kary_id', $req->m_kary_id);
                }
            }
            if ($req->status && $req->status !== 'Semua') {
                $query->where('status', $req->status);
            }

            if ($req->jenis_dispensasi && $req->jenis_dispensasi !== 'Semua') {
                if ($req->jenis_dispensasi === 'Lupa In') {
                    $query->whereNotNull('time_from')->whereNull('time_to');
                } elseif ($req->jenis_dispensasi === 'Lupa Out') {
                    $query->whereNull('time_from')->whereNotNull('time_to');
                } elseif ($req->jenis_dispensasi === 'Lupa In & Out') {
                    $query->whereNotNull('time_from')->whereNotNull('time_to');
                }
            }

            $data = $query->orderBy('date_from', 'desc')->get();

            $trxIds = $data->pluck('id')->toArray();
            $approvalLogs = [];
            if (!empty($trxIds)) {
                $logs = \DB::table('generate_approval_log')
                    ->where('trx_table', 't_cuti')
                    ->whereIn('trx_id', $trxIds)
                    ->orderBy('created_at', 'desc')
                    ->get();
                foreach ($logs as $l) {
                    if (!isset($approvalLogs[$l->trx_id])) {
                        $approvalLogs[$l->trx_id] = $l->action_note ?? $l->note ?? '-';
                    }
                }
            }

            $rows = $data->map(function ($cuti) use ($approvalLogs) {
                $jenis = 'Lupa Absen';
                if ($cuti->time_from && $cuti->time_to) {
                    $jenis = 'Lupa In & Out';
                } elseif ($cuti->time_from) {
                    $jenis = 'Lupa In';
                } elseif ($cuti->time_to) {
                    $jenis = 'Lupa Out';
                }

                $tgl = Carbon::parse($cuti->date_from)->format('d-m-Y');
                if ($cuti->date_to && $cuti->date_to !== $cuti->date_from) {
                    $tgl .= ' s/d ' . Carbon::parse($cuti->date_to)->format('d-m-Y');
                }

                return [
                    'NOMOR' => $cuti->nomor ?? '-',
                    'NIK' => $cuti->m_kary?->kode ?? '-',
                    'NAMA KARYAWAN' => $cuti->m_kary?->nama_lengkap ?? '-',
                    'UNIT' => $cuti->m_dir?->nama ?? $cuti->m_kary?->m_dir?->nama ?? '-',
                    'JABATAN' => $cuti->m_kary?->m_divisi?->nama ?? '-',
                    'TANGGAL' => $tgl,
                    'JAM IN' => $cuti->time_from ? substr($cuti->time_from, 0, 5) : '-',
                    'JAM OUT' => $cuti->time_to ? substr($cuti->time_to, 0, 5) : '-',
                    'JENIS DISPENSASI' => $jenis,
                    'KETERANGAN' => $cuti->keterangan ?? '-',
                    'STATUS' => $cuti->status ?? 'DRAFT',
                    'CATATAN APPROVAL' => $approvalLogs[$cuti->id] ?? '-',
                    'DIBUAT PADA' => $cuti->created_at ? Carbon::parse($cuti->created_at)->format('d-m-Y H:i') : '-'
                ];
            });

            $export = new class($rows) implements FromCollection, WithHeadings {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array
                {
                    return [
                        'NOMOR', 'NIK', 'NAMA KARYAWAN', 'UNIT', 'JABATAN',
                        'TANGGAL', 'JAM IN', 'JAM OUT', 'JENIS DISPENSASI',
                        'KETERANGAN', 'STATUS', 'CATATAN APPROVAL', 'DIBUAT PADA'
                    ];
                }
            };

            return Excel::download($export, "laporan_dispensasi_{$filenamePeriod}.xlsx");
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}