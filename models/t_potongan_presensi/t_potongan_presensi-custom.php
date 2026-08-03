<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
Carbon::setLocale("id");

class t_potongan_presensi extends \App\Models\BasicModels\t_potongan_presensi
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

    protected $factorAdded = [];

    public function custom_generate()
    {
        return $this->generatePotongan();
    }

    public function generatePotongan()
    {
        try {
            $req = app()->request;
            $kary = m_kary::selectRaw(
                "m_kary.*,m_general.value periode_text, m_dir.nama dir, m_divisi.nama divisi"
            )
                ->leftJoin("m_dir", "m_dir.id", "m_kary.m_dir_id")
                ->leftJoin("m_divisi", "m_divisi.id", "m_kary.m_divisi_id")
                // ->leftJoin('m_dept','m_dept.id','m_kary.m_divisi_id')
                ->join("m_general", "m_general.id", "m_kary.periode_gaji_id");
            // ->whereRaw('m_kary.m_standart_gaji_id in(select s.id from m_standart_gaji s where s.is_active = true)')

            if ($req->m_dir_id) {
                $kary = $kary->where("m_kary.m_dir_id", $req->m_dir_id);
            }
            if ($req->m_divisi_id) {
                $kary = $kary->where("m_kary.m_divisi_id", $req->m_divisi_id);
            }

            $kary = $kary->get();

            $date_from = Carbon::parse($req->periode_awal);
            $date_to = Carbon::parse($req->periode_akhir);
            // dd($date_from, $date_to);

            // Menghitung jumlah bulan antara tanggal_awal dan tanggal_akhir
            $interval = $date_from->diff($date_to);
            $jumlah_bulan = $interval->y * 12 + $interval->m;

            $data = [];
            for ($i = 0; $i <= $jumlah_bulan; $i++) {
                $date = $date_from;
                foreach ($kary as $key) {
                    $potongan = $this->potonganOfKary(
                        $key->id,
                        $date_from,
                        $date_to
                    );
                    $data[] = [
                        "m_kary_id" => $key->id,
                        "m_kary.nik" => $key->nik,
                        "m_kary_dir_id" => $key->m_dir_id,
                        "m_kary_dir.nama" => $key->dir,
                        "m_kary_divisi_id" => $key->m_divisi_id,
                        "m_kary_divisi.nama" => $key->divisi,
                        "nik" => $key->nik,
                        "nama_lengkap" => $key->nama_lengkap,
                        "periode" => $date_from->format("d-m-Y"),
                        "periode_in_date" => $date,
                        "periode_id" => $key->periode_gaji_id,
                        "periode_text" => $key->periode_text,
                        "total_potongan" => $potongan["total_potongan"],
                        "detail_potongan" => $potongan["detail"],
                    ];
                }

                // Menambahkan satu bulan untuk iterasi berikutnya
                $date_from->add(new \DateInterval("P1M"));
            }

            return $this->helper->customResponse("OK", 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function potonganOfKary($id, $periode_awal, $periode_akhir)
    {
        try {
            $m_kary_id = $id;
            $kary = m_kary::find($m_kary_id);
            // if(!@$kary->m_standart_gaji_id) return [
            //     'm_kary_id'  => $m_kary_id,
            //     'total_gaji' => 0,
            //     'total_tax'  => 0,
            //     'netto'      => 0,
            //     'detail'     => []
            // ];
            $m_standart_gaji =
                m_standart_gaji::find($kary->m_standart_gaji_id) ?? null;
            // default summary salary
            $getBasicSalary = $this->factorPotongan(
                $m_standart_gaji,
                $kary,
                $periode_awal,
                $periode_akhir
            );

            $netto = $this->summarySubSalary($getBasicSalary);
            $getBasicSalary = array_merge($getBasicSalary, [
                [
                    "label" => "Total Potongan",
                    "factor" => "=",
                    "value" => $netto,
                    "type" => "-",
                ],
            ]);

            $nettoFinish = $this->summarySubSalary($getBasicSalary);

            // default summary tax
            // $arrPPH         = $this->countPPH21($kary, $netto);
            // $totalTax = @$arrPPH[0]['value'];
            // if(count($arrPPH)){
            //     $getBasicSalary = array_merge($getBasicSalary, $arrPPH);
            //     $nettoFinish    = $this->summarySubSalary($getBasicSalary);
            //     $getBasicSalary    = array_merge($getBasicSalary, [
            //         [
            //             'label'    => 'Total Gaji (Setelah PPH 21)',
            //             'factor'   => '=',
            //             'value'    => $nettoFinish,
            //             'type'     => '-'
            //         ]
            //     ]);
            // }

            return [
                "m_kary_id" => $m_kary_id,
                "total_potongan" => $netto,
                "netto" => $nettoFinish,
                "detail" => $getBasicSalary,
            ];
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    private function factorPotongan(
        $standart_gaji,
        $kary = null,
        $periode_awal = null,
        $periode_akhir = null
    ) {
        // $firstDayOfMonth = "$periode-01";
        $firstDayOfMonth = $periode_awal;
        $date = new \DateTime($firstDayOfMonth);

        // Set the date to the last day of the month
        $date->modify("last day of this month");

        // Get the last day as a string in 'Y-m-d' format
        // $lastDayOfMonth = $date->format('Y-m-d');
        $lastDayOfMonth = $periode_akhir;

        $gaji_pokok = 0;
        if ($standart_gaji != null) {
            $gaji_pokok = $standart_gaji?->gaji_pokok ?? 0;

            // faktor lain dari table m_standart_gaji_det
            $standart_gaji_det = m_standart_gaji_det::where(
                "m_standart_gaji_id",
                $standart_gaji->id ?? 0
            )->get();
        }

        if (!$kary) {
            return $defaultColumns;
        }

        // check kehadiran karyawan
        $rekap = $this->hitungRekap(
            @$kary->id,
            $firstDayOfMonth,
            $lastDayOfMonth
        );
        // dd($rekap);
        if (count($rekap)) {
            $not_attend =
                $rekap["hari_kerja"] -
                ($rekap["jumlah_hadir"] + $rekap["jumlah_cuti"]);

            // $total_lembur_hari_biasa = ceil(
            //     $rekap["total_menit_lembur_kerja"] / 60
            // );

            // $total_lembur_hari_libur = ceil(
            //     $rekap["total_menit_lembur_libur"] / 60
            // );

            $total_terlambat = $rekap["total_jam_terlambat"];

            //hitung telat
            // dd($rekap['detail_menit_terlambat']);
            // if (count($rekap["detail_menit_terlambat"]) > 0) {
            //     foreach (
            //         $rekap["detail_menit_terlambat"]
            //         as $tanggal => $menit
            //     ) {
            //         $value = 0;
            //         $minutes = (int) $menit;

            //         if ($minutes > 0) {
            //             if ($minutes <= 15) {
            //                 $value = 5000;
            //             } elseif ($minutes <= 30) {
            //                 $value = 10000;
            //             } elseif ($minutes <= 60) {
            //                 $value = 15000;
            //             } elseif ($minutes <= 90) {
            //                 $value = 25000;
            //             } else {
            //                 // lebih dari 90 menit:
            //                 // dasar 90 menit = 25000, setiap tambahan 30 menit => +10000
            //                 $extra = $minutes - 90;
            //                 // hitung berapa blok 30 menit (pakai ceil agar partial block dihitung)
            //                 $blocks = (int) ceil($extra / 30);
            //                 $value = 25000 + $blocks * 10000;
            //             }

            //             $defaultColumns[] = [
            //                 "label" =>
            //                     "Potongan Terlambat " .
            //                     $tanggal .
            //                     " (" .
            //                     $minutes .
            //                     " Menit)",
            //                 "factor" => "-",
            //                 "value" => $value,
            //                 "type" => "Harian",
            //                 "can_adjust" => 1,
            //             ];
            //         }
            //     }
            // }

            if (count($rekap["detail_menit_terlambat"]) > 0) {

                $komponenTerlambatList = collect(
                    $standart_gaji_det ?? []
                )->filter(function ($item) {
                    return stripos($item->komponen, "terlambat") !== false;
                });

                $komponenTerlambatList = $komponenTerlambatList
                    ->sortBy(function ($item) {
                        preg_match("/\d+/", $item->komponen, $m);
                        return isset($m[0]) ? (int) $m[0] : 0;
                    })
                    ->values();

                foreach (
                    $rekap["detail_menit_terlambat"]
                    as $tanggal => $menit
                ) {
                    $minutes = (int) $menit;
                    if ($minutes <= 0) {
                        continue;
                    }

                    $komponen = $komponenTerlambatList->first(function (
                        $item
                    ) use ($minutes) {
                        preg_match("/\d+/", $item->komponen, $m);
                        $batas = isset($m[0]) ? (int) $m[0] : 0;
                        return $minutes <= $batas;
                    });

                    if (!$komponen) {
                        $komponen = $komponenTerlambatList->last();
                    }

                    $value = (float) ($komponen->nilai ?? 0);

                    $defaultColumns[] = [
                        "label" =>
                            $komponen->komponen .
                            " " .
                            $tanggal .
                            " (" .
                            $minutes .
                            " Menit)",
                        "factor" => $komponen->faktor ?? "-",
                        "value" => $value,
                        "type" => "Harian",
                        "can_adjust" => 1,
                    ];
                }
            }

            //hitung tidak masuk
            $tipe_karyawan =
                m_general::find($kary->tipe_karyawan_id)?->value ?? "KONTRAK";

            if ($gaji_pokok > 0) {
                $gaji_harian = (float) ($gaji_pokok / $rekap["hari_kerja"]);
            } else {
                $gaji_harian = 0;
            }

            if ($tipe_karyawan === "KONTRAK") {
                $value = $gaji_harian * 1.5 * $not_attend;
                $defaultColumns[] = [
                    "label" =>
                        "Potongan Tidak Masuk Kerja (" . $not_attend . " Hari)",
                    "factor" => "-",
                    "value" => $value,
                    "type" => "Harian",
                    "can_adjust" => 1,
                ];
            } elseif ($tipe_karyawan === "TETAP") {
                $value = 100000 * $not_attend;
                $defaultColumns[] = [
                    "label" =>
                        "Potongan Tidak Masuk Kerja (" . $not_attend . " Hari)",
                    "factor" => "-",
                    "value" => $value,
                    "type" => "Harian",
                    "can_adjust" => 1,
                ];
            }

            //hitung lembur
            // if ($total_lembur_hari_biasa > 0) {
            //     $upahPerJam = 10000;
            //     $value = $total_lembur_hari_biasa * $upahPerJam;

            //     // $jamPertama = min(1, $total_lembur_hari_biasa);
            //     // $jamBerikutnya = max(0, $total_lembur_hari_biasa - 1);

            //     // $value =
            //     //     $jamPertama * 1.5 * $upahPerJam +
            //     //     $jamBerikutnya * 2 * $upahPerJam;

            //     $defaultColumns[] = [
            //         "label" => "Upah Lembur Hari Kerja ($total_lembur_hari_biasa Jam)",
            //         "factor" => "+",
            //         "value" => $value,
            //         "type" => "Bulanan",
            //         "can_adjust" => 1,
            //     ];
            // }

            // if ($total_lembur_hari_libur > 0) {
            //     $upahPerJam = 12500;
            //     $value = $total_lembur_hari_libur * $upahPerJam;

            //     // $jamPertama = min(7, $total_lembur_hari_libur);
            //     // $jamKedelapan = $total_lembur_hari_libur > 7 ? 1 : 0;
            //     // $jamSisanya = max(0, $total_lembur_hari_libur - 8);

            //     // $value =
            //     //     $jamPertama * 2 * $upahPerJam +
            //     //     $jamKedelapan * 3 * $upahPerJam +
            //     //     $jamSisanya * 4 * $upahPerJam;

            //     $defaultColumns[] = [
            //         "label" => "Biaya Lembur Hari Libur ($total_lembur_hari_libur Jam)",
            //         "factor" => "+",
            //         "value" => $value,
            //         "type" => "Bulanan",
            //         "can_adjust" => 1,
            //     ];
            // }
        }

        // faktor lain :Potongan
        $t_potongan = t_potongan::where("m_kary_id", @$kary->id ?? 0)
            ->orWhere("is_all_kary", true)
            ->whereRaw("date_from >= ? and date_to <= ?", [
                $firstDayOfMonth,
                $lastDayOfMonth,
            ])
            ->get();
        if (count($t_potongan)) {
            foreach ($t_potongan as $d) {
                $nilai_netto =
                    ((float) $d->nilai * (float) $d->percentage) / 100;
                $defaultColumns[] = [
                    "label" => "Potongan - $d->nomor",
                    "factor" => "-",
                    "value" => $nilai_netto,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                    "t_potongan_id" => $d->id,
                ];
            }
        }

        return $defaultColumns;
    }

    private function summarySubSalary($arrConfig)
    {
        return array_reduce(
            $arrConfig,
            function ($carry, $item) {
                if (is_numeric($item["value"])) {
                    $value = (float) $item["value"];
                    if ($value != 0) {
                        if ($item["factor"] == "+") {
                            $carry = $carry + $item["value"];
                        } elseif ($item["factor"] == "-") {
                            $carry = $carry - $item["value"];
                        }
                    }
                }
                return $carry;
            },
            0
        );
    }

    public function custom_save($req)
    {
        $counter = count($req->detail);
        if ($counter) {
            $nomor = $this->helper->generateNomor("KODE PERHITUNGAN GAJI");
            foreach ($req->detail as $key) {
                $checkAndDelete = $this->where("m_kary_id", @$key["m_kary_id"])
                    ->where("periode", @$key["periode"])
                    ->delete();
                $key["nomor"] = $nomor;
                $key["detail_gaji"] = json_encode($key["detail_gaji"]);
                $hdr = $this->create($key);
            }
        }
        return $this->helper->customResponse("$counter Data berhasil disimpan");
    }

    public function scopeGenerateForFinal($model)
    {
        $req = app()->request;
        // $date_from = \DateTime::createFromFormat('Y-m-d', $req->periode_awal.'-01') ?? null;
        // $date_to = \DateTime::createFromFormat('Y-m-d', $req->periode_akhir.'-30') ?? null;
        $date_from = Carbon::parse($req->periode_awal) ?? null;
        $date_to = Carbon::parse($req->periode_akhir) ?? null;

        $model = $model->whereBetween("periode_in_date", [
            $date_from,
            $date_to,
        ]);
        if ($req->m_divisi_id) {
            $model = $model->where(
                "t_perhitungan_gaji.m_kary_divisi_id",
                $req->m_divisi_id
            );
        }
        if ($req->m_dir_id) {
            $model = $model->where(
                "t_perhitungan_gaji.m_kary_dir_id",
                $req->m_dept_id
            );
        }

        return $model;
    }

    private function hitungRekap($kary_id, $start, $end): array
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $period = CarbonPeriod::create($start, $end);

        $m_kary = m_kary::findOrFail($kary_id);

        $userId = default_users::where("m_kary_id", $m_kary->id)
            ->pluck("id")
            ->first();

        $presensi = presensi_absensi::with("t_jadwal_kerja_det_hari")
            ->where("default_user_id", $userId)
            ->whereBetween("tanggal", [$start, $end])
            ->get()
            ->keyBy(
                fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d")
            );

        $t_jadwal_kerja_det_hari = t_jadwal_kerja_det_hari::whereHas(
            "t_jadwal_kerja",
            function ($q) use ($m_kary) {
                $q->where("id", $m_kary->t_jadwal_kerja_id);
            }
        )
            ->get()
            ->keyBy(fn($item) => $item->day);

        $cuti = t_cuti::where("m_kary_id", $kary_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween("date_from", [$start, $end])
                    ->orWhereBetween("date_to", [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where("date_from", "<=", $start)->where(
                            "date_to",
                            ">=",
                            $end
                        );
                    });
            })
            // ->where("status", "POSTED")
            ->where("status", "APPROVED")
            ->get();

        $cutiDates = [];
        foreach ($cuti as $c) {
            foreach (CarbonPeriod::create($c->date_from, $c->date_to) as $tgl) {
                $cutiDates[$tgl->format("Y-m-d")] = $c->keterangan ?? "CUTI";
            }
        }

        // --- Hitung Rekap ---
        $hasil = [];
        $jumlah_hadir = 0;
        $total_menit_lembur_kerja = 0;
        $total_menit_lembur_libur = 0;
        $total_menit_terlambat = 0;
        $detail_menit_terlambat = [];
        $total_jam_terlambat = 0;
        $total_jam_tidak_hadir = 0;

        // ambil data lembur dalam periode
        $lembur = t_lembur::where("m_kary_id", $kary_id)
            ->whereBetween("tanggal", [$start, $end])
            // ->where("status", "POSTED")
            ->where("status", "APPROVED")
            ->get()
            ->groupBy(
                fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d")
            );

        $liburNasional = m_libur_nasional::whereBetween("tanggal", [
            $start,
            $end,
        ])->get();
        $liburDates = $liburNasional
            ->pluck("keterangan", "tanggal")
            ->mapWithKeys(
                fn($keterangan, $tgl) => [
                    Carbon::parse($tgl)->format("Y-m-d") =>
                        $keterangan ?? "LIBUR NASIONAL",
                ]
            );

        foreach ($period as $tanggal) {
            $key = $tanggal->format("Y-m-d");
            $data = $presensi[$key] ?? null;

            $status = $data?->status ?? "NOT ATTEND";
            if (isset($cutiDates[$key])) {
                $status = $cutiDates[$key];
            }
            // dd($tanggal->translatedFormat('l'));

            // --- ambil tipe hari ---
            $tipe = null;
            if ($data && $data->t_jadwal_kerja_det_hari) {
                $tipe = $data->t_jadwal_kerja_det_hari->tipe_hari;
            } else {
                $tipe = "KERJA";
                $jadwal =
                    $t_jadwal_kerja_det_hari[$tanggal->translatedFormat("l")];
                // dd($t_jadwal_kerja_det_hari['Minggu']);
                if ($jadwal) {
                    $tipe = $jadwal->tipe_hari;
                }
            }

            if (isset($liburDates[$key])) {
                $status = $liburDates[$key];
                $tipe = $liburDates[$key];
            }

            // --- hitung hadir ---
            if ($status === "ATTEND" && $tipe === "KERJA") {
                $jumlah_hadir++;
            }

            // --- hitung lembur ---
            if (isset($lembur[$key])) {
                foreach ($lembur[$key] as $l) {
                    $mulai = Carbon::parse($l->jam_mulai);
                    $selesai = Carbon::parse($l->jam_selesai);
                    $menit = $selesai->diffInMinutes($mulai);

                    if ($tipe === "KERJA") {
                        $total_menit_lembur_kerja += $menit;
                    } else {
                        $total_menit_lembur_libur += $menit;
                    }
                }
            }

            // --- hitung terlambat ---
            if (
                $data &&
                $data->checkin_time &&
                $data->t_jadwal_kerja_det_hari?->waktu_mulai
            ) {
                $checkin = Carbon::parse($data->checkin_time);
                $jadwalMulai = Carbon::parse(
                    $data->t_jadwal_kerja_det_hari->waktu_mulai
                );

                if ($checkin->greaterThan($jadwalMulai)) {
                    $menit_terlambat = $jadwalMulai->diffInMinutes($checkin);

                    $total_menit_terlambat += $jadwalMulai->diffInMinutes(
                        $checkin
                    );

                    $total_jam_terlambat += $menit_terlambat;
                    $detail_menit_terlambat[$key] = $menit_terlambat;
                }
            }

            // --- hitung tidak hadir ---
            if ($tipe === "KERJA" && $status === "NOT ATTEND") {
                if (
                    $data &&
                    $data->t_jadwal_kerja_det_hari?->waktu_mulai &&
                    $data->t_jadwal_kerja_det_hari?->waktu_selesai
                ) {
                    $mulai = Carbon::parse(
                        $data->t_jadwal_kerja_det_hari->waktu_mulai
                    );
                    $selesai = Carbon::parse(
                        $data->t_jadwal_kerja_det_hari->waktu_selesai
                    );
                    $total_jam_tidak_hadir += $selesai->diffInHours($mulai) - 1;
                } else {
                    $total_jam_tidak_hadir += 8; // fallback default 8 jam
                }
            }

            $hasil[] = [
                "tanggal" => $key,
                "tipe" => $tipe,
                "status" => $status,
            ];
        }
        return [
            "hari_kerja" => collect($hasil)
                ->where("tipe", "KERJA")
                ->count(),
            "jumlah_hadir" => $jumlah_hadir,
            "total_jam_tidak_hadir" => $total_jam_tidak_hadir,
            "jumlah_cuti" => count($cutiDates),
            "total_menit_lembur_kerja" => $total_menit_lembur_kerja,
            "total_menit_lembur_libur" => $total_menit_lembur_libur,
            "total_menit_terlambat" => $total_menit_terlambat,
            "detail_menit_terlambat" => $detail_menit_terlambat,
            "total_jam_terlambat" => $total_jam_terlambat,
        ];
    }
}
