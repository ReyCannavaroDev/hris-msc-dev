@php
  $req = app()->request;
  $periode_from = $req->periode_from;
  if (!$periode_from || $periode_from === 'null' || $periode_from === 'undefined') {
      $periode_from = '2023-01-01';
  }
  $periode_to = $req->periode_to;
  if (!$periode_to || $periode_to === 'null' || $periode_to === 'undefined') {
      $periode_to = '2023-12-30';
  }
  
  $tunjangan_filter = $req->tunjangan_filter;
  $has_tunjangan_filter = $tunjangan_filter && $tunjangan_filter !== 'Semua' && $tunjangan_filter !== 'null' && $tunjangan_filter !== 'undefined';

  $query = "
    select 
      k.nik, k.nama_lengkap, kd.nama dir, kdi.nama divisi, kde.nama dept, (fd.netto+fd.total_tax) total_gaji, fd.total_tax, fd.netto, fd.periode_in_date , fd.periode ";
      
  if ($has_tunjangan_filter) {
      $query .= ", coalesce(
          (select sum(value) from t_final_gaji_det_rincian where t_final_gaji_det_id = fd.id and (name ilike ? or label ilike ?)),
          case when sg.kode ilike ? then fd.total_gaji else 0 end
      ) as nilai_tunjangan ";
  } else {
      $query .= ", 0 as nilai_tunjangan ";
  }
  
  $query .= "
    from t_final_gaji_det fd
      join t_final_gaji f on fd.t_final_gaji_id = f.id
      join m_kary k on k.id = fd.m_kary_id 
      left join m_dir kd on kd.id = fd.m_kary_dir_id 
      left join m_divisi kdi on kdi.id = fd.m_kary_divisi_id 
      left join m_dept kde on kde.id = fd.m_kary_dept_id  
      left join m_standart_gaji sg on sg.id = k.m_standart_gaji_id
      where f.status in ('APPROVED', 'POSTED') 
      and f.periode_awal >= ? and f.periode_akhir <= ? 
      and case when kd.id is not null then kd.id = coalesce(?,kd.id) else true end
      and case when kdi.id is not null then kdi.id = coalesce(?,kdi.id) else true end
      and case when k.id is not null then k.id = coalesce(?,k.id) else true end";
      
  $bindings = [];
  if ($has_tunjangan_filter) {
      $bindings[] = '%' . $tunjangan_filter . '%';
      $bindings[] = '%' . $tunjangan_filter . '%';
      $bindings[] = '%' . $tunjangan_filter . '%';
  }
  
  $m_dir_id = $req->m_dir_id;
  if ($m_dir_id === 'null' || $m_dir_id === 'undefined' || trim($m_dir_id) === '') {
      $m_dir_id = null;
  }
  $m_divisi_id = $req->m_divisi_id;
  if ($m_divisi_id === 'null' || $m_divisi_id === 'undefined' || trim($m_divisi_id) === '') {
      $m_divisi_id = null;
  }
  $m_kary_id = $req->m_kary_id;
  if ($m_kary_id === 'null' || $m_kary_id === 'undefined' || trim($m_kary_id) === '') {
      $m_kary_id = null;
  }

  $bindings[] = $periode_from;
  $bindings[] = $periode_to;
  $bindings[] = $m_dir_id;
  $bindings[] = $m_divisi_id;
  $bindings[] = $m_kary_id;
  
  $has_tgl_masuk_from = $req->tgl_masuk_from && $req->tgl_masuk_from !== 'null' && $req->tgl_masuk_from !== 'undefined' && trim($req->tgl_masuk_from) !== '';
  $has_tgl_masuk_to = $req->tgl_masuk_to && $req->tgl_masuk_to !== 'null' && $req->tgl_masuk_to !== 'undefined' && trim($req->tgl_masuk_to) !== '';
  
  if ($has_tgl_masuk_from) {
      $query .= " and coalesce(k.tgl_masuk, (select min(tgl_awal) from m_kary_det_kontrak where m_karyawan_id = k.id)) >= ? ";
      $bindings[] = $req->tgl_masuk_from;
  }
  if ($has_tgl_masuk_to) {
      $query .= " and coalesce(k.tgl_masuk, (select min(tgl_awal) from m_kary_det_kontrak where m_karyawan_id = k.id)) <= ? ";
      $bindings[] = $req->tgl_masuk_to;
  }
 
  if ($has_tunjangan_filter) {
      $query .= " and (
          exists (select 1 from t_final_gaji_det_rincian where t_final_gaji_det_id = fd.id and (name ilike ? or label ilike ?))
          or sg.kode ilike ?
      )";
      $bindings[] = '%' . $tunjangan_filter . '%';
      $bindings[] = '%' . $tunjangan_filter . '%';
      $bindings[] = '%' . $tunjangan_filter . '%';
  }
  
  $raw = \DB::select($query, $bindings);
@endphp
<span style="width:100%;text-align:center;font-weight:bold;"> Rekapitulasi Gaji </span><br/>

@php
  $periode_from = date('d-m-Y', strtotime($periode_from));
  $periode_to = date('d-m-Y', strtotime($periode_to));
@endphp

<span style="width:100%;text-align:center; font-size:10pt"> {{ $periode_from }} - {{ $periode_to }}</span><br/>
@if($has_tgl_masuk_from || $has_tgl_masuk_to)
<span style="width:100%;text-align:center; font-size:9pt; color: #555;"> 
  Filter Tanggal Masuk Karyawan: 
  {{ $req->tgl_masuk_from ? date('d-m-Y', strtotime($req->tgl_masuk_from)) : 'Awal' }} 
  s/d 
  {{ $req->tgl_masuk_to ? date('d-m-Y', strtotime($req->tgl_masuk_to)) : 'Akhir' }}
</span><br/>
@endif
<br/>
<table width="100%" style="font-size:10px;" cellpadding="2">
  <thead style="font-weight:semibold;">
    <tr style="">
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">No</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Tanggal</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">NIK</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Nama Karyawan</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Direktorat</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Divisi</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Departemen</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Gaji</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">PPH</td>
      @if($has_tunjangan_filter)
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Tunjangan ({{ $tunjangan_filter }})</td>
      @endif
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Total Gaji</td>
    </tr>
  </thead>
  <tbody>
    @foreach($raw as $i => $d)
    @php
        $backgroundColor = $i % 2 === 1 ? '#f8f8f8' : '';
        $formatted_gaji = ($d->total_gaji != 0) ? number_format($d->total_gaji, 2, ',', '.') : $d->total_gaji ;
        $formatted_tax = ($d->total_tax != 0) ? number_format($d->total_tax, 2, ',', '.') : $d->total_tax;
        $formatted_netto = ($d->netto != 0) ? number_format($d->netto, 2, ',', '.') : $d->netto;
    @endphp
    <tr style="background-color: {{$backgroundColor}}">
      <td style="border:0.5px solid black;text-align:center;">{{ $i+1 }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->periode }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->nik }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->nama_lengkap }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->dir }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->divisi }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->dept }}</td>
      <td style="border:0.5px solid black;text-align:right;">{{ $formatted_gaji }}</td>
      <td style="border:0.5px solid black;text-align:right;">{{ $formatted_tax }}</td>
      @if($has_tunjangan_filter)
      <td style="border:0.5px solid black;text-align:right;">{{ ($d->nilai_tunjangan != 0) ? number_format($d->nilai_tunjangan, 2, ',', '.') : '0,00' }}</td>
      @endif
      <td style="border:0.5px solid black;text-align:right;">{{ $formatted_netto}}</td>
    </tr>
    @endforeach
  </tbody>
</table>