@php
use App\Models\CustomModels\m_kary;
use App\Models\CustomModels\t_jadwal_kerja_det_hari;
use App\Models\CustomModels\t_jadwal_kerja;
use App\Models\CustomModels\t_cuti;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$req = app()->request;

$karyQuery = m_kary::with([
    't_jadwal_kerja.t_jadwal_kerja_det_hari',
]);

if ($req->filled('kary_id')) {
    $ids = array_map('intval', explode(',', $req->kary_id));
    $karyQuery->whereIn('id', $ids);
} else {
    if ($req->filled('m_divisi_id')) {
        $karyQuery->where('m_divisi_id', $req->m_divisi_id);
    }
    if ($req->filled('m_dir_id')) {
        $karyQuery->where('m_dir_id', $req->m_dir_id);
    }
}

$karyawanList = $karyQuery->get();

$date_start = Carbon::parse($req->date_start)->format('Y-m-d');
$date_end = Carbon::parse($req->date_end)->format('Y-m-d');
$currentMonth = date('m');
@endphp

@foreach($karyawanList as $kary)
@php
  $kary_id = $kary->id;

  $data = DB::select("
     select * from employee_attendance_detail_range(?,?,?)
  ", [$date_start, $date_end, $kary_id]);

  $rekap = DB::select("
    select 
      employee_attendance_range(?,?,k.id) absen,
      (select TO_CHAR(INTERVAL '1 second' * AVG(EXTRACT(EPOCH FROM pa.checkin_time::TIME)), 'HH24:MI:SS')
         from presensi_absensi pa where pa.default_user_id = u.id 
         and pa.checkin_time is not null and to_char(pa.tanggal,'mm') = ?) checkin_avg,
      (select TO_CHAR(INTERVAL '1 second' * AVG(EXTRACT(EPOCH FROM pa.checkout_time::TIME)), 'HH24:MI:SS')
         from presensi_absensi pa where pa.default_user_id = u.id 
         and pa.checkout_time is not null and to_char(pa.tanggal,'mm') = ?) checkout_avg,
      k.id, kode, nama_lengkap, d.nama dept 
    from m_kary k
    join default_users u on u.m_kary_id = k.id
    join m_dept d on d.id = k.m_dept_id
    where k.is_active = true 
    and k.m_dept_id IS NOT NULL and k.m_dept_id != 0
    and k.id = COALESCE(?, k.id)
  ", [$date_start, $date_end, $currentMonth, $currentMonth, $kary_id]);
  //dd($kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day'));
  $jadwalHariList = $kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day');
  $cutiList = t_cuti::where('m_kary_id', $kary_id)
    ->with('alasan')
    ->where('status', 'APPROVED')
    ->where(function ($q) use ($date_start, $date_end) {
        // Cek dulu tipe kolom di runtime
        $columnType = \DB::getSchemaBuilder()->getColumnType('t_cuti', 'date_from');

        if ($columnType === 'string') {
            // Kalau kolom disimpan sebagai string (varchar)
            $q->whereRaw("
                TO_DATE(REPLACE(date_from, '/', '-'), 'DD-MM-YYYY') <= ?
                AND TO_DATE(REPLACE(date_to, '/', '-'), 'DD-MM-YYYY') >= ?
            ", [$date_end, $date_start]);
        } else {
            // Kalau kolom bertipe date
            $q->whereDate('date_from', '<=', $date_end)
              ->whereDate('date_to', '>=', $date_start);
        }
    })
    ->get();
@endphp

<span style="font-weight:bold; font-size: 7pt"> {{ @json_decode(@$data[0]->kary)->nik }} - {{ @json_decode(@$data[0]->kary)->nama_lengkap }}</span><br/>
<span style="font-weight:bold; font-size: 7pt"> Periode  {{$date_start}} - {{$date_end}}</span><br/></br></br>
<table style="width: 100%; font-size: 7pt" cellpadding="2">
  <thead class="bg-[#c6c6c6]">
    <tr>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 15%;">Tanggal</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 6%;">Tipe Hari</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 8%; text-align: center;">Status</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkin Time</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkin Scope</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkout Time</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkout Scope</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Alasan Ijin</th>
    </tr>
  </thead>
  <tbody>
    @php
    $total_checkin_telat = 0;
    $total_checkout_lebih_awal = 0;
    $total_checkin_lebih_awal = 0;
    $total_checkout_telat = 0;
    $total_hadir = 0;
    $total_kerja = 0;
    $total_ijin = 0;
    @endphp
    @foreach($data as $dt)
         @php
        $tanggal = Carbon::parse($dt->all_days_of_range)->format('Y-m-d');

        // CEK APAKAH HARI INI CUTI
        $isCuti = false;
        $alasan_cuti = '-';
        foreach ($cutiList as $cuti) {
            // konversi format date_from & date_to
            //$from = Carbon::createFromFormat('d/m/Y', str_replace('/', '/', $cuti->date_from))->format('Y-m-d');
            //$to   = Carbon::createFromFormat('d/m/Y', str_replace('/', '/', $cuti->date_to))->format('Y-m-d');
            $from = Carbon::parse($cuti->date_from)->format('Y-m-d');
            $to = Carbon::parse($cuti->date_to)->format('Y-m-d');
            if ($tanggal >= $from && $tanggal <= $to) {
                $isCuti = true;
                $alasan_cuti = $cuti->alasan?->value;
                break;
            }
        }

        // Ambil jadwal
        $jadwal = $jadwalHariList[$dt->day_name_idn] ?? null;
        $absensi = json_decode($dt->absensi, true);

        // kalau cuti, langsung tandai dan lanjut ke iterasi berikutnya
        if ($isCuti) {
            $checkin_result = '<span >IJIN</span>';
            $checkout_result = '<span >IJIN</span>';
            $checkin_info = '<small>-</small>';
            $checkout_info = '<small>-</small>';
            $total_ijin++;
        } else {
            // PROSES SEPERTI BIASA
            $waktu_mulai = isset($jadwal) ? Carbon::parse($jadwal->waktu_mulai) : null;
            $waktu_checkin = Carbon::parse(optional(json_decode($dt->absensi))->checkin_time);

            // HITUNG TELAT CHECKIN
            $checkin_result = $absensi['checkin_time'] ?? null;
            if ($checkin_result && @$jadwal->waktu_mulai) {
                $late = $waktu_mulai->diffInMinutes($waktu_checkin);
                if ($waktu_mulai < $waktu_checkin && $late > 0) {
                    $checkin_result .= ' / ' . $jadwal->waktu_mulai . ' <span style="color:red">(' . $late . ' Menit)</span>';
                    $total_checkin_telat += $late;
                } else {
                    $checkin_result .= @$jadwal->waktu_mulai ? ' / ' . @$jadwal->waktu_mulai : '';
                    if ($waktu_checkin < $waktu_mulai && $late > 0) {
                        $total_checkin_lebih_awal += $late;
                    }
                }
            }

            // HITUNG TELAT CHECKOUT
            $checkout_result = $absensi['checkout_time'] ?? null;
            if ($checkout_result && @$jadwal->waktu_akhir) {
                $waktu_akhir = Carbon::parse(@$jadwal->waktu_akhir);
                $waktu_checkout = Carbon::parse($checkout_result);
                $late = $waktu_akhir->diffInMinutes($waktu_checkout);
                if ($waktu_akhir > $waktu_checkout) {
                    $checkout_result .= ' / ' . @$jadwal->waktu_akhir . ' <span style="color:red">(' . $late . ' Menit)</span>';
                    $total_checkout_lebih_awal += $late;
                } else {
                    $checkout_result .= @$jadwal->waktu_akhir ? ' / ' . @$jadwal->waktu_akhir : '';
                    $total_checkout_telat += $late;
                }
            }

            // BUAT INFO SCOPE DAN CATATAN
            $checkinScope  = $absensi['checkin_on_scope'] ?? null;
            $checkoutScope = $absensi['checkout_on_scope'] ?? null;
            $catatanIn     = $absensi['catatan_in'] ?? '-';
            $catatanOut    = $absensi['catatan_out'] ?? '-';

            $checkin_info = '';
            $checkout_info = '';

            if (!empty($checkin_result)) {
                $scopeText = $checkinScope ? 'IN SCOPE' : 'OUT SCOPE';
                $catatanText = $catatanIn ? ' | Catatan: '.$catatanIn : '';
                $checkin_info = '<small>'.$scopeText.$catatanText.'</small>';
            }

            if (!empty($checkout_result)) {
                $scopeText = $checkoutScope ? 'IN SCOPE' : 'OUT SCOPE';
                $catatanText = $catatanOut ? ' | Catatan: '.$catatanOut : '';
                $checkout_info = '<small>'.$scopeText.$catatanText.'</small>';
            }

            if ($jadwal?->tipe_hari === 'KERJA') {
                $total_kerja++;
            }

            if (($absensi['status'] ?? '') === 'ATTEND') {
                $total_hadir++;
            }
        }
    @endphp
        <tr>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 15%;">{{ $dt->day_name_idn }}, {{$dt->date_to_idn}}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 6%;">{{ $jadwal?->tipe_hari }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 8%; text-align: center;">{{ @json_decode($dt->absensi)->status }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkin_result !!}
          </td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkin_info !!}
          </td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkout_result !!}
          </td>
           <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkout_info !!}
          </td>
           <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {{$alasan_cuti}}
          </td>
        </tr>
    @endforeach
  </tbody>
</table>
<br>
<table style="width: 100%; font-size: 7pt" >
  <tbody>
    <tr>
      <td style="color: red; width: 7%">Total Checkin Telat</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ round($total_checkin_telat/60).' Jam ('.$total_checkin_telat.' Menit)' }}</td>
      <td style="width: 7%">Hari Kerja</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ @json_decode(@$rekap[0]->absen)->work_days_in_month ?? $total_kerja }}</td>
    </tr>
    <tr>
      <td style="color: red">Total Checkout Lebih Awal</td>
      <td style="width: 1%">:</td>
     <td style="width: 20%">{{ round($total_checkout_lebih_awal/60).' Jam ('.$total_checkout_lebih_awal.' Menit)' }}</td>
      <td>Hadir</td>
      <td style="width: 1%">:</td>
      <td>{{ @json_decode(@$rekap[0]->absen)->work_present ?? $total_hadir }}</td>
    </tr>
    <tr>
      <td>Total Checkin Lebih Awal</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ round($total_checkin_lebih_awal/60).' Jam ('.$total_checkin_lebih_awal.' Menit)' }}</td>
      <td>Ijin / Cuti</td>
      <td style="width: 1%">:</td>
      <td>{{ $total_ijin }}</td>
    </tr>
    <tr>
      <td>Total Checkout Telat</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ round($total_checkout_telat/60).' Jam ('.$total_checkout_telat.' Menit)' }}</td>
      <td>Alpha</td>
      <td style="width: 1%">:</td>
      <td>{{ @json_decode(@$rekap[0]->absen)->work_not_present ?? '-' }}</td>
    </tr>
    <tr>
      <td>Rata-rata Jam Checkin</td>
      <td style="width: 1%">:</td>
      <td>{{ @$rekap[0]->checkin_avg ?? '-' }}</td>
    </tr>
    <tr>
      <td>Rata-rata Jam Checkout</td>
      <td style="width: 1%">:</td>
      <td>{{ @$rekap[0]->checkout_avg ?? '-' }}</td>
    </tr>
  </tbody>
</table>
@if(!$loop->last)
  <div style="page-break-after: always;"></div>
@endif
@endforeach