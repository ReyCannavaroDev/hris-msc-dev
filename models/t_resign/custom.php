<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;
use Illuminate\Support\Collection;

class t_resign extends \App\Models\BasicModels\t_resign
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        'surat_resign',
        'surat_prakerin'
    ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];
    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        // $m_kary = m_kary::find($arrayData["m_kary_id"]);
        // if($m_kary->is_active === false){
        //     return ["message" => "Karyawan sudah tidak aktif"];
        // }

        $newArrayData = array_merge($arrayData, [
            "nomor" =>
                @$arrayData["nomor"] ??
                $this->helper->generateNomor("KODE RESIGN"),
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $this->updateAfter($model, $arrayData, $metaData, $id);
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        $status = $model->status ?? @$arrayData['status'];
        if ($status === 'POSTED') {
            $karyId = $model->m_kary_id ?? @$arrayData['m_kary_id'];
            if ($karyId) {
                $kary = m_kary::find($karyId);
                if ($kary) {
                    $alasan = $model->keterangan ?? @$arrayData['keterangan'] ?? "Pengunduran Diri";
                    $tipeResignId = $model->tipe_resign_id ?? @$arrayData['tipe_resign_id'];
                    if ($tipeResignId) {
                        $tipeResign = \App\Models\BasicModels\m_general::where('id', $tipeResignId)->value('value');
                        if ($tipeResign) {
                            $alasan = $tipeResign . ($alasan ? ' - ' . $alasan : '');
                        }
                    }

                    $kary->update([
                        "is_active" => false,
                        "tgl_berhenti" => $model->date ?? @$arrayData['date'] ?? Carbon::now()->format('Y-m-d'),
                        "alasan_berhenti" => $alasan,
                    ]);

                    $user = default_users::where('m_kary_id', $kary->id)->first();
                    if ($user) {
                        $user->update(["is_active" => false]);
                    }
                }

                $kontrak = m_kary_det_kontrak::where("m_karyawan_id", $karyId)
                    ->where("status", true)
                    ->first();
                if ($kontrak) {
                    $kontrak->update(["status" => false]);
                }
            }
        }
    }

    public function custom_post($request)
    {
        \DB::beginTransaction();
        try {
            $data = t_resign::find($request->id);
            if (!$data) {
                return response()->json(["message" => "Data tidak ditemukan"], 404);
            }

            if (in_array($data->status, ["DRAFT", "POSTED"])) {
                if ($data->status === "DRAFT") {
                    $data->update([
                        "status" => "POSTED",
                    ]);
                }

                $kary = m_kary::find($data->m_kary_id);
                if ($kary) {
                    $alasan = $data->keterangan ?? "Pengunduran Diri";
                    if ($data->tipe_resign_id) {
                        $tipeResign = \App\Models\BasicModels\m_general::where('id', $data->tipe_resign_id)->value('value');
                        if ($tipeResign) {
                            $alasan = $tipeResign . ($data->keterangan ? ' - ' . $data->keterangan : '');
                        }
                    }

                    $kary->update([
                        "is_active" => false,
                        "tgl_berhenti" => $data->date ?? Carbon::now()->format('Y-m-d'),
                        "alasan_berhenti" => $alasan,
                    ]);

                    // Nonaktifkan user login di default_users jika ada (aman bila user null)
                    $user = default_users::where('m_kary_id', $kary->id)->first();
                    if ($user) {
                        $user->update([
                            "is_active" => false,
                        ]);
                    }
                }

                // Nonaktifkan kontrak aktif di m_kary_det_kontrak
                $kontrak = m_kary_det_kontrak::where(
                    "m_karyawan_id",
                    $data->m_kary_id
                )
                    ->where("status", true)
                    ->first();
                if ($kontrak) {
                    $kontrak->update([
                        "status" => false,
                    ]);
                }

                \DB::commit();

                return response()->json([
                    "message" => 'Data pengunduran diri berhasil diposting dan status karyawan berhasil dinonaktifkan',
                ]);
            } else {
                return response()->json(
                    ["message" => 'Status data tidak valid untuk diposting'],
                    400
                );
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                "message" => "Terjadi kesalahan saat memproses data: " . $e->getMessage(),
            ], 500);
        }
    }

    public function custom_posted($request)
    {
        return $this->custom_post($request);
    }

    public function custom_syncKaryawanResign($request = null)
    {
        \DB::beginTransaction();
        try {
            $resigns = t_resign::where('status', 'POSTED')->get();
            $count = 0;

            foreach ($resigns as $resign) {
                if (!$resign->m_kary_id) continue;

                $kary = m_kary::find($resign->m_kary_id);
                if ($kary) {
                    $alasan = $resign->keterangan ?? "Pengunduran Diri";
                    if ($resign->tipe_resign_id) {
                        $tipeResign = \App\Models\BasicModels\m_general::where('id', $resign->tipe_resign_id)->value('value');
                        if ($tipeResign) {
                            $alasan = $tipeResign . ($resign->keterangan ? ' - ' . $resign->keterangan : '');
                        }
                    }

                    $kary->update([
                        "is_active" => false,
                        "tgl_berhenti" => $resign->date ?? $kary->tgl_berhenti ?? Carbon::now()->format('Y-m-d'),
                        "alasan_berhenti" => $alasan,
                    ]);

                    $user = default_users::where('m_kary_id', $kary->id)->first();
                    if ($user) {
                        $user->update(["is_active" => false]);
                    }

                    $kontrak = m_kary_det_kontrak::where("m_karyawan_id", $resign->m_kary_id)
                        ->where("status", true)
                        ->first();
                    if ($kontrak) {
                        $kontrak->update(["status" => false]);
                    }

                    $count++;
                }
            }

            \DB::commit();

            return response()->json([
                "message" => "$count data karyawan berhasil disinkronkan menjadi Inactive sesuai pengunduran diri",
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                "message" => "Gagal sinkronisasi: " . $e->getMessage(),
            ], 500);
        }
    }

    public function public_exportResign()
    {
        try {
            $fileName = 'data_resign_' . Carbon::now()->format('Ymd_His') . '.xlsx';

            $data = t_resign::with([
                    'm_kary.m_divisi',
                    'm_dir',
                    'm_divisi',
                    'tipe_resign' => function ($q) { $q->select('id', 'value'); },
                    'creator' => function ($q) { $q->select('id', 'name'); },
                ])
                ->get()
                ->map(function ($resign) {
                    return [
                        'TANGGAL' => $resign->date ? Carbon::parse($resign->date)->format('Y-m-d') : '',
                        'NOMOR' => $resign->nomor ?? '',
                        'NIK' => $resign->m_kary?->nik ?? '',
                        'NAMA_KARYAWAN' => $resign->m_kary?->nama_lengkap ?? '',
                        'DIVISI' => $resign->m_divisi?->nama ?? $resign->m_kary?->m_divisi?->nama ?? '',
                        'UNIT' => $resign->m_dir?->nama ?? '',
                        'TIPE_RESIGN' => $resign->tipe_resign?->value ?? '',
                        'KETERANGAN' => $resign->keterangan ?? '',
                        'STATUS' => $resign->status ?? '',
                        'SURAT_RESIGN' => $resign->surat_resign ?? '',
                        'SURAT_PRAKERIN' => $resign->surat_prakerin ?? '',
                        'DIBUAT_OLEH' => $resign->creator?->name ?? '-',
                        'DIBUAT_PADA' => $resign->created_at ? $resign->created_at->format('Y-m-d H:i:s') : '',
                    ];
                });

            // Buat export dinamis tanpa file terpisah
            $export = new class($data) implements FromCollection, WithHeadings {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array
                {
                    return [
                        'TANGGAL',
                        'NOMOR',
                        'NIK',
                        'NAMA KARYAWAN',
                        'DIVISI',
                        'UNIT',
                        'TIPE RESIGN',
                        'KETERANGAN',
                        'STATUS',
                        'SURAT RESIGN',
                        'SURAT PRAKERIN',
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
