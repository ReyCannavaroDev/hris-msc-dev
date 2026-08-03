<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;
Carbon::setLocale("id");


class m_kary_det_kontrak extends \App\Models\BasicModels\m_kary_det_kontrak
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }
    
    public $fileColumns    = [ 'contract' ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $nomor = @$arrayData['nomor'] ?? $this->helper->generateNomor("KODE KONTRAK");
        $newArrayData  = array_merge( $arrayData,[
                "nomor" => $nomor,
        ]);

        $hasOldContract = \DB::table('m_kary_det_kontrak')
            ->where('m_karyawan_id', $arrayData['m_karyawan_id'])
            ->exists();

        if($hasOldContract)
        {
            $tgl_awal  = Carbon::createFromFormat('d/m/Y', $arrayData['tgl_awal'])->format('Y-m-d');
            $tgl_akhir = Carbon::createFromFormat('d/m/Y', $arrayData['tgl_akhir'])->format('Y-m-d');
            $ext = \DB::table('t_extend_kontrak')->insert([
                'nomor'           => $nomor,
                'm_karyawan_id'   => $arrayData['m_karyawan_id'],
                'm_divisi_id'     => $arrayData['m_divisi_id'],
                'm_dir_id'        => $arrayData['m_dir_id'],
                'tipe_karyawan_id'=> $arrayData['tipe_karyawan_id'],
                'tgl_awal'        => $tgl_awal,
                'tgl_akhir'       => $tgl_akhir,
                'duration'        => $arrayData['duration'],
                'contract_signed' => @$arrayData['contract'] ?? '',
                'status'          => 'COMPLETED',
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
            ]);
        }

        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }    

    public function t_extend_kontrak() :\HasMany
    {
        return $this->HasMany('App\Models\BasicModels\t_extend_kontrak', 'm_kary_det_kontrak_id', 'id');
    }

    public function scopeEndKontrak($model)
    {
        $periode_awal = app()->request->start_month;   // format: YYYY-MM
        $periode_akhir = app()->request->end_month;    // format: YYYY-MM

        $id_tetap = m_general::where('group', 'TIPE KARYAWAN')
            ->where('key', 'T')
            ->first()?->id ?? 0;

        // Jika salah satu kosong → default bulan ini
        if (!$periode_awal) {
            $periode_awal = Carbon::now()->format('Y-m');
        }
        if (!$periode_akhir) {
            $periode_akhir = Carbon::now()->format('Y-m');
        }

        // Convert YYYY-MM → tanggal awal & akhir bulan
        try {
            $startDate = Carbon::createFromFormat('Y-m', $periode_awal)->startOfMonth();
            $endDate   = Carbon::createFromFormat('Y-m', $periode_akhir)->endOfMonth();
        } catch (\Exception $e) {
            // fallback jika format salah
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfMonth();
        }

        return $model
            ->select(
                'm_kary.id',
                'm_kary.nama_lengkap as m_kary.nama_lengkap',
                'm_divisi.nama as jabatan',
                'm_dir.nama as m_dir.nama',
                'tipe_karyawan.value as tipe_karyawan',
                'tipe_karyawan.value as tipe_karyawan.value',
                'm_kary_det_kontrak.*'
            )
            ->join('m_kary', 'm_kary_det_kontrak.m_karyawan_id', 'm_kary.id')
            ->whereBetween('m_kary_det_kontrak.tgl_akhir', [$startDate, $endDate])
            ->where('m_kary_det_kontrak.status', true)
            ->whereDoesntHave('t_extend_kontrak')
            ->whereNotIn('m_kary_det_kontrak.tipe_karyawan_id', (array) $id_tetap);
    }

    public function public_exportEndKontrak()
    {
        try {
            $fileName =
                "data_expired_kontrak_" .
                Carbon::now()->format("Ymd_His") .
                ".xlsx";

            $periode_awal = app()->request->start_month;   // format: YYYY-MM
            $periode_akhir = app()->request->end_month;    // format: YYYY-MM

            $id_tetap = m_general::where('group', 'TIPE KARYAWAN')
                ->where('key', 'T')
                ->first()?->id ?? 0;

            // Jika salah satu kosong → default bulan ini
            if (!$periode_awal) {
                $periode_awal = Carbon::now()->format('Y-m');
            }
            if (!$periode_akhir) {
                $periode_akhir = Carbon::now()->format('Y-m');
            }

            // Convert YYYY-MM → tanggal awal & akhir bulan
            try {
                $startDate = Carbon::createFromFormat('Y-m', $periode_awal)->startOfMonth();
                $endDate   = Carbon::createFromFormat('Y-m', $periode_akhir)->endOfMonth();
            } catch (\Exception $e) {
                // fallback jika format salah
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
            }


            // Ambil data extend kontrak + relasi yang relevan
            $data = m_kary_det_kontrak::join('m_kary', 'm_kary_det_kontrak.m_karyawan_id', 'm_kary.id')
            ->whereBetween('m_kary_det_kontrak.tgl_akhir', [$startDate, $endDate])
            ->where('m_kary_det_kontrak.status', true)
            ->whereDoesntHave('t_extend_kontrak')
            ->whereNotIn('m_kary_det_kontrak.tipe_karyawan_id', (array) $id_tetap)
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
                        // "DURASI_BULAN" => $extend->duration ?? 0,
                        // "TEMPLATE_KONTRAK" => $extend->contract_template ?? "",
                        // "KONTRAK_TTD" => $extend->contract_signed ?? "",
                        "STATUS" => ($extend->status ?? false) ? 'Aktif' : 'Nonaktif',
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

    public function custom_countend()
    {
        $startDate = Carbon::now()->startOfMonth()->addMonths(2)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth()->addMonths(2)->endOfMonth();
        $date = $startDate->copy()->translatedFormat('F Y');

        $id_tetap = m_general::where('group', 'TIPE KARYAWAN')
            ->where('key', 'T')
            ->first()?->id ?? 0;

        $count = m_kary_det_kontrak::whereBetween('tgl_akhir', [$startDate, $endDate])
        ->whereDoesntHave('t_extend_kontrak')
        ->whereNotIn('m_kary_det_kontrak.tipe_karyawan_id', (array) $id_tetap)
        ->count();

        return response()->json([
            "periode" => $date,
            "count_end" => $count,
        ]);
    }

}