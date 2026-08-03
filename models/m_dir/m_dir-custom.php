<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use DB;


class m_dir extends \App\Models\BasicModels\m_dir
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
      $newArrayData  = array_merge( $arrayData,[
        'm_comp_id' => auth()->user()->m_comp_id ?? 0
      ] );
      return [
          "model"  => $model,
          "data"   => $newArrayData,
          // "errors" => ['error1']
      ];
    }

    public function custom_seeder(){
        $data = [
            [
                "nama" => "Business & Network Building Material",
                "desc" => null,
                "is_active" => 1,
            ],
            [
                "nama" => "Business & Network Consumer Goods",
                "desc" => null,
                "is_active" => 1,
            ],
            [
                "nama" => "Operation & Business Development",
                "desc" => null,
                "is_active" => 1,
            ],
            [
                "nama" => "Finance, Accounting & Tax",
                "desc" => null,
                "is_active" => 1,
            ],
            [
                "nama" => "People Performance & Culture",
                "desc" => null,
                "is_active" => 1,
            ],
            [
                "nama" => "Information Technology",
                "desc" => null,
                "is_active" => 1,
            ],
            [
                "nama" => "CEO Office",
                "desc" => null,
                "is_active" => 1,
            ],
            [
                "nama" => "Business Development Office",
                "desc" => null,
                "is_active" => 1,
            ],
        ];

        m_dir::insert($data);
    }

    public function custom_dashboard()
    {
        $dir_query = m_dir::where('is_active', true);
        $dir_count = $dir_query->count();
        $div_count = m_divisi::where('is_active', true)->count();
        $total_kary = default_users::whereHas('m_kary', function($q){
            $q->where('is_active', true);
        })->count();
        $now = Carbon::now()->format('Y-m-d');
        $periode_in_date = Carbon::now()
            ->subMonth()
            ->day(20)
            ->format('Y-m-d');        

        $total_hadir = default_users::whereHas('presensi_absensi', function($q) use ($now){
            $q->where('tanggal', $now);
        })->count();
        $tidak_hadir = $total_kary - $total_hadir;

        $m_dir = $dir_query->get();
        $salary_per_dir = []; // array penampung

        foreach ($m_dir as $dir) {
            $gaji = t_final_gaji_det::whereHas('m_kary', function($q) use ($dir){
                    $q->where('m_dir_id', $dir->id);
                })
                ->where('periode_in_date',  $periode_in_date)
                ->sum('total_gaji') ?? 0;

            // bisa langsung masukkan ke array
            $salary_per_dir[] = [
                'dir_id'   => $dir->id,
                'periode'     => Carbon::parse($periode_in_date)->format('Y-m'),
                'dir_name' => $dir->nama ?? null, // kalau ada kolom nama
                'total_gaji'  => (int)$gaji
            ];
        }

        // $m_subcomp = m_subcomp::get();
        // $salary_per_subcomp = []; // array penampung

        // foreach ($m_subcomp as $subcomp) {
        //     $gaji = t_final_gaji_det::whereHas('m_kary', function($q) use ($subcomp){
        //             $q->where('m_subcomp_id', $subcomp->id);
        //         })
        //         ->where('periode_in_date',  $periode_in_date)
        //         ->sum('total_gaji') ?? 0;

        //     // bisa langsung masukkan ke array
        //     $salary_per_subcomp[] = [
        //         'm_dir_id'   => $dir->id,
        //         'periode'     => Carbon::parse($periode_in_date)->format('Y-m'),
        //         'subcomp_name' => $dir->name ?? null,
        //         'total_gaji'  => (int)$gaji
        //     ];
        // }
        $stat = $this->custom_getDashboardStats();
        // dd($stat['top_late']);

        $data = [
            "dir_count" => $dir_count,
            "div_count" => $div_count,
            "total_hadir" => $total_hadir,
            "total_absen" => $tidak_hadir,
            "dir_salary" => $salary_per_dir,
            "late"      => $stat['top_late'],
            "absent"    => $stat['top_absent'],
            "perfect"   => $stat['top_perfect']
        ];

        return $data;
    }

    public function custom_getDashboardStats()
    {
        \Carbon\Carbon::setLocale('id');
        $date_start = Carbon::now()->subMonths(1)->format('Y-m-d');
        $date_end = Carbon::now()->format('Y-m-d');

        // 1. TOP LATE (Sudah dikonversi ke menit di SQL)
        $topLate = DB::table('presensi_absensi as p')
            ->join('default_users as u', 'p.default_user_id', '=', 'u.id')
            ->join('m_kary as k', 'u.m_kary_id', '=', 'k.id')
            ->join('t_jadwal_kerja_det_hari as d', function($join) {
                $join->on('k.t_jadwal_kerja_id', '=', 'd.t_jadwal_kerja_id')
                    ->on('d.day', '=', DB::raw("(
                        CASE EXTRACT(DOW FROM p.tanggal)
                            WHEN 0 THEN 'Minggu'
                            WHEN 1 THEN 'Senin'
                            WHEN 2 THEN 'Selasa'
                            WHEN 3 THEN 'Rabu'
                            WHEN 4 THEN 'Kamis'
                            WHEN 5 THEN 'Jumat'
                            WHEN 6 THEN 'Sabtu'
                        END
                    )"));
            })
            ->select('k.nama_lengkap', DB::raw("ROUND(SUM(CASE WHEN p.checkin_time > d.waktu_mulai THEN EXTRACT(EPOCH FROM (p.checkin_time - d.waktu_mulai))/60 ELSE 0 END)) as total_late"))
            ->whereBetween('p.tanggal', [$date_start, $date_end])
            ->groupBy('k.nama_lengkap')
            ->orderByDesc('total_late')
            ->take(5)
            ->get();

        // 2. TOP ABSENT (Mencari hari kerja yang tidak ada di tabel presensi)
        // Menggunakan Generate Series untuk membuat kalender bayangan
        $topAbsent = DB::select("
            WITH date_range AS (
                SELECT generate_series(?::date, ?::date, '1 day'::interval)::date as tgl
            ),
            jadwal_karyawan AS (
                SELECT 
                    k.id as kary_id, 
                    k.nama_lengkap, 
                    u.id as user_id, 
                    dr.tgl
                FROM m_kary k
                JOIN default_users u ON k.id = u.m_kary_id
                CROSS JOIN date_range dr
                JOIN t_jadwal_kerja_det_hari d ON k.t_jadwal_kerja_id = d.t_jadwal_kerja_id 
                    AND d.day = (
                        CASE EXTRACT(DOW FROM dr.tgl)
                            WHEN 0 THEN 'Minggu'
                            WHEN 1 THEN 'Senin'
                            WHEN 2 THEN 'Selasa'
                            WHEN 3 THEN 'Rabu'
                            WHEN 4 THEN 'Kamis'
                            WHEN 5 THEN 'Jumat'
                            WHEN 6 THEN 'Sabtu'
                        END
                    )
                WHERE d.tipe_hari = 'KERJA'
                -- Tambahkan filter agar tidak menghitung hari masa depan jika range-nya sampai akhir bulan
                AND dr.tgl <= CURRENT_DATE
            )
            SELECT jk.nama_lengkap, COUNT(*) as total_absent
            FROM jadwal_karyawan jk
            LEFT JOIN presensi_absensi p ON jk.user_id = p.default_user_id AND jk.tgl = p.tanggal
            WHERE p.id IS NULL
            GROUP BY jk.nama_lengkap
            HAVING COUNT(*) > 0
            ORDER BY total_absent DESC
            LIMIT 5
        ", [$date_start, $date_end]);

        // 3. PERFECT ATTENDANCE (Hadir tepat waktu & pulang sesuai jadwal)
        $topPerfect = DB::table('presensi_absensi as p')
            ->join('default_users as u', 'p.default_user_id', '=', 'u.id')
            ->join('m_kary as k', 'u.m_kary_id', '=', 'k.id')
            ->join('t_jadwal_kerja_det_hari as d', function($join) {
                $join->on('k.t_jadwal_kerja_id', '=', 'd.t_jadwal_kerja_id')
                    // ->on('d.day', '=', DB::raw("trim(to_char(p.tanggal, 'Day'))"));
                    ->on('d.day', '=', DB::raw("(
                        CASE EXTRACT(DOW FROM p.tanggal)
                            WHEN 0 THEN 'Minggu'
                            WHEN 1 THEN 'Senin'
                            WHEN 2 THEN 'Selasa'
                            WHEN 3 THEN 'Rabu'
                            WHEN 4 THEN 'Kamis'
                            WHEN 5 THEN 'Jumat'
                            WHEN 6 THEN 'Sabtu'
                        END
                    )"));
            })
            ->select('k.nama_lengkap', DB::raw("COUNT(*) as total_perfect"))
            ->whereBetween('p.tanggal', [$date_start, $date_end])
            ->whereRaw("p.checkin_time <= d.waktu_mulai")
            ->whereRaw("p.checkout_time >= d.waktu_akhir")
            ->groupBy('k.nama_lengkap')
            ->orderByDesc('total_perfect')
            ->take(5)
            ->get();

        return [
            'top_late' => $topLate,
            'top_absent' => $topAbsent,
            'top_perfect' => $topPerfect
        ];
    }

    public function custom_latestat()
    {
        $date_end = Carbon::now()->format('Y-m-d');
        $date_start = Carbon::now()->subMonths(1)->format('Y-m-d');
        // dd($date_start, $date_end);

        $results = presensi_absensi::whereBetween('tanggal', [$date_start, $date_end])
        ->with(['default_users.m_kary.t_jadwal_kerja.t_jadwal_kerja_det_hari']) 
        ->get()
        // dd($results);
        ->map(function ($item) {
            $hariIni = Carbon::parse($item->tanggal)->translatedFormat('l');
            $jadwal = $item->default_users?->m_kary?->t_jadwal_kerja?->t_jadwal_kerja_det_hari
                ->where('day', $hariIni)->first();

            $lateMinutes = 0;

            if ($jadwal && $item->checkin_time) {
                $jamMasuk = Carbon::parse($jadwal->waktu_mulai);
                $jamCheckin = Carbon::parse($item->checkin_time);

                if ($jamCheckin->gt($jamMasuk)) {
                    $lateMinutes = $jamCheckin->diffInMinutes($jamMasuk);
                }
            }

            return [
                'nama' => $item->default_users?->m_kary?->nama_lengkap ?? 'Unknown',
                'late_minutes' => $lateMinutes,
                'tanggal' => $item->tanggal
            ];
        })
        ->groupBy('nama')
        ->map(function ($group) {
            return [
                'nama' => $group->first()['nama'],
                'total_late' => $group->sum('late_minutes')
            ];
        })
        ->sortByDesc('total_late')
        ->take(5)
        ->values();

        return response()->json($results);
    }

    public function custom_absent()
    {
        \Carbon\Carbon::setLocale('id');
        $date_end = Carbon::now();
        $date_start = Carbon::now()->subMonths(1);

        $karyawan = m_kary::with(['t_jadwal_kerja.t_jadwal_kerja_det_hari', 'default_users'])->get();

        $allPresensi = presensi_absensi::whereBetween('tanggal', [
                $date_start->format('Y-m-d'), 
                $date_end->format('Y-m-d')
            ])
            ->get()
            ->groupBy(function($item) {
                return $item->default_user_id . '_' . $item->tanggal;
            });

        $rangeTanggal = [];
        for ($date = $date_start->copy(); $date->lte($date_end); $date->addDay()) {
            $rangeTanggal[] = [
                'tanggal' => $date->format('Y-m-d'),
                'hari' => $date->translatedFormat('l')
            ];
        }

        $absentStats = $karyawan->map(function ($kary) use ($rangeTanggal, $allPresensi) {
            $absentCount = 0;
            $userId = $kary->default_users?->id;

            if (!$userId) return null; 

            foreach ($rangeTanggal as $rt) {
                $jadwal = $kary->t_jadwal_kerja?->t_jadwal_kerja_det_hari
                    ->where('day', $rt['hari'])
                    ->where('tipe_hari', 'KERJA') 
                    ->first();

                if ($jadwal) {
                    $key = $userId . '_' . $rt['tanggal'];
                    if (!isset($allPresensi[$key])) {
                        $absentCount++;
                    }
                }
            }

            return [
                'nama' => $kary->nama_lengkap ?? 'Unknown',
                'total_absent' => $absentCount
            ];
        })
        ->filter()
        ->where('total_absent', '>', 0)
        ->sortByDesc('total_absent')
        ->take(5)
        ->values();

        return response()->json($absentStats);
    }

    public function custom_perfectstat()
    {
        \Carbon\Carbon::setLocale('id');
        $date_end = Carbon::now()->format('Y-m-d');
        $date_start = Carbon::now()->subMonths(1)->format('Y-m-d');

        $results = presensi_absensi::whereBetween('tanggal', [$date_start, $date_end])
            ->with(['default_users.m_kary.t_jadwal_kerja.t_jadwal_kerja_det_hari']) 
            ->get()
            // --- TAMBAHKAN FILTER INI ---
            ->filter(function ($item) {
                // Hanya proses jika relasi user dan karyawan ada
                return $item->default_users !== null && $item->default_users->m_kary !== null;
            })
            // ----------------------------
            ->map(function ($item) {
                $hariIni = Carbon::parse($item->tanggal)->translatedFormat('l');
                $jadwal = $item->default_users->m_kary->t_jadwal_kerja?->t_jadwal_kerja_det_hari
                            ->where('day', $hariIni)->first();

                $isLate = false;
                $isEarlyOut = false;

                if ($jadwal) {
                    // 1. Cek Terlambat Datang
                    if ($item->checkin_time) {
                        $jamMasuk = Carbon::parse($jadwal->waktu_mulai);
                        $jamCheckin = Carbon::parse($item->checkin_time);
                        if ($jamCheckin->gt($jamMasuk)) {
                            $isLate = true;
                        }
                    }

                    // 2. Cek Pulang Awal
                    if ($item->checkout_time) {
                        $jamPulang = Carbon::parse($jadwal->waktu_selesai);
                        $jamCheckout = Carbon::parse($item->checkout_time);
                        if ($jamCheckout->lt($jamPulang)) {
                            $isEarlyOut = true;
                        }
                    } else {
                        // Jika tanggal sudah lewat tapi belum checkout, anggap gagal
                        if ($item->tanggal < Carbon::now()->format('Y-m-d')) {
                            $isEarlyOut = true;
                        }
                    }
                }

                return [
                    'nama' => $item->default_users->m_kary->nama_lengkap,
                    'is_perfect_today' => (!$isLate && !$isEarlyOut) ? 1 : 0,
                    'is_failed' => ($isLate || $isEarlyOut) ? 1 : 0,
                ];
            })
            ->groupBy('nama')
            ->map(function ($group) {
                $totalGagal = $group->sum('is_failed');
                return [
                    'nama' => $group->first()['nama'],
                    'total_hadir_sempurna' => $group->sum('is_perfect_today'),
                    'pernah_melanggar' => $totalGagal > 0
                ];
            })
            ->where('pernah_melanggar', false)
            ->sortByDesc('total_hadir_sempurna')
            ->take(5)
            ->values();

        return response()->json($results);
    }


}