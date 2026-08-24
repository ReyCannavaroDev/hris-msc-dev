<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

    $defaultColumns = [];

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
      $hari_kerja_full = $rekap['hari_kerja'] ?? 25;
      $hari_belum_join = $rekap['hari_belum_join'] ?? 0;
      $not_attend      = $rekap['not_attend'] ?? 0;
      $hari_efektif    = $hari_kerja_full - $hari_belum_join;

      $not_complete = $rekap['tidak_absen_pulang'];

      $total_lembur_hari_biasa = floor(
        $rekap["total_menit_lembur_kerja"] / 60
      );

      $total_lembur_hari_libur = floor(
        $rekap["total_menit_lembur_libur"] / 60
      );

      $total_terlambat = $rekap["total_jam_terlambat"];

      $periode_gaji_type = strtoupper(\App\Models\BasicModels\m_general::find($kary->periode_gaji_id)?->value ?? 'BULANAN');
      $gaji_harian_val = ($gaji_pokok > 0) ? (int) ($gaji_pokok / 25) : 0;

      if ($periode_gaji_type === 'HARIAN') {
        $base_gaji = $hari_efektif * $gaji_harian_val;
        array_unshift($defaultColumns, [
          'label' => "Gaji Pokok (Berdasarkan $hari_efektif Hari Kerja x Rp " . number_format($gaji_harian_val,0,',','.') . ")",
          'factor' => '+',
          'value' => (int) $base_gaji,
          'type' => 'gaji_pokok_periode',
          'can_adjust' => 1
        ]);
      } else {
      array_unshift($defaultColumns, [
        'label' => 'Gaji Pokok (Bulanan)',
        'factor' => '+',
        'value' => (float) $gaji_pokok,
        'type' => 'gaji_pokok_periode',
        'can_adjust' => 1
      ]);

      if ($hari_belum_join > 0) {
        $defaultColumns[] = [
        'label' => "Penyesuaian Tanggal Masuk ($hari_belum_join Hari x Rp " . number_format($gaji_harian_val,0,',','.') . ")",
        'factor' => '-',
        'value' => (int) ($hari_belum_join * $gaji_harian_val),
        'type' => 'Bulanan',
        'can_adjust' => 1
      ];
    }
  }

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
    'label' => "Potongan Tidak Masuk Kerja ($not_attend Hari x Rp " . number_format($gaji_harian,0,',','.') . ")",
    'factor' => '-',
    'value' => (int) $value,
    'type' => 'Harian',
    'can_adjust' => 1
  ];
} else { // BULANAN
$value = (int) ($gaji_harian * 1.5 * (int) $not_attend);
$defaultColumns[] = [
'label' => "Potongan Tidak Masuk Kerja ($not_attend Hari x Rp " . number_format($gaji_harian,0,',','.') . " x 1.5)",
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
  $upahPerJam = 10000;
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
              ->where('dk.status', true)
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

                    $tgl_masuk = $m_kary->tgl_masuk 
                        ? Carbon::parse($m_kary->tgl_masuk) 
                        : (optional($m_kary->m_kary_det_kontrak()->orderBy('id')->first())->tgl_awal 
                            ? Carbon::parse($m_kary->m_kary_det_kontrak()->orderBy('id')->first()->tgl_awal) 
                            : null);
                    // We DO NOT override $start here, so the period is the full calendar month.
                    // if ($tgl_masuk && $tgl_masuk->greaterThan($start)) {
                      //     $start = clone $tgl_masuk;
                      // }

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
                    $hari_belum_join = 0;
                    $not_attend_days = 0;

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

                    // --- cek hari sebelum join ---
                    $is_before_join = false;
                    if ($tgl_masuk && $tanggal->copy()->startOfDay()->lessThan($tgl_masuk->copy()->startOfDay())) {
                      $is_before_join = true;
                    }

                    // --- hitung hadir ---
                    if ($status === "ATTEND" && $tipe === 'KERJA' && !$is_before_join) {
                      $jumlah_hadir++;
                    }

                    // --- hitung lembur ---
                    if ($data && $data->checkout_time && $status === "ATTEND" && !$is_before_join) {
                      $hariIndex = Carbon::parse($data->tanggal)->translatedFormat('l');
                      $jadwalAkhir = $data->t_jadwal_kerja_det_hari?->waktu_akhir;
                      if (!$jadwalAkhir) {
                        $jadwalAkhir = isset($t_jadwal_kerja_det_hari[$hariIndex]) ? $t_jadwal_kerja_det_hari[$hariIndex]->waktu_akhir : null;
                      }

                      if ($jadwalAkhir) {
                        $checkoutTime = Carbon::parse($data->checkout_time);
                        $jadwalAkhirTime = Carbon::parse($jadwalAkhir);

                        if ($checkoutTime->greaterThan($jadwalAkhirTime)) {
                          $menit = $jadwalAkhirTime->diffInMinutes($checkoutTime);
                          $jam_lembur_hari = floor($menit / 60);
                          if ($jam_lembur_hari >= 1) {
                            if ($tipe === "KERJA") {
                              $total_menit_lembur_kerja += $jam_lembur_hari * 60;
                            } else {
                            $total_menit_lembur_libur += $jam_lembur_hari * 60;
                          }
                        }
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
                              if ($data && $data->checkin_time && $status === 'ATTEND' && !$is_before_join) {
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
                                if ($is_before_join) {
                                  $hari_belum_join++;
                                } else {
                                $not_attend_days++;
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
                          }

                          if ($tipe === "KERJA" && $status === "WORKING" && !$is_before_join) {
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
                        "hari_belum_join" => $hari_belum_join,
                        "not_attend" => $not_attend_days,
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

    public function public_exportBpjsKesehatan()
    {
        try {
            $req = request();
            $monthStr = $req->month ?: Carbon::now()->format('Y-m');
            $date_start = Carbon::parse($monthStr . '-01')->format('Y-m-d');
            $date_end   = Carbon::parse($date_start)->endOfMonth()->format('Y-m-d');

            $karyQuery = m_kary::with(['m_dir', 'm_divisi', 'm_dept', 'm_standart_gaji']);

            if ($req->filled('m_kary_id')) {
                $ids = array_map('intval', explode(',', $req->m_kary_id));
                $karyQuery->whereIn('id', $ids);
            } else {
                if ($req->filled('m_divisi_id')) $karyQuery->where('m_divisi_id', $req->m_divisi_id);
                if ($req->filled('m_dir_id')) $karyQuery->where('m_dir_id', $req->m_dir_id);
            }

            if ($req->filled('is_active')) {
                $isActive = filter_var($req->is_active, FILTER_VALIDATE_BOOLEAN);
                $karyQuery->where('is_active', $isActive);
            }

            $karyawans = $karyQuery->get();
            $karyawanIds = $karyawans->pluck('id')->toArray();

            if (empty($karyawanIds)) {
                throw new \Exception("Tidak ada data karyawan yang sesuai dengan filter.");
            }

            $kartuList = DB::table('m_kary_det_kartu')
                ->whereIn('m_kary_id', $karyawanIds)
                ->get()
                ->keyBy('m_kary_id');

            $rows = [];
            $no = 1;
            $capMaxKes = 12000000;

            foreach ($karyawans as $kary) {
                $kartu = $kartuList[$kary->id] ?? null;
                $noBpjs = $kartu->bpjs_no_kesehatan ?? $kartu->bpjs_no ?? '-';

                $gajiPokok = (float) ($kary->m_standart_gaji->gaji_pokok ?? 0);
                $tunjTetap = (float) ($kary->m_standart_gaji->tunjangan_tetap ?? 0);
                $upahDasar = $gajiPokok + $tunjTetap;

                $dasarHitung = min($upahDasar, $capMaxKes);

                $iuranPerusahaan = round(0.04 * $dasarHitung);
                $iuranKaryawan   = round(0.01 * $dasarHitung);
                $totalIuran      = $iuranPerusahaan + $iuranKaryawan;

                $rows[] = [
                    'NO' => $no++,
                    'ID KARYAWAN' => $kary->kode ?? '-',
                    'NAMA KARYAWAN' => $kary->nama_lengkap ?? '-',
                    'UNIT' => $kary->m_dir->nama ?? '-',
                    'DIVISI' => $kary->m_divisi->nama ?? '-',
                    'NO BPJS KESEHATAN' => $noBpjs,
                    'DASAR UPAH' => $upahDasar,
                    'IURAN PERUSAHAAN (4%)' => $iuranPerusahaan,
                    'IURAN KARYAWAN (1%)' => $iuranKaryawan,
                    'TOTAL IURAN (5%)' => $totalIuran,
                ];
            }

            if ($req->export === 'html' || strtolower($req->tipe ?? '') === 'html') {
                $html = '<div class="table-responsive p-3" style="background:#fff; font-family:sans-serif; overflow-x:auto;">';
                $html .= '<h3 style="margin-bottom:4px; font-weight:bold; font-size:18px; color:#1f2937;">LAPORAN BPJS KESEHATAN</h3>';
                $html .= '<p style="color:#6b7280; font-size:13px; margin-top:0; margin-bottom:16px;">Periode: ' . htmlspecialchars($monthStr) . '</p>';
                $html .= '<table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">';
                $html .= '<thead><tr style="background:#005FBF; color:#ffffff;">';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db; text-align:center;">No</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db;">ID Karyawan</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db;">Nama Karyawan</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db;">Unit</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db;">Jabatan</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db;">No BPJS Kes</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db; text-align:right;">Dasar Upah</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db; text-align:right;">Iuran Perush (4%)</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db; text-align:right;">Iuran Kary (1%)</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #d1d5db; text-align:right;">Total Iuran (5%)</th>';
                $html .= '</tr></thead><tbody>';

                $totUpah = 0; $totPerush = 0; $totKary = 0; $totAll = 0;
                foreach ($rows as $r) {
                    $totUpah += $r['DASAR UPAH'];
                    $totPerush += $r['IURAN PERUSAHAAN (4%)'];
                    $totKary += $r['IURAN KARYAWAN (1%)'];
                    $totAll += $r['TOTAL IURAN (5%)'];

                    $html .= '<tr style="border-bottom:1px solid #e5e7eb;">';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb; text-align:center;">' . $r['NO'] . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['ID KARYAWAN']) . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb; font-weight:600;">' . htmlspecialchars($r['NAMA KARYAWAN']) . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['UNIT']) . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['DIVISI']) . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['NO BPJS KESEHATAN']) . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['DASAR UPAH'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb; text-align:right; color:#16a34a; font-weight:500;">Rp ' . number_format($r['IURAN PERUSAHAAN (4%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb; text-align:right; color:#dc2626; font-weight:500;">Rp ' . number_format($r['IURAN KARYAWAN (1%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px; border:1px solid #e5e7eb; text-align:right; font-weight:700;">Rp ' . number_format($r['TOTAL IURAN (5%)'], 0, ',', '.') . '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody><tfoot><tr style="background:#f9fafb; font-weight:bold; border-top:2px solid #9ca3af;">';
                $html .= '<td colspan="6" style="padding:10px 8px; border:1px solid #d1d5db; text-align:right;">TOTAL:</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totUpah, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #d1d5db; text-align:right; color:#16a34a;">Rp ' . number_format($totPerush, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #d1d5db; text-align:right; color:#dc2626;">Rp ' . number_format($totKary, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #d1d5db; text-align:right; color:#1e40af;">Rp ' . number_format($totAll, 0, ',', '.') . '</td>';
                $html .= '</tr></tfoot></table></div>';

                return response($html, 200)->header('Content-Type', 'text/html');
            }

            $export = new class(collect($rows)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return [
                        'NO', 'ID KARYAWAN', 'NAMA KARYAWAN', 'UNIT', 'DIVISI',
                        'NO BPJS KESEHATAN', 'DASAR UPAH', 'IURAN PERUSAHAAN (4%)',
                        'IURAN KARYAWAN (1%)', 'TOTAL IURAN (5%)'
                    ];
                }
                public function styles(Worksheet $sheet) {
                    $highestColumn = $sheet->getHighestColumn();
                    $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
                    return [];
                }
            };

            return Excel::download($export, "laporan_bpjs_kesehatan_{$monthStr}.xlsx");
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function public_exportBpjsKetenagakerjaan()
    {
        try {
            $req = request();
            $monthStr = $req->month ?: Carbon::now()->format('Y-m');
            $date_start = Carbon::parse($monthStr . '-01')->format('Y-m-d');
            $date_end   = Carbon::parse($date_start)->endOfMonth()->format('Y-m-d');

            $karyQuery = m_kary::with(['m_dir', 'm_divisi', 'm_dept', 'm_standart_gaji']);

            if ($req->filled('m_kary_id')) {
                $ids = array_map('intval', explode(',', $req->m_kary_id));
                $karyQuery->whereIn('id', $ids);
            } else {
                if ($req->filled('m_divisi_id')) $karyQuery->where('m_divisi_id', $req->m_divisi_id);
                if ($req->filled('m_dir_id')) $karyQuery->where('m_dir_id', $req->m_dir_id);
            }

            if ($req->filled('is_active')) {
                $isActive = filter_var($req->is_active, FILTER_VALIDATE_BOOLEAN);
                $karyQuery->where('is_active', $isActive);
            }

            $karyawans = $karyQuery->get();
            $karyawanIds = $karyawans->pluck('id')->toArray();

            if (empty($karyawanIds)) {
                throw new \Exception("Tidak ada data karyawan yang sesuai dengan filter.");
            }

            $kartuList = DB::table('m_kary_det_kartu')
                ->whereIn('m_kary_id', $karyawanIds)
                ->get()
                ->keyBy('m_kary_id');

            $rows = [];
            $no = 1;
            $capMaxJp = 10042300;

            foreach ($karyawans as $kary) {
                $kartu = $kartuList[$kary->id] ?? null;
                $noBpjsTk = $kartu->bpjs_no_ketenagakerjaan ?? $kartu->bpjs_no ?? '-';

                $gajiPokok = (float) ($kary->m_standart_gaji->gaji_pokok ?? 0);
                $tunjTetap = (float) ($kary->m_standart_gaji->tunjangan_tetap ?? 0);
                $upahDasar = $gajiPokok + $tunjTetap;

                $jkk = round(0.0024 * $upahDasar);
                $jkm = round(0.0030 * $upahDasar);

                $jhtPerusahaan = round(0.037 * $upahDasar);
                $jhtKaryawan   = round(0.020 * $upahDasar);

                $dasarJp = min($upahDasar, $capMaxJp);
                $jpPerusahaan  = round(0.020 * $dasarJp);
                $jpKaryawan    = round(0.010 * $dasarJp);

                $totalPerusahaan = $jkk + $jkm + $jhtPerusahaan + $jpPerusahaan;
                $totalKaryawan   = $jhtKaryawan + $jpKaryawan;
                $grandTotal      = $totalPerusahaan + $totalKaryawan;

                $rows[] = [
                    'NO' => $no++,
                    'ID KARYAWAN' => $kary->kode ?? '-',
                    'NAMA KARYAWAN' => $kary->nama_lengkap ?? '-',
                    'UNIT' => $kary->m_dir->nama ?? '-',
                    'DIVISI' => $kary->m_divisi->nama ?? '-',
                    'NO BPJS TK' => $noBpjsTk,
                    'DASAR UPAH' => $upahDasar,
                    'JKK (0.24%)' => $jkk,
                    'JKM (0.30%)' => $jkm,
                    'JHT PERUSAHAAN (3.7%)' => $jhtPerusahaan,
                    'JHT KARYAWAN (2%)' => $jhtKaryawan,
                    'JP PERUSAHAAN (2%)' => $jpPerusahaan,
                    'JP KARYAWAN (1%)' => $jpKaryawan,
                    'TOTAL DITANGGUNG PERUSAHAAN' => $totalPerusahaan,
                    'TOTAL DIPOTONG KARYAWAN' => $totalKaryawan,
                    'GRAND TOTAL' => $grandTotal,
                ];
            }

            if ($req->export === 'html' || strtolower($req->tipe ?? '') === 'html') {
                $html = '<div class="table-responsive p-3" style="background:#fff; font-family:sans-serif; overflow-x:auto;">';
                $html .= '<h3 style="margin-bottom:4px; font-weight:bold; font-size:18px; color:#1f2937;">LAPORAN BPJS KETENAGAKERJAAN</h3>';
                $html .= '<p style="color:#6b7280; font-size:13px; margin-top:0; margin-bottom:16px;">Periode: ' . htmlspecialchars($monthStr) . '</p>';
                $html .= '<table style="width:100%; border-collapse:collapse; font-size:11px; text-align:left;">';
                $html .= '<thead><tr style="background:#005FBF; color:#ffffff;">';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:center;">No</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">ID Kary</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">Nama Karyawan</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">Unit</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">Jabatan</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">No BPJS TK</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Dasar Upah</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">JKK (0.24%)</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">JKM (0.30%)</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">JHT Perush (3.7%)</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">JHT Kary (2%)</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">JP Perush (2%)</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">JP Kary (1%)</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; background:#047857;">Total Perush</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; background:#b91c1c;">Total Kary</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; background:#1e3a8a;">Grand Total</th>';
                $html .= '</tr></thead><tbody>';

                $totUpah = 0; $totJkk = 0; $totJkm = 0; $totJhtP = 0; $totJhtK = 0; $totJpP = 0; $totJpK = 0; $totPerush = 0; $totKary = 0; $totAll = 0;
                foreach ($rows as $r) {
                    $totUpah += $r['DASAR UPAH'];
                    $totJkk += $r['JKK (0.24%)'];
                    $totJkm += $r['JKM (0.30%)'];
                    $totJhtP += $r['JHT PERUSAHAAN (3.7%)'];
                    $totJhtK += $r['JHT KARYAWAN (2%)'];
                    $totJpP += $r['JP PERUSAHAAN (2%)'];
                    $totJpK += $r['JP KARYAWAN (1%)'];
                    $totPerush += $r['TOTAL DITANGGUNG PERUSAHAAN'];
                    $totKary += $r['TOTAL DIPOTONG KARYAWAN'];
                    $totAll += $r['GRAND TOTAL'];

                    $html .= '<tr style="border-bottom:1px solid #e5e7eb;">';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:center;">' . $r['NO'] . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['ID KARYAWAN']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; font-weight:600;">' . htmlspecialchars($r['NAMA KARYAWAN']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['UNIT']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['DIVISI']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['NO BPJS TK']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['DASAR UPAH'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['JKK (0.24%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['JKM (0.30%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['JHT PERUSAHAAN (3.7%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['JHT KARYAWAN (2%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['JP PERUSAHAAN (2%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['JP KARYAWAN (1%)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right; font-weight:600; color:#047857;">Rp ' . number_format($r['TOTAL DITANGGUNG PERUSAHAAN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right; font-weight:600; color:#b91c1c;">Rp ' . number_format($r['TOTAL DIPOTONG KARYAWAN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right; font-weight:700; color:#1e3a8a;">Rp ' . number_format($r['GRAND TOTAL'], 0, ',', '.') . '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody><tfoot><tr style="background:#f9fafb; font-weight:bold; border-top:2px solid #9ca3af;">';
                $html .= '<td colspan="6" style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">TOTAL:</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totUpah, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totJkk, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totJkm, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totJhtP, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totJhtK, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totJpP, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totJpK, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; color:#047857;">Rp ' . number_format($totPerush, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; color:#b91c1c;">Rp ' . number_format($totKary, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; color:#1e3a8a;">Rp ' . number_format($totAll, 0, ',', '.') . '</td>';
                $html .= '</tr></tfoot></table></div>';

                return response($html, 200)->header('Content-Type', 'text/html');
            }

            $export = new class(collect($rows)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return [
                        'NO', 'ID KARYAWAN', 'NAMA KARYAWAN', 'UNIT', 'DIVISI',
                        'NO BPJS TK', 'DASAR UPAH', 'JKK (0.24%)', 'JKM (0.30%)',
                        'JHT PERUSAHAAN (3.7%)', 'JHT KARYAWAN (2%)',
                        'JP PERUSAHAAN (2%)', 'JP KARYAWAN (1%)',
                        'TOTAL DITANGGUNG PERUSAHAAN', 'TOTAL DIPOTONG KARYAWAN',
                        'GRAND TOTAL'
                    ];
                }
                public function styles(Worksheet $sheet) {
                    $highestColumn = $sheet->getHighestColumn();
                    $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
                    return [];
                }
            };

            return Excel::download($export, "laporan_bpjs_ketenagakerjaan_{$monthStr}.xlsx");
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function public_exportPph21()
    {
        try {
            $req = request();
            $monthStr = $req->month ?: Carbon::now()->format('Y-m');
            $date_start = Carbon::parse($monthStr . '-01')->format('Y-m-d');
            $date_end   = Carbon::parse($date_start)->endOfMonth()->format('Y-m-d');

            $karyQuery = m_kary::with(['m_dir', 'm_divisi', 'm_dept', 'm_standart_gaji']);

            if ($req->filled('m_kary_id')) {
                $ids = array_map('intval', explode(',', $req->m_kary_id));
                $karyQuery->whereIn('id', $ids);
            } else {
                if ($req->filled('m_divisi_id')) $karyQuery->where('m_divisi_id', $req->m_divisi_id);
                if ($req->filled('m_dir_id')) $karyQuery->where('m_dir_id', $req->m_dir_id);
            }

            if ($req->filled('is_active')) {
                $isActive = filter_var($req->is_active, FILTER_VALIDATE_BOOLEAN);
                $karyQuery->where('is_active', $isActive);
            }

            $karyawans = $karyQuery->get();
            $karyawanIds = $karyawans->pluck('id')->toArray();

            if (empty($karyawanIds)) {
                throw new \Exception("Tidak ada data karyawan yang sesuai dengan filter.");
            }

            $kartuList = DB::table('m_kary_det_kartu')
                ->whereIn('m_kary_id', $karyawanIds)
                ->get()
                ->keyBy('m_kary_id');

            $generalIds = collect()
                ->merge($karyawans->pluck('tanggungan_id'))
                ->merge($karyawans->pluck('jk_id'))
                ->merge($karyawans->pluck('status_nikah_id'))
                ->filter()
                ->unique()
                ->toArray();
            $generalList = m_general::whereIn('id', $generalIds)->get()->keyBy('id');

            // Ambil Master Pengaturan PPh 21 aktif (dari tabel m_pph & m_pph_det)
            $masterPph = m_pph::with('m_pph_det')
                ->where('is_active', true)
                ->whereDate('tgl_pengaturan', '<=', $date_end)
                ->orderBy('tgl_pengaturan', 'desc')
                ->first();

            // Biaya Jabatan (%) dari master m_pph (default 5%)
            $costLevelPercent = ($masterPph && $masterPph->cost_level !== null) ? ((float)$masterPph->cost_level / 100) : 0.05;

            // Tier / Lapisan Tarif Pajak dari m_pph_det
            $tierList = $masterPph ? $masterPph->m_pph_det->sortBy('gaji_min')->values() : collect();

            $rows = [];
            $no = 1;

            foreach ($karyawans as $kary) {
                $kartu = $kartuList[$kary->id] ?? null;
                $npwpNo = $kartu->npwp_no ?? '-';
                $hasNpwp = (!empty($npwpNo) && $npwpNo != '-' && strlen(trim($npwpNo)) > 5);

                $tanggungan = $generalList[$kary->tanggungan_id] ?? null;
                $statusPtkp = $tanggungan->value ?? 'TK/0';

                // Hitung Nilai PTKP: Prioritaskan dari master m_pph jika tersedia
                if ($masterPph) {
                    $jkText = strtolower($generalList[$kary->jk_id]->value ?? 'pria');
                    $nikahText = strtolower($generalList[$kary->status_nikah_id]->value ?? 'single');
                    $isMenikah = (stripos($nikahText, 'nikah') !== false || stripos($nikahText, 'kawin') !== false);
                    $isWanita = (stripos($jkText, 'wanita') !== false || stripos($jkText, 'perempuan') !== false);

                    if ($isWanita) {
                        $basePtkp = (float) ($isMenikah ? $masterPph->besaran_nikah_wanita : $masterPph->besaran_single_wanita);
                    } else {
                        $basePtkp = (float) ($isMenikah ? $masterPph->besaran_nikah_pria : $masterPph->besaran_single_pria);
                    }

                    preg_match('/\d+/', $statusPtkp, $matches);
                    $jumlahTanggungan = isset($matches[0]) ? (int)$matches[0] : 0;
                    $dependantAmt = (float) ($masterPph->dependant_amt ?? 4500000);

                    $nilaiPtkpSetahun = $basePtkp + ($jumlahTanggungan * $dependantAmt);
                } else {
                    $nilaiPtkpSetahun = (float) (@$tanggungan->value_2 ?: 54000000);
                }

                $gajiPokok = (float) ($kary->m_standart_gaji->gaji_pokok ?? 0);
                $tunjTetap = (float) ($kary->m_standart_gaji->tunjangan_tetap ?? 0);
                $upahDasar = $gajiPokok + $tunjTetap;

                $jkk = round(0.0024 * $upahDasar);
                $jkm = round(0.0030 * $upahDasar);
                $bpjsKesPerusahaan = round(0.04 * min($upahDasar, 12000000));
                $premiBruto = $jkk + $jkm + $bpjsKesPerusahaan;

                $penghasilanBruto = $upahDasar + $premiBruto;

                // Biaya Jabatan dinamis dari master m_pph (maksimal Rp 500.000/bulan)
                $biayaJabatan = min(500000, round($costLevelPercent * $penghasilanBruto));
                $jhtKaryawan = round(0.02 * $upahDasar);
                $jpKaryawan  = round(0.01 * min($upahDasar, 10042300));
                $totalPengurang = $biayaJabatan + $jhtKaryawan + $jpKaryawan;

                $nettoBulanan = max(0, $penghasilanBruto - $totalPengurang);
                $nettoSetahun = $nettoBulanan * 12;

                $pkpSetahun = max(0, $nettoSetahun - $nilaiPtkpSetahun);

                // Hitung PPh 21 Setahun Menggunakan Bracket m_pph_det (atau Fallback Progresif UU HPP)
                $pph21Setahun = 0;
                if ($pkpSetahun > 0) {
                    if ($tierList->isNotEmpty()) {
                        $sisaPkp = $pkpSetahun;
                        foreach ($tierList as $tier) {
                            $min = (float) $tier->gaji_min;
                            $max = (float) $tier->gaji_max;
                            $ratePercent = $hasNpwp ? (float)$tier->npwp : (float)$tier->non_npwp;
                            $rate = $ratePercent / 100;

                            if ($max > 0) {
                                $rentang = $max - $min;
                                if ($sisaPkp > 0) {
                                    $pkpDiTier = min($sisaPkp, $rentang);
                                    $pph21Setahun += ($pkpDiTier * $rate);
                                    $sisaPkp -= $pkpDiTier;
                                }
                            } else {
                                if ($sisaPkp > 0) {
                                    $pph21Setahun += ($sisaPkp * $rate);
                                    $sisaPkp = 0;
                                }
                            }
                        }
                    } else {
                        if ($pkpSetahun <= 60000000) {
                            $pph21Setahun = $pkpSetahun * 0.05;
                        } elseif ($pkpSetahun <= 250000000) {
                            $pph21Setahun = (60000000 * 0.05) + (($pkpSetahun - 60000000) * 0.15);
                        } elseif ($pkpSetahun <= 500000000) {
                            $pph21Setahun = (60000000 * 0.05) + (190000000 * 0.15) + (($pkpSetahun - 250000000) * 0.25);
                        } elseif ($pkpSetahun <= 5000000000) {
                            $pph21Setahun = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + (($pkpSetahun - 500000000) * 0.30);
                        } else {
                            $pph21Setahun = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + (4500000000 * 0.30) + (($pkpSetahun - 5000000000) * 0.35);
                        }
                        if (!$hasNpwp) {
                            $pph21Setahun = $pph21Setahun * 1.20;
                        }
                    }
                }

                $pph21Bulanan = round($pph21Setahun / 12);

                $rows[] = [
                    'NO' => $no++,
                    'ID KARYAWAN' => $kary->kode ?? '-',
                    'NAMA KARYAWAN' => $kary->nama_lengkap ?? '-',
                    'NPWP' => $npwpNo,
                    'STATUS PTKP' => $statusPtkp,
                    'UNIT' => $kary->m_dir->nama ?? '-',
                    'DIVISI' => $kary->m_divisi->nama ?? '-',
                    'PENGHASILAN BRUTO' => $penghasilanBruto,
                    'BIAYA JABATAN & PENGURANG' => $totalPengurang,
                    'PENGHASILAN NETTO (BULAN)' => $nettoBulanan,
                    'PTKP SETAHUN' => $nilaiPtkpSetahun,
                    'PKP SETAHUN' => $pkpSetahun,
                    'PPH 21 TERUTANG (BULAN INI)' => $pph21Bulanan,
                ];
            }

            if ($req->export === 'html' || strtolower($req->tipe ?? '') === 'html') {
                $html = '<div class="table-responsive p-3" style="background:#fff; font-family:sans-serif; overflow-x:auto;">';
                $html .= '<h3 style="margin-bottom:4px; font-weight:bold; font-size:18px; color:#1f2937;">LAPORAN PPH 21 KARYAWAN</h3>';
                $html .= '<p style="color:#6b7280; font-size:13px; margin-top:0; margin-bottom:16px;">Periode: ' . htmlspecialchars($monthStr) . '</p>';
                $html .= '<table style="width:100%; border-collapse:collapse; font-size:11px; text-align:left;">';
                $html .= '<thead><tr style="background:#005FBF; color:#ffffff;">';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:center;">No</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">ID Kary</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">Nama Karyawan</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">NPWP</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">PTKP</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">Unit</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db;">Jabatan</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Penghasilan Bruto</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Pengurang / Biaya Jab</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Netto (Bulan)</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">PTKP Setahun</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">PKP Setahun</th>';
                $html .= '<th style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; background:#b91c1c;">PPh 21 Bulan Ini</th>';
                $html .= '</tr></thead><tbody>';

                $totBruto = 0; $totPengurang = 0; $totNetto = 0; $totPph = 0;
                foreach ($rows as $r) {
                    $totBruto += $r['PENGHASILAN BRUTO'];
                    $totPengurang += $r['BIAYA JABATAN & PENGURANG'];
                    $totNetto += $r['PENGHASILAN NETTO (BULAN)'];
                    $totPph += $r['PPH 21 TERUTANG (BULAN INI)'];

                    $html .= '<tr style="border-bottom:1px solid #e5e7eb;">';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:center;">' . $r['NO'] . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['ID KARYAWAN']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; font-weight:600;">' . htmlspecialchars($r['NAMA KARYAWAN']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['NPWP']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:center;">' . htmlspecialchars($r['STATUS PTKP']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['UNIT']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb;">' . htmlspecialchars($r['DIVISI']) . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['PENGHASILAN BRUTO'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['BIAYA JABATAN & PENGURANG'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['PENGHASILAN NETTO (BULAN)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['PTKP SETAHUN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right;">Rp ' . number_format($r['PKP SETAHUN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:6px; border:1px solid #e5e7eb; text-align:right; font-weight:700; color:#b91c1c;">Rp ' . number_format($r['PPH 21 TERUTANG (BULAN INI)'], 0, ',', '.') . '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody><tfoot><tr style="background:#f9fafb; font-weight:bold; border-top:2px solid #9ca3af;">';
                $html .= '<td colspan="7" style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">TOTAL:</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totBruto, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totPengurang, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right;">Rp ' . number_format($totNetto, 0, ',', '.') . '</td>';
                $html .= '<td colspan="2" style="padding:8px 6px; border:1px solid #d1d5db;"></td>';
                $html .= '<td style="padding:8px 6px; border:1px solid #d1d5db; text-align:right; color:#b91c1c;">Rp ' . number_format($totPph, 0, ',', '.') . '</td>';
                $html .= '</tr></tfoot></table></div>';

                return response($html, 200)->header('Content-Type', 'text/html');
            }

            $export = new class(collect($rows)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return [
                        'NO', 'ID KARYAWAN', 'NAMA KARYAWAN', 'NPWP', 'STATUS PTKP',
                        'UNIT', 'DIVISI', 'PENGHASILAN BRUTO', 'BIAYA JABATAN & PENGURANG',
                        'PENGHASILAN NETTO (BULAN)', 'PTKP SETAHUN', 'PKP SETAHUN',
                        'PPH 21 TERUTANG (BULAN INI)'
                    ];
                }
                public function styles(Worksheet $sheet) {
                    $highestColumn = $sheet->getHighestColumn();
                    $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
                    return [];
                }
            };

            return Excel::download($export, "laporan_pph21_{$monthStr}.xlsx");
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function public_exportStatistikGaji()
    {
        try {
            $req = request();
            $periode_from = $req->periode_from ?: Carbon::now()->startOfMonth()->format('Y-m-d');
            $periode_to   = $req->periode_to ?: Carbon::now()->endOfMonth()->format('Y-m-d');
            
            // Format YYYY-MM-DD
            if (strlen($periode_from) == 7) $periode_from = Carbon::parse($periode_from . '-01')->startOfMonth()->format('Y-m-d');
            if (strlen($periode_to) == 7)   $periode_to   = Carbon::parse($periode_to . '-01')->endOfMonth()->format('Y-m-d');

            $groupBy = strtolower($req->group_by ?: 'unit'); // 'global', 'unit', 'divisi', 'dept', 'posisi'

            // Ambil data karyawan aktif dengan relasi
            $karyQuery = m_kary::with(['m_dir', 'm_divisi', 'm_dept', 'm_posisi', 'm_standart_gaji']);

            if ($req->filled('m_dir_id') && $req->m_dir_id !== 'null' && $req->m_dir_id !== 'undefined') {
                $karyQuery->where('m_dir_id', $req->m_dir_id);
            }
            if ($req->filled('m_divisi_id') && $req->m_divisi_id !== 'null' && $req->m_divisi_id !== 'undefined') {
                $karyQuery->where('m_divisi_id', $req->m_divisi_id);
            }
            if ($req->filled('m_dept_id') && $req->m_dept_id !== 'null' && $req->m_dept_id !== 'undefined') {
                $karyQuery->where('m_dept_id', $req->m_dept_id);
            }
            if ($req->filled('is_active') && $req->is_active !== 'null' && $req->is_active !== 'undefined') {
                $isActive = filter_var($req->is_active, FILTER_VALIDATE_BOOLEAN);
                $karyQuery->where('is_active', $isActive);
            }

            $karyawans = $karyQuery->get()->keyBy('id');
            $karyIds = $karyawans->keys()->toArray();

            if (empty($karyIds)) {
                throw new \Exception("Tidak ada data karyawan yang sesuai dengan filter.");
            }

            // Coba ambil dari t_final_gaji_det jika sudah ada finalisasi di periode tersebut
            $finalDetList = DB::table('t_final_gaji_det as fd')
                ->join('t_final_gaji as f', 'fd.t_final_gaji_id', '=', 'f.id')
                ->whereIn('fd.m_kary_id', $karyIds)
                ->where(function($q) use ($periode_from, $periode_to) {
                    $q->whereBetween('f.periode_awal', [$periode_from, $periode_to])
                      ->orWhereBetween('f.periode_akhir', [$periode_from, $periode_to])
                      ->orWhereBetween('fd.periode_in_date', [$periode_from, $periode_to]);
                })
                ->select('fd.*')
                ->get();

            $hasFinalGaji = $finalDetList->isNotEmpty();
            $empStats = [];

            if ($hasFinalGaji) {
                $finalDetIds = $finalDetList->pluck('id')->toArray();
                $rincianList = DB::table('t_final_gaji_det_rincian')
                    ->whereIn('t_final_gaji_det_id', $finalDetIds)
                    ->get()
                    ->groupBy('t_final_gaji_det_id');

                foreach ($finalDetList as $fd) {
                    $kary = $karyawans[$fd->m_kary_id] ?? null;
                    if (!$kary) continue;

                    $rincian = $rincianList[$fd->id] ?? collect();
                    $gajiPokok = 0;
                    $tunjangan = 0;
                    $lembur = 0;
                    $potAbsen = 0;
                    $potPinjaman = 0;
                    $potStockLain = 0;

                    foreach ($rincian as $r) {
                        $label = strtolower($r->label . ' ' . $r->name);
                        $val = (float) $r->value;
                        $factor = trim($r->factor);

                        if ($factor === '+' || $factor === '1' || $factor === 'TAMBAH') {
                            if (stripos($label, 'pokok') !== false) {
                                $gajiPokok += $val;
                            } elseif (stripos($label, 'lembur') !== false) {
                                $lembur += $val;
                            } else {
                                $tunjangan += $val;
                            }
                        } elseif ($factor === '-' || $factor === '-1' || $factor === 'KURANG') {
                            if (stripos($label, 'absen') !== false || stripos($label, 'terlambat') !== false || stripos($label, 'tidak hadir') !== false || stripos($label, 'presensi') !== false) {
                                $potAbsen += $val;
                            } elseif (stripos($label, 'pinjam') !== false || stripos($label, 'kasbon') !== false || stripos($label, 'koperasi') !== false) {
                                $potPinjaman += $val;
                            } else {
                                $potStockLain += $val;
                            }
                        }
                    }

                    if ($gajiPokok == 0 && $tunjangan == 0) {
                        $gajiPokok = (float) ($kary->m_standart_gaji->gaji_pokok ?? 0);
                        $tunjangan = (float) ($kary->m_standart_gaji->tunjangan_tetap ?? 0);
                    }

                    $netto = (float) $fd->netto;
                    if ($netto == 0) {
                        $netto = max(0, ($gajiPokok + $tunjangan + $lembur) - ($potAbsen + $potPinjaman + $potStockLain));
                    }

                    $empStats[] = [
                        'kary_id' => $kary->id,
                        'kary' => $kary,
                        'gaji_pokok' => $gajiPokok,
                        'tunjangan' => $tunjangan,
                        'lembur' => $lembur,
                        'pot_absen' => $potAbsen,
                        'pot_pinjaman' => $potPinjaman,
                        'pot_stock_lain' => $potStockLain,
                        'total_potongan' => ($potAbsen + $potPinjaman + $potStockLain),
                        'netto' => $netto,
                    ];
                }
            } else {
                // Fallback dinamis ke Master Standar Gaji
                $standartGajiIds = $karyawans->pluck('m_standart_gaji_id')->filter()->unique()->toArray();
                $sgDetList = DB::table('m_standart_gaji_det')
                    ->whereIn('m_standart_gaji_id', $standartGajiIds)
                    ->get()
                    ->groupBy('m_standart_gaji_id');

                foreach ($karyawans as $kary) {
                    $gajiPokok = (float) ($kary->m_standart_gaji->gaji_pokok ?? 0);
                    $tunjTetap = (float) ($kary->m_standart_gaji->tunjangan_tetap ?? 0);
                    $uangMakan = (float) ($kary->m_standart_gaji->uang_makan ?? 0);
                    $tunjPosisi = (float) ($kary->m_standart_gaji->tunjangan_posisi ?? 0);
                    $tunjangan = $tunjTetap + $uangMakan + $tunjPosisi;
                    $lembur = 0;
                    $potAbsen = 0;
                    $potPinjaman = 0;
                    $potStockLain = 0;

                    $sgDets = $sgDetList[$kary->m_standart_gaji_id] ?? collect();
                    foreach ($sgDets as $det) {
                        $komp = strtolower($det->komponen);
                        $val = (float) $det->nilai;
                        $faktor = trim($det->faktor);

                        if ($faktor === '+' || $faktor === 'TAMBAH') {
                            if (stripos($komp, 'lembur') !== false) {
                                $lembur += $val;
                            } else {
                                $tunjangan += $val;
                            }
                        } elseif ($faktor === '-' || $faktor === 'KURANG') {
                            if (stripos($komp, 'absen') !== false || stripos($komp, 'terlambat') !== false) {
                                $potAbsen += $val;
                            } elseif (stripos($komp, 'pinjam') !== false || stripos($komp, 'kasbon') !== false) {
                                $potPinjaman += $val;
                            } else {
                                $potStockLain += $val;
                            }
                        }
                    }

                    $totalPotongan = $potAbsen + $potPinjaman + $potStockLain;
                    $netto = max(0, ($gajiPokok + $tunjangan + $lembur) - $totalPotongan);

                    $empStats[] = [
                        'kary_id' => $kary->id,
                        'kary' => $kary,
                        'gaji_pokok' => $gajiPokok,
                        'tunjangan' => $tunjangan,
                        'lembur' => $lembur,
                        'pot_absen' => $potAbsen,
                        'pot_pinjaman' => $potPinjaman,
                        'pot_stock_lain' => $potStockLain,
                        'total_potongan' => $totalPotongan,
                        'netto' => $netto,
                    ];
                }
            }

            // Pengelompokan (Grouping Data)
            $grouped = collect($empStats)->groupBy(function($item) use ($groupBy) {
                $k = $item['kary'];
                if ($groupBy === 'global') return 'SELURUH PERUSAHAAN (GLOBAL)';
                if ($groupBy === 'dept')   return $k->m_dept->nama ?? 'Tanpa Departemen';
                if ($groupBy === 'divisi') return $k->m_divisi->nama ?? 'Tanpa Divisi';
                if ($groupBy === 'posisi' || $groupBy === 'jabatan') return $k->m_posisi->desc_kerja ?? ($k->m_divisi->nama ?? 'Tanpa Jabatan');
                return $k->m_dir->nama ?? 'Tanpa Unit'; // default: unit
            });

            $summaryRows = [];
            $no = 1;
            $allNettos = collect($empStats)->pluck('netto')->toArray();
            $grandTotalKary = count($empStats);
            $grandTotalPokok = collect($empStats)->sum('gaji_pokok');
            $grandTotalTunj = collect($empStats)->sum('tunjangan');
            $grandTotalLembur = collect($empStats)->sum('lembur');
            $grandTotalPotAbsen = collect($empStats)->sum('pot_absen');
            $grandTotalPotPinjam = collect($empStats)->sum('pot_pinjaman');
            $grandTotalPotStock = collect($empStats)->sum('pot_stock_lain');
            $grandTotalPot = collect($empStats)->sum('total_potongan');
            $grandTotalNetto = collect($empStats)->sum('netto');
            $grandAvgNetto = $grandTotalKary > 0 ? round($grandTotalNetto / $grandTotalKary) : 0;
            $grandMaxNetto = !empty($allNettos) ? max($allNettos) : 0;
            $grandMinNetto = !empty($allNettos) ? min($allNettos) : 0;

            foreach ($grouped as $groupName => $items) {
                $headcount = count($items);
                $totPokok = collect($items)->sum('gaji_pokok');
                $totTunj = collect($items)->sum('tunjangan');
                $totLembur = collect($items)->sum('lembur');
                $totPotAbsen = collect($items)->sum('pot_absen');
                $totPotPinjam = collect($items)->sum('pot_pinjaman');
                $totPotStock = collect($items)->sum('pot_stock_lain');
                $totPot = collect($items)->sum('total_potongan');
                $totNetto = collect($items)->sum('netto');
                $nettoArray = collect($items)->pluck('netto')->toArray();
                $avgNetto = $headcount > 0 ? round($totNetto / $headcount) : 0;
                $maxNetto = !empty($nettoArray) ? max($nettoArray) : 0;
                $minNetto = !empty($nettoArray) ? min($nettoArray) : 0;

                $summaryRows[] = [
                    'NO' => $no++,
                    'GRUP' => $groupName,
                    'TOTAL KARYAWAN' => $headcount,
                    'TOTAL GAJI POKOK' => $totPokok,
                    'TOTAL TUNJANGAN' => $totTunj,
                    'TOTAL LEMBUR' => $totLembur,
                    'POTONGAN ABSENSI' => $totPotAbsen,
                    'POTONGAN PINJAMAN' => $totPotPinjam,
                    'POTONGAN SELISIH STOCK / LAIN' => $totPotStock,
                    'TOTAL POTONGAN' => $totPot,
                    'TOTAL GAJI BERSIH (NETTO)' => $totNetto,
                    'RATA-RATA GAJI' => $avgNetto,
                    'GAJI TERTINGGI' => $maxNetto,
                    'GAJI TERENDAH' => $minNetto,
                ];
            }

            // Jika Request HTML View
            if ($req->export === 'html' || strtolower($req->tipe ?? '') === 'html') {
                $html = '<div class="statistik-container p-4" style="background:#f8fafc; font-family:Inter, sans-serif;">';
                
                // HEADER BANNER
                $html .= '<div style="margin-bottom:20px; border-bottom:2px solid #e2e8f0; padding-bottom:12px;">';
                $html .= '<h2 style="font-size:20px; font-weight:800; color:#1e293b; margin:0 0 4px 0;">LAPORAN STATISTIK PENGGAJIAN</h2>';
                $html .= '<p style="font-size:13px; color:#64748b; margin:0;">Periode: <b>' . date('d/m/Y', strtotime($periode_from)) . ' s/d ' . date('d/m/Y', strtotime($periode_to)) . '</b> | Pengelompokan: <span style="text-transform:uppercase; color:#0284c7; font-weight:bold;">' . htmlspecialchars($groupBy) . '</span></p>';
                $html .= '</div>';

                // KPI SUMMARY CARDS
                $html .= '<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">';
                
                $cards = [
                    ['label' => 'Total Pengeluaran Gaji', 'value' => 'Rp ' . number_format($grandTotalNetto, 0, ',', '.'), 'color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
                    ['label' => 'Rata-rata Gaji Karyawan', 'value' => 'Rp ' . number_format($grandAvgNetto, 0, ',', '.'), 'color' => '#059669', 'bg' => '#ecfdf5', 'border' => '#a7f3d0'],
                    ['label' => 'Gaji Tertinggi (Max)', 'value' => 'Rp ' . number_format($grandMaxNetto, 0, ',', '.'), 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
                    ['label' => 'Gaji Terendah (Min)', 'value' => 'Rp ' . number_format($grandMinNetto, 0, ',', '.'), 'color' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fde68a'],
                    ['label' => 'Total Karyawan Terdata', 'value' => number_format($grandTotalKary, 0, ',', '.') . ' Orang', 'color' => '#475569', 'bg' => '#f1f5f9', 'border' => '#cbd5e1'],
                ];

                foreach ($cards as $c) {
                    $html .= '<div style="background:' . $c['bg'] . '; border:1px solid ' . $c['border'] . '; border-radius:10px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">';
                    $html .= '<div style="font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">' . $c['label'] . '</div>';
                    $html .= '<div style="font-size:18px; font-weight:800; color:' . $c['color'] . ';">' . $c['value'] . '</div>';
                    $html .= '</div>';
                }
                $html .= '</div>';

                // TABEL STATISTIK REKAPITULASI
                $html .= '<div style="overflow-x:auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 2px 4px rgba(0,0,0,0.04);">';
                $html .= '<table style="width:100%; border-collapse:collapse; font-size:11px; text-align:left;">';
                $html .= '<thead>';
                $html .= '<tr style="background:#1e3a8a; color:#ffffff;">';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:center;">No</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6;">Kelompok / Grup (' . strtoupper($groupBy) . ')</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:center;">Karyawan</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right;">Gaji Pokok</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right;">Tunjangan</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right;">Lembur</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right; background:#991b1b;">Pot. Absensi</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right; background:#991b1b;">Pot. Pinjaman</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right; background:#991b1b;">Pot. Stock/Lain</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right; background:#b91c1c;">Tot Potongan</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right; background:#047857;">Total Gaji Netto</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right; background:#0f766e;">Rata-Rata</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right;">Tertinggi</th>';
                $html .= '<th style="padding:10px 8px; border:1px solid #3b82f6; text-align:right;">Terendah</th>';
                $html .= '</tr></thead><tbody>';

                foreach ($summaryRows as $r) {
                    $html .= '<tr style="border-bottom:1px solid #e2e8f0;">';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:center;">' . $r['NO'] . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; font-weight:700; color:#1e293b;">' . htmlspecialchars($r['GRUP']) . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:center; font-weight:600;">' . $r['TOTAL KARYAWAN'] . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right;">Rp ' . number_format($r['TOTAL GAJI POKOK'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right;">Rp ' . number_format($r['TOTAL TUNJANGAN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right;">Rp ' . number_format($r['TOTAL LEMBUR'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right; color:#dc2626;">Rp ' . number_format($r['POTONGAN ABSENSI'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right; color:#dc2626;">Rp ' . number_format($r['POTONGAN PINJAMAN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right; color:#dc2626;">Rp ' . number_format($r['POTONGAN SELISIH STOCK / LAIN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right; font-weight:600; color:#b91c1c;">Rp ' . number_format($r['TOTAL POTONGAN'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right; font-weight:700; color:#047857;">Rp ' . number_format($r['TOTAL GAJI BERSIH (NETTO)'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right; font-weight:600; color:#0f766e;">Rp ' . number_format($r['RATA-RATA GAJI'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right;">Rp ' . number_format($r['GAJI TERTINGGI'], 0, ',', '.') . '</td>';
                    $html .= '<td style="padding:8px 6px; border:1px solid #e2e8f0; text-align:right;">Rp ' . number_format($r['GAJI TERENDAH'], 0, ',', '.') . '</td>';
                    $html .= '</tr>';
                }

                // TOTAL GRAND FOOTER
                $html .= '</tbody><tfoot><tr style="background:#f1f5f9; font-weight:bold; border-top:2px solid #64748b;">';
                $html .= '<td colspan="2" style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right; font-size:12px;">GRAND TOTAL:</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:center;">' . number_format($grandTotalKary, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right;">Rp ' . number_format($grandTotalPokok, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right;">Rp ' . number_format($grandTotalTunj, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right;">Rp ' . number_format($grandTotalLembur, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right; color:#dc2626;">Rp ' . number_format($grandTotalPotAbsen, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right; color:#dc2626;">Rp ' . number_format($grandTotalPotPinjam, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right; color:#dc2626;">Rp ' . number_format($grandTotalPotStock, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right; color:#b91c1c;">Rp ' . number_format($grandTotalPot, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right; color:#047857; font-size:12px;">Rp ' . number_format($grandTotalNetto, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right; color:#0f766e;">Rp ' . number_format($grandAvgNetto, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right;">Rp ' . number_format($grandMaxNetto, 0, ',', '.') . '</td>';
                $html .= '<td style="padding:10px 8px; border:1px solid #cbd5e1; text-align:right;">Rp ' . number_format($grandMinNetto, 0, ',', '.') . '</td>';
                // Simpan template HTML untuk render View & PDF
                if ($req->export === 'html' || strtolower($req->tipe ?? '') === 'html') {
                    return response($html, 200)->header('Content-Type', 'text/html');
                }
            }

            // Export Excel / PDF Dataset
            $export = new class(collect($summaryRows)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return [
                        'NO', 'GRUP / KELOMPOK', 'TOTAL KARYAWAN', 'TOTAL GAJI POKOK', 'TOTAL TUNJANGAN',
                        'TOTAL LEMBUR', 'POTONGAN ABSENSI', 'POTONGAN PINJAMAN', 'POTONGAN SELISIH STOCK / LAIN',
                        'TOTAL POTONGAN', 'TOTAL GAJI BERSIH (NETTO)', 'RATA-RATA GAJI', 'GAJI TERTINGGI', 'GAJI TERENDAH'
                    ];
                }
                public function styles(Worksheet $sheet) {
                    $highestColumn = $sheet->getHighestColumn();
                    $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
                    return [];
                }
            };

            // Jika Request PDF
            if ($req->export === 'pdf' || strtolower($req->tipe ?? '') === 'pdf') {
                try {
                    return Excel::download($export, "laporan_statistik_penggajian_{$periode_from}_{$periode_to}.pdf", \Maatwebsite\Excel\Excel::DOMPDF);
                } catch (\Exception $ex) {
                    $printHtml = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Statistik Penggajian (' . $periode_from . ' s/d ' . $periode_to . ')</title><style>@page { size: landscape; margin: 8mm; } body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 10px; } @media print { .no-print { display: none !important; } body { padding: 0; } }</style></head><body><div class="no-print" style="margin-bottom:15px; padding:10px 16px; background:#1e293b; color:white; border-radius:6px; display:flex; justify-content:space-between; align-items:center;"><span style="font-weight:bold; font-size:13px;">Pratinjau PDF Laporan Statistik Penggajian</span><div><button onclick="window.print()" style="background:#2563eb; color:white; border:none; padding:8px 16px; border-radius:4px; font-weight:bold; cursor:pointer;">🖨️ Cetak / Save to PDF</button><button onclick="window.close()" style="background:#64748b; color:white; border:none; padding:8px 12px; border-radius:4px; margin-left:8px; cursor:pointer;">Tutup</button></div></div>' . $html . '<script>window.onload = function() { setTimeout(function() { window.print(); }, 500); };</script></body></html>';
                    return response($printHtml, 200)->header('Content-Type', 'text/html');
                }
            }

            return Excel::download($export, "laporan_statistik_penggajian_{$periode_from}_{$periode_to}.xlsx");
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}