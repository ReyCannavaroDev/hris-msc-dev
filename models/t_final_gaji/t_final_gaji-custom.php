<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;
Carbon::setLocale('id');

class t_final_gaji extends \App\Models\BasicModels\t_final_gaji
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        /*file_column*/
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $req = app()->request;
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE FINAL GAJI"),
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_tes()
    {
        $det = t_final_gaji_det::with([
            "t_final_gaji_det_rincian" => function ($query) {
                $query->where("t_potongan_id", "!=", null);
            },
        ])
            ->where("t_final_gaji_id", 1)
            ->get();

        return response()->json($det);
    }

    public function custom_postData($request)
    {
        \DB::beginTransaction();
        $data = t_final_gaji::find($request->id);

        if (!$data) {
            return response()->json(["error" => "Data tidak ditemukan."], 404);
        }

        try {
            $update = $data->update([
                "status" => "POSTED",
            ]);
            $create = true;
            $det = t_final_gaji_det::with([
                "t_final_gaji_det_rincian" => function ($query) {
                    $query->where("t_potongan_id", "!=", null);
                },
            ])
                ->where("t_final_gaji_id", $request->id)
                ->get();
            foreach ($det as $dt) {
                foreach ($dt["t_final_gaji_det_rincian"] as $dt1) {
                    $potongan = t_potongan::where(
                        "id",
                        $dt1["t_potongan_id"]
                    )->first();
                    $create =
                        t_potongan_det_bayar::create([
                            "m_potongan_id" => $dt1["t_potongan_id"],
                            "t_final_gaji_id" => $request->id,
                            "percentage" => $potongan["percentage"],
                            "nilai" => $dt1["value"],
                            "paid_at" => \Carbon::now(),
                        ]) && $create;
                }
                // if($create){
                //     $cekAngsuran = t_potongan_det_bayar::where('t_potongan_id')
                // }
            }

            if ($update && $create) {
                \DB::commit();
                return response()->json([
                    "message" => "Data berhasil diposting.",
                ]);
            } else {
                \DB::rollback();
                return response()->json(
                    ["error" => "Gagal memperbarui status."],
                    500
                );
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            // Handle exception, log error messages, etc.
            return response()->json(
                ["error" => "Terjadi kesalahan: " . $e->getMessage()],
                500
            );
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
            $spd = t_final_gaji::find(req("id"));
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

    private function createAppTicket($id)
    {
        $trx = $this->find($id);

        $conf = [
            "app_name" => "APPROVAL FINALISASI GAJI",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Finalisasi Gaji",
            "form_name" => "t_final_gaji",
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
                        "status" => $req->type,
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

    public function public_exportPotongan()
    {
        try {
            $fileName = 'data_potongan_' . Carbon::now()->format('Ymd_His') . '.xlsx';
            $f_id = app()->request?->f_id;
            $is_active = app()->request?->is_active ?? null;

            $headerGaji = t_final_gaji::find($f_id);

            // Pastikan header gaji ditemukan agar tidak error
            if (!$headerGaji) {
                return response()->json(['error' => 'Data Final Gaji tidak ditemukan'], 404);
            }

            $periode_awal  = $headerGaji->periode_awal;
            $periode_akhir = $headerGaji->periode_akhir;

            // Ambil data cuti + relasi yang relevan
            $data = t_final_gaji_det::with(['t_final_gaji_det_rincian' => function($q){
                    $q->where('factor', '-');
                }])
                ->where('t_final_gaji_id', $f_id)
                ->whereHas('m_kary', function($q) use ($is_active, $periode_akhir){
                    //$q->where('is_active', $is_active)
                    $q->when($is_active !== null && $is_active !== '', function ($query) use ($is_active) {
                        return $query->where('is_active', $is_active);
                    })
                    ->whereHas('m_kary_det_kontrak', function($qKontrak) use ($periode_akhir) {
                    $qKontrak->whereDate('tgl_awal', '<=', $periode_akhir);
                    });
                })
                ->get()
                ->map(function ($item) {
                    $kary = m_kary::with(['m_dir', 'm_divisi'])->find($item->m_kary_id) ?? null;
                    return [
                        'ID KARYAWAN' => $kary->kode ?? '',
                        'NAMA_KARYAWAN' => $kary?->nama_lengkap ?? '',
                        'STATUS_KARYAWAN' => ($kary?->is_active) ? 'AKTIF' : 'NONAKTIF',
                        'JABATAN' => $kary?->m_divisi?->nama ?? '',
                        'UNIT' => $kary?->m_dir?->nama ?? '',
                        'PERIODE' => Carbon::parse($item->periode)->translatedFormat('F Y'),
                        'POTONGAN' => number_format($item->t_final_gaji_det_rincian->sum('value'), 0, '.', ',') ?? '0',
                    ];
                });

            $export = new class($data) implements FromCollection, WithHeadings {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array
                {
                    return [
                        'ID KARYAWAN',
                        'NAMA KARYAWAN',
                        'STATUS_KARYAWAN',
                        'JABATAN',
                        'UNIT',
                        'PERIODE',
                        'POTONGAN'
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
