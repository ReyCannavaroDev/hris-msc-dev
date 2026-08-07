<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
Carbon::setLocale('id');

class t_perhitungan_gaji extends \App\Models\BasicModels\t_perhitungan_gaji
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }

    public $fileColumns = [ /*file_column*/];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];


    protected $factorAdded = [];

    private function factorSalary($standart_gaji, $kary = null, $periode_awal = null, $periode_akhir = null)
    {
        // $firstDayOfMonth = "$periode-01";
        $firstDayOfMonth = $periode_awal;
        $date = new \DateTime($firstDayOfMonth);

        // Set the date to the last day of the month
        $date->modify('last day of this month');

        // Get the last day as a string in 'Y-m-d' format
        // $lastDayOfMonth = $date->format('Y-m-d');
        $lastDayOfMonth = $periode_akhir;

        $defaultColumns = [
            [
                'name' => 'gaji_pokok',
                'type' => 'gaji_pokok_periode'
            ]
            // [
            //     'name'  => 'uang_saku',
            //     'type'  => 'uang_saku_periode'
            // ],
            // [
            //     'name'  => 'tunjangan_posisi',
            //     'type'  => 'tunjangan_posisi_periode'
            // ],
            // [
            //     'name'  => 'tunjangan_kemahalan_id',
            //     'table' => 'm_tunj_kemahalan',
            //     'type'  => 'tunjangan_kemahalan_periode'
            // ],
            // [
            //     'name'  => 'uang_makan',
            //     'type'  => 'uang_makan'
            // ],
            // [
            //     'name'  => 'tunjangan_tetap',
            //     'type'  => 'tunjangan_tetap'
            // ]
        ];

        foreach ($defaultColumns as $idx => $key) {
            $defaultColumns[$idx]['label'] = $this->helper->snakeCaseToCapitalize($key['name']);
            $defaultColumns[$idx]['factor'] = '+';
            $defaultColumns[$idx]['value'] = (float) $standart_gaji?->gaji_pokok ?? 0;
            $defaultColumns[$idx]['can_adjust'] = 1;
            // if ($defaultColumns[$idx]['value'] == 0) {
            //     unset($defaultColumns[$idx]);
            // }
        }

        $gaji_pokok = 0;
        if ($standart_gaji != null) {
            $gaji_pokok = $standart_gaji?->gaji_pokok ?? 0;

            // faktor lain dari table m_standart_gaji_det
            $standart_gaji_det = m_standart_gaji_det::where('m_standart_gaji_id', $standart_gaji->id ?? 0)->get();
            foreach ($standart_gaji_det as $d) {
                if ($d->periode != 'Harian') {
                    if ($d->tipe_komponen === 'NOMINAL') {
                        $defaultColumns[] = [
                            'label' => $d->komponen,
                            'factor' => $d->faktor,
                            'value' => (int) $d->nilai,
                            'type' => $d->periode,
                            'can_adjust' => 1

                        ];
                    } else {
                        $value = (int) ($d->nilai / 100 * $gaji_pokok);
                        $defaultColumns[] = [
                            'label' => $d->komponen,
                            'factor' => $d->faktor,
                            'value' => $value,
                            'type' => $d->periode,
                            'can_adjust' => 1
                        ];
                    }
                }
            }
        }


        if (!$kary)
            return $defaultColumns;

        // tunjangan masa kerja
        // $general_masa_kerja = m_general::where('group', 'TUNJANGAN MASA KERJA')->where('key','01')->pluck('value')->first();
        // if($general_masa_kerja && $kary->tgl_masuk) {
        //     $general_masa_kerja = (float)$general_masa_kerja;
        //     $date_from = \DateTime::createFromFormat('Y-m-d', $kary->tgl_masuk);
        //     $date_to = \DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
        //     $interval = @$date_from->diff($date_to) ?? 0;
        //     $jumlah_tahun = floor($interval->days / 365);

        //     $total_tunjangan = $general_masa_kerja * pow(2, $jumlah_tahun);
        //     if($total_tunjangan > 0){
        //         $defaultColumns[] = [
        //             'label'    => "Tunjangan Masa Kerja ($jumlah_tahun)",
        //             'factor'   => '+',
        //             'value'    => $total_tunjangan,
        //             'type'     => 'Bulanan',
        //             'can_adjust' => 1
        //         ];
        //     }
        // }

        $potongan = m_standart_gaji_det::whereHas('m_standart_gaji', function ($q) {
            $q->where('desc', 'NOMINAL POTONGAN')
                ->orWhere('kode', 'SG-112025000503');
        })->get();
        // dd($potongan);

        $t_bonus = t_bonus::where('m_kary_id', @$kary->id ?? 0)
            ->whereRaw("date_from >= ? and date_to <= ?", [$periode_awal, $periode_akhir])
            ->where('status', 'POSTED')
            ->get();

        if (count($t_bonus)) {
            foreach ($t_bonus as $d) {
                $defaultColumns[] = [
                    'label' => "Bonus - $d->nomor ($d->keterangan)",
                    'factor' => '+',
                    'value' => (int) $d->nilai,
                    'type' => 'BULANAN',
                    'can_adjust' => 1,
                ];
            }
        }

        // check kehadiran karyawan
        $rekap = $this->hitungRekap(@$kary->id, $firstDayOfMonth, $lastDayOfMonth);
        $this->currentRekap = $rekap;

        if ($rekap) {
            $not_attend = $rekap["hari_kerja"] - ($rekap["jumlah_hadir"] + $rekap["jumlah_cuti"] + $rekap['tidak_absen_pulang']);
            $not_complete = $rekap['tidak_absen_pulang'];

            $total_lembur_hari_biasa = ceil(
                $rekap["total_menit_lembur_kerja"] / 60
            );

            $total_lembur_hari_libur = ceil(
                $rekap["total_menit_lembur_libur"] / 60
            );

            $total_terlambat = $rekap["total_jam_terlambat"];

            //komponen gaji yang harian
            if (isset($standart_gaji_det)) {
                foreach ($standart_gaji_det as $d) {
                    if ($d->periode === 'harian' && stripos($d->komponen, 'terlambat') === false) {
                        if ($d->tipe_komponen === 'NOMINAL') {
                            $defaultColumns[] = [
                                'label' => $d->komponen . ' (' . $rekap['jumlah_hadir'] . ' Hari)',
                                'factor' => $d->faktor,
                                'value' => (int) ($d->nilai * $rekap['jumlah_hadir']),
                                'type' => $d->periode,
                                'can_adjust' => 1

                            ];
                        } else {
                            $value = (int) ($d->nilai / 100 * $gaji_pokok);
                            $defaultColumns[] = [
                                'label' => $d->komponen . ' (' . $rekap['jumlah_hadir'] . ' Hari)',
                                'factor' => $d->faktor,
                                'value' => $value * $rekap['jumlah_hadir'],
                                'type' => $d->periode,
                                'can_adjust' => 1
                            ];
                        }
                    }
                }
            }

            //hitung telat
            // dd($rekap['detail_menit_terlambat']);
            if (!empty($rekap['detail_menit_terlambat'])) {
                foreach ($rekap['detail_menit_terlambat'] as $tanggal => $menit) {
                    $minutes = (int) $menit;
                    if ($minutes <= 0) {
                        continue;
                    }

                    // Ambil komponen “terlambat” dari standar gaji
                    // $komponenTerlambatList = collect($standart_gaji_det ?? [])
                    //     ->filter(function ($item) {
                    //         return stripos($item->komponen, "terlambat") !== false;
                    //     })
                    //     ->sortBy(function ($item) {
                    //         preg_match("/\d+/", $item->komponen, $m);
                    //         return isset($m[0]) ? (int) $m[0] : 0;
                    //     })
                    //     ->values();

                    $komponenTerlambatList = collect($potongan ?? [])
                        ->filter(function ($item) {
                            return stripos($item->komponen, "terlambat") !== false;
                        })
                        ->sortBy(function ($item) {
                            preg_match("/\d+/", $item->komponen, $m);
                            return isset($m[0]) ? (int) $m[0] : 0;
                        })
                        ->values();

                    $value = 0;
                    $label = '';

                    // if ($komponenTerlambatList->isNotEmpty()) {
                    //     // Cari komponen yang sesuai batas menit
                    //     $komponen = $komponenTerlambatList->first(function ($item) use ($minutes) {
                    //         preg_match("/\d+/", $item->komponen, $m);
                    //         $batas = isset($m[0]) ? (int) $m[0] : 0;
                    //         return $minutes <= $batas;
                    //     });

                    //     if (!$komponen) {
                    //         $komponen = $komponenTerlambatList->last();
                    //     }

                    //     $value = (int) ($komponen->nilai ?? 0);
                    //     $label = $komponen->komponen;
                    //     $factor = $komponen->faktor ?? '-';
                    // } else {
                    //     // Tidak ada komponen di DB → pakai aturan default manual
                    //     if ($minutes <= 15) {
                    //         $value = 5000;
                    //     } elseif ($minutes <= 30) {
                    //         $value = 10000;
                    //     } elseif ($minutes <= 45) {
                    //         $value = 15000;
                    //     } elseif ($minutes <= 60) {
                    //         $value = 25000;
                    //     }elseif($minutes > 60) {
                    //         $value = 100000;
                    //     }
                    //     // else {
                    //     //     $extra = $minutes - 90;
                    //     //     $blocks = (int) ceil($extra / 30);
                    //     //     $value = 25000 + ($blocks * 10000);
                    //     // }

                    //     $label = "Potongan Terlambat";
                    //     $factor = '-';
                    // }

                    $nominalTerlambat_raw = m_general::where('group', 'NOMINAL TERLAMBAT')
                        ->where('is_active', true);

                    if ($komponenTerlambatList->isNotEmpty()) {
                        // Cari komponen yang sesuai batas menit
                        $komponen = $komponenTerlambatList->first(function ($item) use ($minutes) {
                            preg_match("/\d+/", $item->komponen, $m);
                            $batas = isset($m[0]) ? (int) $m[0] : 0;
                            return $minutes <= $batas;
                        });

                        if (!$komponen) {
                            $komponen = $komponenTerlambatList->last();
                        }

                        $value = (int) ($komponen->nilai ?? 0);
                        $label = $komponen->komponen;
                        $factor = $komponen->faktor ?? '-';
                    } elseif ($nominalTerlambat_raw->exists()) {
                        $nominalRules = $nominalTerlambat_raw->orderBy('value', 'asc')->get();

                        $rule = $nominalRules->first(function ($item) use ($minutes) {
                            return $minutes <= (int) $item->value;
                        });

                        if (!$rule) {
                            $rule = $nominalRules->last();
                        }

                        $value = (int) ($rule->value_2 ?? 0);
                        $label = "Potongan Terlambat";
                        $factor = '-';
                    } else {
                        if ($minutes <= 15) {
                            $value = 5000;
                        } elseif ($minutes <= 30) {
                            $value = 10000;
                        } elseif ($minutes <= 45) {
                            $value = 15000;
                        } elseif ($minutes <= 60) {
                            $value = 25000;
                        } elseif ($minutes > 60) {
                            $value = 100000;
                        }

                        $label = "Potongan Terlambat";
                        $factor = '-';
                    }

                    $defaultColumns[] = [
                        'label' => $label . ' ' . $tanggal . ' (' . $minutes . ' Menit)',
                        'factor' => $factor,
                        'value' => (int) $value,
                        'type' => 'Harian',
                        'can_adjust' => 1,
                    ];
                }
            }

            //hitung tidak masuk
            $periode_gaji = strtoupper(\App\Models\BasicModels\m_general::find($kary->periode_gaji_id)?->value ?? 'BULANAN');

            if ($gaji_pokok > 0) {
                $gaji_harian = (int) ($gaji_pokok / 25);
            } else {
                $gaji_harian = 0;
            }

            if ($not_attend > 0) {
                if ($periode_gaji === 'HARIAN') {
                    $value = (int) ($gaji_harian * (int) $not_attend);
                    $defaultColumns[] = [
                        'label' => 'Potongan Tidak Masuk Kerja (' . $not_attend . ' Hari)',
                        'factor' => '-',
                        'value' => (int) $value,
                        'type' => 'Harian',
                        'can_adjust' => 1
                    ];
                } else { // BULANAN
                    $value = (int) ($gaji_harian * 1.5 * (int) $not_attend);
                    $defaultColumns[] = [
                        'label' => 'Potongan Tidak Masuk Kerja (' . $not_attend . ' Hari)',
                        'factor' => '-',
                        'value' => (int) $value,
                        'type' => 'Bulanan',
                        'can_adjust' => 1
                    ];
                }
            }

            if ($not_complete > 0) {
                $value = 100000 * $not_complete;
                $defaultColumns[] = [
                    'label' => 'Potongan Absen Tidak Lengkap (' . $not_complete . ' Hari)',
                    'factor' => '-',
                    'value' => (int) $value,
                    'type' => 'Harian',
                    'can_adjust' => 1
                ];
            }

            //hitung lembur
            if ($total_lembur_hari_biasa > 0) {
                $upahPerJam = 10000;
                $value = $total_lembur_hari_biasa * $upahPerJam;

                // $jamPertama = min(1, $total_lembur_hari_biasa);
                // $jamBerikutnya = max(0, $total_lembur_hari_biasa - 1);

                // $value =
                //     $jamPertama * 1.5 * $upahPerJam +
                //     $jamBerikutnya * 2 * $upahPerJam;

                $defaultColumns[] = [
                    "label" => "Upah Lembur Hari Kerja ($total_lembur_hari_biasa Jam)",
                    "factor" => "+",
                    "value" => $value,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                ];
            }

            if ($total_lembur_hari_libur > 0) {
                $upahPerJam = 12500;
                $value = $total_lembur_hari_libur * $upahPerJam;

                // $jamPertama = min(7, $total_lembur_hari_libur);
                // $jamKedelapan = $total_lembur_hari_libur > 7 ? 1 : 0;
                // $jamSisanya = max(0, $total_lembur_hari_libur - 8);

                // $value =
                //     $jamPertama * 2 * $upahPerJam +
                //     $jamKedelapan * 3 * $upahPerJam +
                //     $jamSisanya * 4 * $upahPerJam;

                $defaultColumns[] = [
                    "label" => "Biaya Lembur Hari Libur ($total_lembur_hari_libur Jam)",
                    "factor" => "+",
                    "value" => $value,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                ];
            }
        }

        // faktor lain :Potongan
        // $t_potongan = t_potongan::where('m_kary_id', @$kary->id ?? 0)->orWhere('is_all_kary', true)->whereRaw("date_from >= ? and date_to <= ?",[$firstDayOfMonth,$lastDayOfMonth])->get();
        // if(count($t_potongan)) {
        //     foreach($t_potongan as $d){
        //         $nilai_netto = ((float)$d->nilai * (float)$d->percentage)/100;
        //         $defaultColumns[] = [
        //             'label'    => "Potongan - $d->nomor",
        //             'factor'   => '-',
        //             'value'    => $nilai_netto,
        //             'type'     => 'Bulanan',
        //             'can_adjust' => 1,
        //             't_potongan_id' => $d->id
        //         ];
        //     }
        // }

        return $defaultColumns;
    }

    private function countPPH21($kary, $netto = 0)
    {
        $getBasicSalary = [];
        // pengurangan dari perhitungan pph21
        // ------------------------- contoh perhitungan ---------------------------
        // Penghasilan Neto dalam setahun Rp9.400.000 x 12	    = Rp112.800.000
        // PTKP Status Lajang	                                = Rp54.000.000 (-)
        // Pendapatan Kena Pajak (PKP):	
        // PKP setahun Rp112.800.000 – Rp54.000.000	            = Rp58.800.000

        $tanggungan = m_general::find($kary->tanggungan_id);
        if ($tanggungan) {

            // persentase pajak <= Rp50.000.000                 = 5%
            // persentase pajak > Rp50.000.000  – Rp250.000.000 = 15%
            // persentase pajak > Rp250.000.000 – Rp500.000.000 = 25%
            // persentase pajak > Rp250.000.000 – Rp500.000.000 = 30%

            $nilaiTanggungan = @$tanggungan->value_2 ?? 0;
            $nettoYear = $netto * 12;
            $nettoPTKP = $nettoYear - $nilaiTanggungan;

            // hentikan fungsi ketika gaji masih dibawah jumlah tanggungan 
            if ($nettoPTKP <= 0)
                return $getBasicSalary;

            $percent = 0;
            if ($nettoPTKP <= 50000000) {
                $before_value = 0;
                $before_percent = $percent;
                $percent = 5;
            } elseif (
                $nettoPTKP > 50000000
                && $nettoPTKP <= 250000000
            ) {
                $before_value = 50000000;
                $before_percent = $percent;
                $percent = 15;

            } elseif ($nettoPTKP > 250000000 && $nettoPTKP <= 500000000) {
                $before_value = 250000000;
                $before_percent = $percent;
                $percent = 25;

            } elseif ($nettoPTKP > 500000000) {
                $before_value = 500000000;
                $before_percent = $percent;
                $percent = 30;
            }
            $getBasicSalary = $this->countTaxDetail(
                $tanggungan,
                $nettoPTKP,
                $before_value,
                $before_percent,
                $percent,
                $getBasicSalary
            );
        }
        return $getBasicSalary;
    }

    private function countTaxDetail(
        $tanggungan,
        $nettoPTKP,
        $before_value,
        $before_percent,
        $percent,
        $mergingArr
    ) {
        $outstanding = $nettoPTKP - $before_value;
        $tax1 = $before_percent * $before_value / 100;
        $tax2 = $percent * $outstanding / 100;
        $total_tax = $tax1 + $tax2;
        // insert dari kondisi gaji sebelumnya sebelumnya
        // ex: 5% x 50.000.000
        // ex: 15% x 800.0000
        $detail = [];
        if ($before_percent != 0) {
            // jika netto / before value memiliki sisa
            $detail = [
                [
                    'label' => "$before_percent% x $before_value",
                    'factor' => '+',
                    'value' => $tax1,
                    'type' => 'Tahunan'
                ],
                [
                    'label' => "$percent% x $outstanding",
                    'factor' => '+',
                    'value' => $tax2,
                    'type' => 'Tahunan'
                ]
            ];
        } else {
            // jika netto / before value tidak memiliki sisa (konidisi pertama)
            $detail = [
                [
                    'label' => "$percent% x $nettoPTKP",
                    'factor' => '+',
                    'value' => $tax2,
                    'type' => 'Tahunan',
                ]
            ];
        }

        $mergingArr[] = [
            'label' => "PTKP $tanggungan->value (perbulan)",
            'factor' => '-',
            'value' => $total_tax / 12,
            'type' => 'Bulanan',
            'can_adjust' => 0,
            'detail' => $detail
        ];
        return $mergingArr;
    }

    private function summarySubSalary($arrConfig)
    {
        return array_reduce($arrConfig, function ($carry, $item) {
            if (is_numeric($item['value'])) {
                $value = (float) $item['value'];
                if ($value != 0) {
                    if ($item['factor'] == '+') {
                        $carry = $carry + $item['value'];
                    } elseif ($item['factor'] == '-') {
                        $carry = $carry - $item['value'];
                    }
                }
            }
            return $carry;
        }, 0);
    }

    public function salaryOfKary($id, $periode_awal, $periode_akhir)
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
            $m_standart_gaji = m_standart_gaji::find($kary->m_standart_gaji_id) ?? null;
            // default summary salary
            $getBasicSalary = $this->factorSalary($m_standart_gaji, $kary, $periode_awal, $periode_akhir);
            $netto = $this->summarySubSalary($getBasicSalary);
            // $getBasicSalary    = array_merge($getBasicSalary, [
            //     [
            //         'label'    => 'Total Gaji',
            //         'factor'   => '=',
            //         'value'    => $netto,
            //         'type'     => '-'
            //     ]
            // ]);

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
                'm_kary_id' => $m_kary_id,
                'total_gaji' => $netto,
                'total_tax' => 0,
                'netto' => $nettoFinish,
                'detail' => $getBasicSalary,
                'rekap' => $this->currentRekap ?? null
            ];
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function generateSalary()
    {
        try {
            $req = app()->request;
            $date_from = Carbon::parse($req->periode_awal);
            $date_to = Carbon::parse($req->periode_akhir);

            $kary = m_kary::selectRaw("m_kary.*,m_general.value periode_text, m_dir.nama dir, m_divisi.nama divisi")
                ->leftJoin('m_dir', 'm_dir.id', 'm_kary.m_dir_id')
                ->leftJoin('m_divisi', 'm_divisi.id', 'm_kary.m_divisi_id')
                // ->leftJoin('m_dept','m_dept.id','m_kary.m_divisi_id')
                ->join('m_general', 'm_general.id', 'm_kary.periode_gaji_id')
                ->join('m_kary_det_kontrak as dk', 'dk.m_karyawan_id', 'm_kary.id')
                ->whereDate('dk.tgl_awal', '<=', $date_to)
                ->whereRaw('m_kary.m_standart_gaji_id in(select s.id from m_standart_gaji s where s.is_active = true)')
            ;

            if ($req->m_dir_id)
                $kary = $kary->where('m_kary.m_dir_id', $req->m_dir_id);
            if ($req->m_divisi_id)
                $kary = $kary->where('m_kary.m_divisi_id', $req->m_divisi_id);
            if ($req->m_kary_id)
                $kary = $kary->where('m_kary.id', $req->m_kary_id);


            $kary = $kary->get();

            // dd($date_from, $date_to);

            // Menghitung jumlah bulan antara tanggal_awal dan tanggal_akhir
            $interval = $date_from->diff($date_to);
            $jumlah_bulan = (($interval->y) * 12) + ($interval->m);

            $data = [];
            for ($i = 0; $i <= $jumlah_bulan; $i++) {

                $date = $date_from;
                // dd($date);
                foreach ($kary as $key) {
                    $gaji = $this->salaryOfKary($key->id, $date_from, $date_to);
                    // dd($gaji);
                    if (!is_array($gaji)) {
                        $gaji = [
                            'total_tax' => 0,
                            'total_gaji' => 0,
                            'netto' => 0,
                            'detail' => [],
                        ];
                    }
                    $data[] = [
                        'm_kary_id' => $key->id,
                        'm_kary.nik' => $key->kode,
                        'm_kary_dir_id' => $key->m_dir_id,
                        'm_kary_dir.nama' => $key->dir,
                        'm_kary_divisi_id' => $key->m_divisi_id,
                        'm_kary_divisi.nama' => $key->divisi,
                        // 'm_kary_dept_id'    => $key->m_dept_id,
                        // 'm_kary_dept.nama'  => $key->dept,
                        'nik' => $key->kode,
                        'nama_lengkap' => $key->nama_depan,
                        'periode' => $date_from->format('d-m-Y'),
                        // 'periode'           => $date_from . ' - ' . $date_to,
                        'periode_in_date' => $date_to,
                        'periode_id' => $key->periode_gaji_id,
                        'periode_text' => $key->periode_text,
                        'total_tax' => $gaji['total_tax'] ?? 0,
                        'total_gaji' => $gaji['total_gaji'],
                        'netto' => $gaji['netto'],
                        'detail_gaji' => $gaji['detail'],
                        'rekap' => $gaji['rekap'] ?? null,
                    ];
                }

                // Menambahkan satu bulan untuk iterasi berikutnya
                // $date_from->add(new \DateInterval('P1M'));
            }


            return $this->helper->customResponse('OK', 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function public_generate()
    {
        $data = $this->salaryOfKary(app()->request->id ?? 8, '2024-03');

        return response(['msg' => $data]);
    }

    public function custom_generate()
    {
        return $this->generateSalary();
    }

    public function custom_generatePPH($req)
    {
        $netto = $req->netto;
        $kary = m_kary::find($req->m_kary_id);
        return $this->countPPH21($kary, $netto);
    }

    public function custom_save($req)
    {
        $counter = count($req->detail);
        if ($counter) {
            $nomor = $this->helper->generateNomor('KODE PERHITUNGAN GAJI');
            foreach ($req->detail as $key) {
                $checkAndDelete = $this->where('m_kary_id', @$key['m_kary_id'])->where('periode', @$key['periode'])->delete();
                $key['nomor'] = $nomor;
                $key['detail_gaji'] = json_encode($key['detail_gaji']);
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


        $model = $model->whereBetween('periode_in_date', [$date_from, $date_to])
            ->whereHas('m_kary', function ($q) {
                $q->where('is_active', true)
                    ->orWhere('is_active', 1)
                    ->orWhere('is_active', '1');
            });

        if ($req->m_divisi_id)
            $model = $model->where('t_perhitungan_gaji.m_kary_divisi_id', $req->m_divisi_id);
        if ($req->m_dir_id)
            $model = $model->where('t_perhitungan_gaji.m_kary_dir_id', $req->m_dir_id);
        if ($req->m_kary_id)
            $model = $model->where('t_perhitungan_gaji.m_kary_id', $req->m_kary_id);


        return $model;
    }

    private function hitungRekap($kary_id, $start, $end): array
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $m_kary = m_kary::findOrFail($kary_id);

        $tgl_masuk = $m_kary->tgl_masuk ? Carbon::parse($m_kary->tgl_masuk) : null;
        if ($tgl_masuk && $tgl_masuk->greaterThan($start)) {
            $start = clone $tgl_masuk;
        }

        $period = CarbonPeriod::create($start, $end);

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


        $t_jadwal_kerja_det_hari = t_jadwal_kerja_det_hari::whereHas('t_jadwal_kerja', function ($q) use ($m_kary) {
            $q->where('id', $m_kary->t_jadwal_kerja_id);
        })
            ->get()
            ->keyBy(
                fn($item) => ($item->day)
            );

        // dd($t_jadwal_kerja_det_hari);

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
        // dd($cutiDates);

        // --- Hitung Rekap ---
        $hasil = [];
        $jumlah_hadir = 0;
        $total_menit_lembur_kerja = 0;
        $total_menit_lembur_libur = 0;
        $total_menit_terlambat = 0;
        $detail_menit_terlambat = [];
        $total_jam_terlambat = 0;
        $total_jam_tidak_hadir = 0;
        $tidak_absen_pulang = 0;

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
                $jadwal = $t_jadwal_kerja_det_hari[$tanggal->translatedFormat('l')];
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
            if ($status === "ATTEND" && $tipe === 'KERJA') {
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

            // --- hitung terlambat yang lama---
            // dd($data);
            // if (
            //     $data &&
            //     $data->checkin_time &&
            //     $data->t_jadwal_kerja_det_hari?->waktu_mulai
            // ) {
            //     $checkin = Carbon::parse($data->checkin_time);
            //     $jadwalMulai = Carbon::parse(
            //         $data->t_jadwal_kerja_det_hari->waktu_mulai
            //     );

            //     if ($checkin->greaterThan($jadwalMulai)) {
            //         $menit_terlambat = $jadwalMulai->diffInMinutes(
            //             $checkin
            //         );

            //         $total_menit_terlambat += $jadwalMulai->diffInMinutes(
            //             $checkin
            //         );

            //         $total_jam_terlambat += ($menit_terlambat);
            //         $detail_menit_terlambat[$key] = ($menit_terlambat);
            //     }
            // }

            //hitung terlambat yang baru
            if ($data && $data->checkin_time && $status === 'ATTEND') {
                $hariIndex = Carbon::parse($data->tanggal)->translatedFormat('l');
                $jadwalMulai = $data->t_jadwal_kerja_det_hari?->waktu_mulai;
                if (!$jadwalMulai) {
                    $jadwalMulai = $t_jadwal_kerja_det_hari[$hariIndex]->waktu_mulai ?? null;
                }

                if ($jadwalMulai) {
                    $checkin = Carbon::parse($data->checkin_time);
                    $jadwalMulai = Carbon::parse($jadwalMulai);

                    if ($checkin->greaterThan($jadwalMulai)) {
                        $menit_terlambat = $jadwalMulai->diffInMinutes($checkin);

                        $total_menit_terlambat += $menit_terlambat;
                        $total_jam_terlambat += $menit_terlambat;
                        $detail_menit_terlambat[$key] = $menit_terlambat;
                    }
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

            if ($tipe === "KERJA" && $status === "WORKING") {
                $tidak_absen_pulang++;
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
            "tidak_absen_pulang" => $tidak_absen_pulang,
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