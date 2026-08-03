<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;


class t_bonus extends \App\Models\BasicModels\t_bonus
{    
    private $helper;
    
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }
    
    public $fileColumns = [
        'doc'
    ];
    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE BONUS"),
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_postData($request)
    {
        $data = t_bonus::find($request->id);

        if (!$data) {
            return response()->json(["error" => "Data tidak ditemukan."], 404);
        }

        try {
            $update = $data->update([
                "status" => "POSTED",
            ]);

            if ($update) {
                return response()->json([
                    "message" => "Data berhasil diposting.",
                ]);
            } else {
                return response()->json(
                    ["error" => "Gagal memperbarui status."],
                    500
                );
            }
        } catch (\Exception $e) {
            // Handle exception, log error messages, etc.
            return response()->json(
                ["error" => "Terjadi kesalahan: " . $e->getMessage()],
                500
            );
        }
    }

    public function public_exportBonus()
    {
        try {
            $fileName = 'data_bonus_' . Carbon::now()->format('Ymd_His') . '.xlsx';

            // Ambil data bonus + relasi yang relevan
            $data = t_bonus::with(['m_kary.m_divisi', 'm_kary.m_dir', 'jenis_bonus'])
                ->get()
                ->map(function ($bonus) {
                    return [
                        'NOMOR' => $bonus->nomor ?? '',
                        'NAMA_KARYAWAN' => $bonus->m_kary?->nama_lengkap ?? '',
                        'DIVISI' => $bonus->m_kary?->m_divisi?->nama ?? '',
                        'DIREKTORAT' => $bonus->m_kary?->m_dir?->nama ?? '',
                        'JENIS_BONUS' => $bonus->jenis_bonus?->value ?? '',
                        'NILAI' => number_format($bonus->nilai, 2, ',', '.') ?? '0',
                        'PERIODE' => ($bonus->date_from ? Carbon::parse($bonus->date_from)->format('Y-m-d') : '-') . ' s.d. ' .
                                    ($bonus->date_to ? Carbon::parse($bonus->date_to)->format('Y-m-d') : '-'),
                        'STATUS' => $bonus->status ?? '',
                        'KETERANGAN' => $bonus->keterangan ?? '',
                        'DOKUMEN' => $bonus->no_doc ?? '',
                        'CREATED_AT' => $bonus->created_at ? $bonus->created_at->format('Y-m-d H:i:s') : '',
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
                        'NOMOR',
                        'NAMA KARYAWAN',
                        'DIVISI',
                        'DIREKTORAT',
                        'JENIS BONUS',
                        'NILAI',
                        'PERIODE',
                        'STATUS',
                        'KETERANGAN',
                        'DOKUMEN',
                        'CREATED AT',
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