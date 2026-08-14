<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;

class t_extend_kontrak extends \App\Models\BasicModels\t_extend_kontrak
{
    private $helper;
    public function __construct()
    {
        $this->helper = getCore("Helper");
        parent::__construct();
    }

    public $fileColumns = ["contract_template", "contract_signed"];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE KONTRAK"),
        ]);
        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_complete($req)
    {
        \DB::beginTransaction();
        try {
            $extend = $this->find($req->id);

            if (!$extend) {
                return $this->helper->customResponse(
                    "Data tidak ditemukan",
                    422
                );
            }

            $extend->update([
                "status" => "COMPLETED",
            ]);

            $old_contract = m_kary_det_kontrak::find(
                $extend->m_kary_det_kontrak_id
            );
            $old_contract->update([
                "status" => false,
            ]);

            // $new_contract = m_kary_det_kontrak::create([
            //     "m_karyawan_id" => $extend->m_karyawan_id,
            //     "m_divisi_id" => $extend->m_divisi_id,
            //     "m_dir_id" => $extend->m_dir_id,
            //     "tipe_karyawan_id" => $extend->tipe_karyawan_id,
            //     "tgl_awal" => $extend->tgl_awal,
            //     "tgl_akhir" => $extend->tgl_akhir,
            //     "duration" => $extend->duration,
            //     "contract" => $extend->contract_signed,
            //     "status" => true,
            // ]);
            
            $new_contract = \DB::table("m_kary_det_kontrak")->insert([
                "nomor" => $extend->nomor,
                "m_karyawan_id" => $extend->m_karyawan_id,
                "m_divisi_id" => $extend->m_divisi_id,
                "m_dir_id" => $extend->m_dir_id,
                "tipe_karyawan_id" => $extend->tipe_karyawan_id,
                "tgl_awal" => $extend->tgl_awal,
                "tgl_akhir" => $extend->tgl_akhir,
                "duration" => $extend->duration,
                "contract" => $extend->contract_signed,
                "status" => true,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            $m_kary = m_kary::find($extend->m_karyawan_id);
            $m_kary->update([
                "m_divisi_id" => $extend->m_divisi_id,
                "m_dir_id" => $extend->m_dir_id,
                "tipe_karyawan_id" => $extend->tipe_karyawan_id,
            ]);

            \DB::commit();

            return $this->helper->customResponse("Proses perpanjangan selesai");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_posted($req)
    {
        \DB::beginTransaction();
        try {
            $data = $this->find($req->id);
            if (!$data) {
                return $this->helper->customResponse(
                    "Data tidak ditemukan",
                    422
                );
            }
            $data->status = "POSTED";
            $data->save();

            \DB::commit();
            return $this->helper->customResponse("Data berhasil diposting");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function public_exportExtendKontrak()
    {
        try {
            $fileName =
                "data_extend_kontrak_" .
                Carbon::now()->format("Ymd_His") .
                ".xlsx";

            // Ambil data extend kontrak + relasi yang relevan
            $data = t_extend_kontrak::with([
                "m_karyawan.m_divisi",
                "m_dir",
                "tipe_karyawan" => function ($q) {
                    $q->select("id", "value");
                },
            ])
                ->get()
                ->map(function ($extend) {
                    return [
                        "NAMA_KARYAWAN" =>
                            $extend->m_karyawan?->nama_lengkap ?? "",
                        "DIVISI" => $extend->m_karyawan?->m_divisi?->nama ?? "",
                        "UNIT" => $extend->m_dir?->nama ?? "",
                        "TIPE_KARYAWAN" => $extend->tipe_karyawan?->value ?? "",
                        "TANGGAL_MULAI" => $extend->tgl_awal
                            ? Carbon::parse($extend->tgl_awal)->format("d-m-Y")
                            : "",
                        "TANGGAL_SELESAI" => $extend->tgl_akhir
                            ? Carbon::parse($extend->tgl_akhir)->format("d-m-Y")
                            : "",
                        "DURASI_BULAN" => $extend->duration ?? 0,
                        "TEMPLATE_KONTRAK" => $extend->contract_template ?? "",
                        "KONTRAK_TTD" => $extend->contract_signed ?? "",
                        "STATUS" => $extend->status ?? "",
                        "DIBUAT_PADA" => $extend->created_at
                            ? $extend->created_at->format("Y-m-d H:i:s")
                            : "",
                    ];
                });

            // Buat export dinamis tanpa file terpisah
            $export = new class ($data) implements FromCollection, WithHeadings
            {
                protected $data;
                public function __construct($data)
                {
                    $this->data = $data;
                }
                public function collection()
                {
                    return $this->data;
                }
                public function headings(): array
                {
                    return [
                        "NAMA KARYAWAN",
                        "DIVISI",
                        "UNIT",
                        "TIPE KARYAWAN",
                        "TANGGAL MULAI",
                        "TANGGAL SELESAI",
                        "DURASI (BULAN)",
                        "TEMPLATE KONTRAK",
                        "KONTRAK TTD",
                        "STATUS",
                        "DIBUAT PADA",
                    ];
                }
            };

            return Excel::download($export, $fileName, ExcelType::XLSX);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "error" =>
                        "Terjadi kesalahan saat export: " . $e->getMessage(),
                ],
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
            $spd = t_extend_kontrak::find(req("id"));
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
            "app_name" => "APPROVAL PERPANJANGAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Perpanjangan",
            "form_name" => "m_kary_kontrak",
            "trx_nomor" => $trx->nomor,
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => auth()->user()->id,
            "trx_m_dir_id" => @$trx->m_dir_id ?? null,
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

            $user_id = auth()->user()->id;
            $approver = m_kary::whereHas('default_users', function($q) use ($user_id){
                $q->where('id', $user_id);
            })->first() ?? null;

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if($req->contract_signed){
                    $data->update([
                        "contract_signed" => $req->contract_signed
                    ]);
                }

                if($data->m_dir_id != $approver?->m_dir_id){
                     return $this->helper->customResponse(
                        "Anda tidak memiliki hak akses untuk approval ini",
                        400
                    );
                }

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
}