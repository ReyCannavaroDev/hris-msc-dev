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

    public function custom_post($request)
    {
        $data = t_resign::find($request->id);
        if (!$data) {
            return response()->json(["message" => "Data not found"], 404);
        }
        if ($data->status === "DRAFT") {
            $data->update([
                "status" => "POSTED",
            ]);

            $kary = m_kary::find($data->m_kary_id);
            if($kary->is_active === false){
                return response()->json(["message" => "Karyawan sudah tidak aktif"], 404);
            }
            if ($kary) {
                $kary->update([
                    "is_active" => false,
                ]);
                $user = default_users::where('m_kary_id', $kary->id)->first();
                $user->update([
                    "is_active" => false,
                ]);
            }

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

            return response()->json([
                "message" => 'DRAFT status changed to "POSTED"',
            ]);
        } else {
            return response()->json(
                ["message" => 'POSTED status is not "DRAFT"'],
                400
            );
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
