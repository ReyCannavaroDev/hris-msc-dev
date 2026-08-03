<?php

namespace App\Models\CustomModels;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
Carbon::setLocale('id');
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class presensi_absensi extends \App\Models\BasicModels\presensi_absensi
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');

    }

    public $fileColumns = [];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function onRetrieved($model)
    {
        $model->checkout_foto = url('').'/'.$model->checkout_foto;
        $model->checkin_foto = url('').'/'.$model->checkin_foto;
    }

    public function default_users()
    {
        return $this->belongsTo(default_users::class, 'default_user_id', 'id');
    }

    // public function custom_get_by_daily($req)
    // {
    //     $req->month = $req->month.'-01';
    //     $weeks = $req->weeks;
    //     $start_date = '';
    //     $end_date = '';

    //     $weeksArr = explode('/', $weeks);
    //     if(count($weeksArr) > 1){
    //         $start_date = $weeksArr[0];
    //         $end_date = $weeksArr[1];
    //         $data = \DB::select("
    //             SELECT json_agg(json_build_object(
    //                 'all_days_of_month', all_days_of_month,
    //                 'date_to_idn', date_to_idn,
    //                 'day_name_idn', day_name_idn,
    //                 'type', type,
    //                 'presentase', presentase,
    //                 'attend', attend,
    //                 'cuti', cuti,
    //                 'alpha', alpha,
    //                 'total_kary', total_kary
    //             )) AS monthly_report
    //             FROM generate_weekly_report(?,?,?,?)",[$start_date, $end_date,$req->divisi_id,$req->dept_id]);
    //     }else{
    //         $data = \DB::select("
    //             SELECT json_agg(json_build_object(
    //                 'all_days_of_month', all_days_of_month,
    //                 'date_to_idn', date_to_idn,
    //                 'day_name_idn', day_name_idn,
    //                 'type', type,
    //                 'presentase', presentase,
    //                 'attend', attend,
    //                 'cuti', cuti,
    //                 'alpha', alpha,
    //                 'total_kary', total_kary
    //             )) AS monthly_report
    //             FROM generate_monthly_report(?,?,?)",[$req->month,$req->divisi_id,$req->dept_id]);
    //     }

        
    //     if(count($data)){   
    //         return $this->helper->customResponse('OK',200,json_decode($data[0]->monthly_report));
    //     }else{
    //         return $this->helper->customResponse('OK',200,[]);
    //     }
    // }

    public function custom_get_by_daily($req)
    {
        if ($req->weeks) {
            [$startDate, $endDate] = explode("/", $req->weeks);
        } else {
            $startDate = Carbon::parse($req->month . "-01");
            $endDate = Carbon::parse($req->month . "-03");
        }

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dates[] = $current->format("Y-m-d");
            $current->addDay();
        }

        $hariLibur = m_general::where("group", "HARI LIBUR")
            ->pluck("value")
            ->toArray();

        $cutiBersama = m_libur_nasional::pluck("tanggal")
            ->map(fn($t) => Carbon::parse($t)->format("Y-m-d"))
            ->toArray();

        $totalKary = default_users::whereHas("m_kary", function ($q) use (
            $req
        ) {
            $q->where("is_active", true);
            if ($req->divisi_id) {
                $q->where("m_divisi_id", $req->divisi_id);
            }
            if ($req->dir_id) {
                $q->where("m_dir_id", $req->dir_id);
            }
            if ($req->dept_id) {
                $q->where("m_dept_id", $req->dept_id);
            }
        })->count();

        $result = [];

        foreach ($dates as $index => $date) {
            $carbonDate = Carbon::parse($date);
            $dayNameIdn = $carbonDate->translatedFormat("l");
            $dayNameIdn = ucfirst($dayNameIdn);

            if (in_array($dayNameIdn, $hariLibur)) {
                $type = "Hari Libur";
            } elseif (in_array($carbonDate->format("Y-m-d"), $cutiBersama)) {
                $type = "Cuti Bersama";
            } else {
                $type = "Hari Kerja";
            }

            $hadir = default_users::whereHas("m_kary", function ($q1) use (
                $req
            ) {
                $q1->where("is_active", true);
                if ($req->divisi_id) {
                    $q1->where("m_divisi_id", $req->divisi_id);
                }
                if ($req->dir_id) {
                    $q1->where("m_dir_id", $req->dir_id);
                }
                if ($req->dept_id) {
                    $q1->where("m_dept_id", $req->dept_id);
                }
            })
                ->whereHas("presensi_absensi", function ($q) use ($date) {
                    $q->where("tanggal", $date);
                })
                ->count();

            $cuti = t_cuti::where("status", "APPROVED")
                ->whereDate("date_from", ">=", $date)
                ->whereDate("date_to", "<=", $date)
                ->count();

            $alpha = $totalKary - $hadir - $cuti;

            $presentase = $totalKary > 0 ? ($hadir / $totalKary) * 100 : 0;

            $result[] = [
                "all_days_of_month" => $carbonDate->format("Y-m-d"),
                "date_to_idn" => $carbonDate->format("d-m-Y"),
                "day_name_idn" => $dayNameIdn,
                "type" => $type,
                "presentase" => $presentase,
                "attend" => $hadir,
                "cuti" => $cuti,
                "alpha" => $alpha,
                "total_kary" => $totalKary,
            ];
        }

        return $this->helper->customResponse("OK", 200, $result);
    }

    public function custom_get_by_date($req)
    {
      $req = app()->request;
        $os_id = $req->m_os_id ?? null;
        $kary = default_users::with(['presensi_absensi' => function($q) use ($req, $os_id){
           $q->where('tanggal', $req->date); 
        }])
        ->whereHas('m_kary', function($q) use ($req, $os_id){
            $q->where('is_active', true);
            if(!empty($req->m_os_id)){
                $q->where('m_company_outsourcing_id', $req->m_os_id);
            }
            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($req->divisi_id)) {
                $q->where('m_divisi_id', $req->divisi_id);
            }
            if (!empty($req->dir_id)) {
                $q->where('m_dir_id', $req->dir_id);
            }
        })
        ->get()
        ->map(function($user) use ($req) {
            $absensi = $user->presensi_absensi->first();
            $jadwal = $absensi && $absensi->t_jadwal_kerja_d_hari_n ? $absensi->t_jadwal_kerja_d_hari_n : null ;

            if ($absensi) {
                $status = $absensi->status;
                $absensiData = $absensi;
                $absensiData['presensi_absensi_id'] = $absensi?->id ?? 0;
            } else {
                $status = "NOT ATTEND";
                $absensiData = ['status' => 'NOT ATTEND'];
            }

            return [
                'm_kary_id'       => $user->m_kary_id,
                'default_user_id' => $user->id,
                "presensi_absensi_id" => $absensi?->id ?? 0,
                'kode'            => $user->m_kary->kode,
                'divisi'          => $user->m_kary->m_divisi?->nama ?? '-',
                'dir'             => $user->m_kary->m_dir?->nama ?? '-',
                'nama_lengkap'    => $user->m_kary->nama_depan ?? null,
                'absensi'         => $absensiData,
                'status'          => $status,
                'jam_kerja'       => $jadwal ? $jadwal->waktu_mulai . ' - ' . $jadwal->waktu_akhir : '-',
                'desc'            => $jadwal && $jadwal->m_jam_kerja ? $jadwal->m_jam_kerja->desc : '-',
            ];
        });

        return $this->helper->customResponse('OK', 200, $kary);  
    }

    // public function custom_get_by_date($req)
    // {
    //     $data = \DB::select("
    //         SELECT json_agg(json_build_object(
    //             'm_kary_id', m_kary_id,
    //             'default_user_id', default_user_id,
    //             'kode', kode,
    //             'nama_lengkap', nama_lengkap,
    //             'dept', dept,
    //             'absensi', absensi
    //         )) AS att_report
    //         FROM get_employee_attendance_report(?,?,?)",[$req->date,$req->divisi_id,$req->dept_id ?? null]);

        
    //     if(count($data)){   
    //         return $this->helper->customResponse('OK',200,json_decode($data[0]->att_report));
    //     }else{
    //         return $this->helper->customResponse('OK',200,[]);
    //     }
    // }

    public function custom_checkin($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) 
            return $this->helper->responseValidate($validator);
        
        //$checkin_time = $req->checkin_time;

        if(floatval($req->long) < 114){
            $checkin_time = Carbon::now('Asia/Jakarta');
        }elseif(floatval($req->long) < 141){
            $checkin_time = Carbon::now('Asia/Makassar');
        }else{
            $checkin_time = Carbon::now('Asia/Jayapura');
        }

        DB::beginTransaction();
        try {
            $can_outscope = m_kary::whereHas('default_users', function($q){
                $q->where('id', auth()->user()->id);
            })->first()?->can_outscope ?? false;
            // dd($can_outscope);

            $distance = $this->distance($req->lat, $req->long);
            // dd($distance);

            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = null;
            } elseif($can_outscope) {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = $req->catatan_in ?? null;
            }else{
                // trigger_error('OUT SCOPE NOT ALLOWED');
                // return;
                abort(403, 'Anda berada di luar jangkauan (OUT SCOPE NOT ALLOWED)');
            }

            $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->exists();
            if ($check_exists_absen == true) 
                return $this->helper->customResponse("Anda sudah checkin hari ini", 422);

            if ($req->hasFile('foto')) {
                $file = $req->file('foto');
                $ext = strtolower($file->getClientOriginalExtension());

                // Nama file .webp
                $fileName = auth()->user()->username.':::'.md5(time()).'.webp';

                // Folder berdasarkan tahun/bulan/tanggal
                $subfolder = date('Y') . '/' . date('m') . '/' . date('d');
                $destination = public_path('uploads/presensi/' . $subfolder);

                // Buat folder kalau belum ada
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                // Buat image resource tergantung tipe
                switch ($ext) {
                    case 'jpeg':
                    case 'jpg':
                        $src = imagecreatefromjpeg($file->getRealPath());
                        break;
                    case 'png':
                        $src = imagecreatefrompng($file->getRealPath());
                        imagepalettetotruecolor($src);
                        imagealphablending($src, true);
                        imagesavealpha($src, true);
                        break;
                    default:
                        trigger_error('Format tidak didukung');
                        return;
                }

                // Simpan ke WebP dengan kualitas 80
                imagewebp($src, $destination.'/'.$fileName, 80);
                imagedestroy($src);

                // Path relatif untuk disimpan di DB
                $storedPath = 'uploads/presensi/'.$subfolder.'/'.$fileName;

            } else {
                trigger_error('IMAGE NOT VALID');
                return;
            }

            $this->create([
                "tanggal" => date("Y-m-d"),
                "checkin_time" => $checkin_time ?? date("H:i:s"),
                // "checkin_foto" => "uploads/presensi/$fileName",
                'checkin_foto' => $storedPath,
                "checkin_lat" => $data["checkin_lat"],
                "checkin_long" => $data["checkin_long"],
                "checkin_address" => $data["checkin_address"],
                "checkin_region" => $data["region"],
                "checkin_on_scope" => $data["on_scope"],
                "catatan_in" => $data["catatan_in"],
                "default_user_id" => auth()->user()->id,
                "creator_id" => auth()->user()->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkin gagal, coba kembali nanti - ".$e->getMessage(), 400);
        }
        return $this->helper->customResponse("Checkin berhasil", 200, $data);
    }

    public function custom_checkout($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) return $this->helper->responseValidate($validator);

        //$checkout_time = $req->checkout_time;

        if(floatval($req->long) < 114){
            $checkout_time = Carbon::now('Asia/Jakarta');
        }elseif(floatval($req->long) < 141){
            $checkout_time = Carbon::now('Asia/Makassar');
        }else{
            $checkout_time = Carbon::now('Asia/Jayapura');
        }

        DB::beginTransaction();
        try {
            $can_outscope = m_kary::whereHas('default_users', function($q){
                $q->where('id', auth()->user()->id);
            })->first()?->can_outscope ?? false;

            $distance = $this->distance($req->lat, $req->long);
            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkout_lat"] = $req->lat;
                $data["checkout_long"] = $req->long;
                $data["checkout_address"] = $req->address;
                $data["catatan_out"] = null;
            } elseif($can_outscope) {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkout_lat"] = $req->lat;
                $data["checkout_long"] = $req->long;
                $data["checkout_address"] = $req->address;
                $data["catatan_out"] = $req->catatan_out ?? null;
            }else{
                abort(403, 'Anda berada di luar jangkauan (OUT SCOPE NOT ALLOWED)');
            }

            $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "ATTEND")
                ->exists();
            if ($check_exists_absen) 
                return $this->helper->customResponse("Anda sudah checkout hari ini", 422);
            
            $check_not_exists_checkin = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "WORKING")->exists();
             if ($check_exists_absen) 
                return $this->helper->customResponse("Anda belum checkin hari ini", 422);

            if ($req->hasFile('foto')) {
                $file = $req->file('foto');
                $ext = strtolower($file->getClientOriginalExtension());

                // Nama file .webp
                $fileName = auth()->user()->username.':::'.md5(time()).'.webp';

                // Folder berdasarkan tahun/bulan/tanggal
                $subfolder = date('Y') . '/' . date('m') . '/' . date('d');
                $destination = public_path('uploads/presensi/' . $subfolder);

                // Buat folder kalau belum ada
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                // Buat image resource tergantung tipe
                switch ($ext) {
                    case 'jpeg':
                    case 'jpg':
                        $src = imagecreatefromjpeg($file->getRealPath());
                        break;
                    case 'png':
                        $src = imagecreatefrompng($file->getRealPath());
                        imagepalettetotruecolor($src);
                        imagealphablending($src, true);
                        imagesavealpha($src, true);
                        break;
                    default:
                        trigger_error('Format tidak didukung');
                        return;
                }

                // Simpan ke WebP dengan kualitas 80
                imagewebp($src, $destination.'/'.$fileName, 80);
                imagedestroy($src);

                // Path relatif untuk disimpan di DB
                $storedPath = 'uploads/presensi/'.$subfolder.'/'.$fileName;

            } else {
                trigger_error('IMAGE NOT VALID');
                return;
            }

            $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "WORKING")
                ->update([
                    "checkout_time" => $checkout_time ?? date("H:i:s"),
                    "checkout_foto" => $storedPath,
                    "checkout_lat" => $data["checkout_lat"],
                    "checkout_long" => $data["checkout_long"],
                    "checkout_address" => $data["checkout_address"],
                    "checkout_region" => $data["region"],
                    "checkout_on_scope" => $data["on_scope"],
                    "catatan_out" => $data["catatan_out"],
                    "status" => "ATTEND",
                ]);
            DB::commit();
       } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkout gagal, coba kembali nanti - ", 422);
        }
        return $this->helper->customResponse("Checkout berhasil", 200, $data);
    }

    private function distance($lat, $long)
    {
        $distance = DB::select("select distance_location(?,?)", [$lat, $long]);
        if (count($distance)) {
            $location = json_decode($distance[0]->distance_location);
            return @$location[0] ?? false;
        } else {
            return false;
        }
    }

    public function custom_distance_check($req)
    {
        $distance = $this->distance($req->lat, $req->long);
        if ($distance) {
            $data["on_scope"] = true;
            $data["region"] = $distance->nama;
            $data["lat"] = $distance->lat;
            $data["long"] = $distance->long;
            $data["address"] = $req->address;
            $data["office"] = $distance->nama;
        } else {
            $data["on_scope"] = false;
            $data["region"] = "Out Scope";
            $data["lat"] = $req->lat;
            $data["long"] = $req->long;
            $data["address"] = $req->address;
            $data["office"] = null;
        }
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_status($model)
    {
        $data = [
            'status' => $this->where('tanggal', date('Y-m-d'))->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
        ];
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_status_get_jadwal_kerja(){
        $karyId = auth()->user()->m_kary_id;
        $getTodayNum = Carbon::today()->dayOfWeek;
        $startOfWeek = Carbon::today()->startOfWeek();
        $endOfweek = Carbon::today()->endOfweek();
        $now = Carbon::now();

        $t_jadwal_kerja_det = t_jadwal_kerja_det::where('m_kary_id',$karyId)->whereHas('t_jadwal_kerja_det_hari',function($query) {
            $query->whereIn('day_num',range(1,7));
        })->with(['t_jadwal_kerja_det_hari.m_jam_kerja' => function($select){
            $select->select('id','m_jam_kerja.is_hari_berikutnya');
        }])->get([
            'id','m_kary_id','t_jadwal_kerja_det_hari_id'
        ]);


        $t_jadwal_kerja_det = $t_jadwal_kerja_det->transform(function ($item){
            $tanggal = Carbon::today()->startOfWeek()->addDays($item->t_jadwal_kerja_det_hari->day_num - 1)->toDateString();
            $item->t_jadwal_kerja_det_hari->tanggal = $tanggal;
            $waktu_mulai = $item->t_jadwal_kerja_det_hari->waktu_mulai;
            $waktu_akhir = $item->t_jadwal_kerja_det_hari->waktu_akhir;
            $item->start_work = Carbon::parse("$tanggal $waktu_mulai")->subHours(2)->toDateTimeString();
            if($item->t_jadwal_kerja_det_hari->m_jam_kerja->is_hari_berikutnya == true){
                $item->end_work = Carbon::parse("$tanggal $waktu_akhir")->addDay()->addHours(2)->toDateTimeString();
            }else{
                 $item->end_work = Carbon::parse("$tanggal $waktu_akhir")->toDateTimeString();
            }
            return $item;
        });
        
        $startDayBeforeWeekend = $t_jadwal_kerja_det[6]['start_work'];
        $EndDayBeforeWeekend = $t_jadwal_kerja_det[6]['end_work'];

        $day_before = [
        "start_work" => Carbon::parse($startDayBeforeWeekend)->subWeek()->toDateTimeString(),
        "end_work" => Carbon::parse($EndDayBeforeWeekend)->subWeek()->toDateTimeString(),
        ];

        $t_jadwal_kerja_det->prepend($day_before);
        
        $getData = $t_jadwal_kerja_det->where('start_work', '<=', $now)
                            ->where('end_work', '>=', $now)
                            ->first();
        if($getData){
            $isPresent = $this->whereBetween('created_at', [$getData['start_work'],$getData['end_work']])->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND';
            $data = [
                'status' => $isPresent 
            ];
        }else{
            $data = [
                'status' => "ATTEND" 
            ];
        }
        return $this->helper->customResponse("OK", 200, $data);
    }


    public function scopeFilter($model)
    {
        if(req('date_from') && req('date_to')){
            return $model->whereBetween('tanggal',[req('date_from'),req('date_to')])->where('default_user_id', auth()->user()->id ?? 0);
        }
    }

    public function custom_get_absen($req)
    {
        $periode = ($req->periode ?? date('Y-m')).'-1';
        $m_kary_id = auth()->user()->m_kary_id;

        $data = \DB::select("
            select * from employee_attendance_detail(?, ?);
        ", [$periode,$m_kary_id ?? 0]);

        // transform object for mobile
        foreach($data as $dt){
            $dt->status = @json_decode($dt->absensi)->status ?? null;
            $dt->tanggal = @json_decode($dt->absensi)->tanggal ?? null;
            $dt->catatan_in = @json_decode($dt->absensi)->catatan_in ?? null;
            $dt->catatan_out = @json_decode($dt->absensi)->catatan_out ?? null;
            $dt->checkin_lat = @json_decode($dt->absensi)->checkin_lat ?? null;
            $dt->checkin_foto = ($inPic=@json_decode($dt->absensi)->checkin_foto) ? (str_contains($inPic,'http')?$inPic:url($inPic)) : null;
            $dt->checkin_long = @json_decode($dt->absensi)->checkin_long ?? null;
            $dt->checkin_time = @json_decode($dt->absensi)->checkin_time ?? null;
            $dt->checkout_lat = @json_decode($dt->absensi)->checkout_lat ?? null;
            $dt->checkout_foto = ($outPic=@json_decode($dt->absensi)->checkout_foto) ? (str_contains($outPic,'http')?$outPic:url($outPic)) : null;
            $dt->checkout_long = @json_decode($dt->absensi)->checkout_long ?? null;
            $dt->checkout_time = @json_decode($dt->absensi)->checkout_time ?? null;
            $dt->checkin_region = @json_decode($dt->absensi)->checkin_region ?? null;
            $dt->checkin_address = @json_decode($dt->absensi)->checkin_address ?? null;
            $dt->checkout_region = @json_decode($dt->absensi)->checkout_region ?? null;
            $dt->checkin_on_scope = @json_decode($dt->absensi)->checkin_on_scope ?? null;
            $dt->checkout_address = @json_decode($dt->absensi)->checkout_address ?? null;
            $dt->checkout_on_scope = @json_decode($dt->absensi)->checkout_on_scope ?? null;
            $dt->presensi_absensi_id = @json_decode($dt->absensi)->presensi_absensi_id ?? null;
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    // public function public_exportPresensi()
    // {
    //     try {
    //         $req = request();

    //         $date_start = Carbon::parse($req->date_start)->format('Y-m-d');
    //         $date_end   = Carbon::parse($req->date_end)->format('Y-m-d');
    //         $currentMonth = date('m');

    //         $karyQuery = m_kary::with([
    //             't_jadwal_kerja.t_jadwal_kerja_det_hari',
    //             'm_dir',
    //             'm_divisi'
    //         ]);

    //         if ($req->filled('kary_id')) {
    //             $ids = array_map('intval', explode(',', $req->kary_id));
    //             $karyQuery->whereIn('id', $ids);
    //         } else {
    //             if ($req->filled('m_divisi_id')) {
    //                 $karyQuery->where('m_divisi_id', $req->m_divisi_id);
    //             }
    //             if ($req->filled('m_dir_id')) {
    //                 $karyQuery->where('m_dir_id', $req->m_dir_id);
    //             }
    //         }

    //         $karyawanList = $karyQuery->get();

    //         $rows = [];

    //         foreach ($karyawanList as $kary) {

    //             $kary_id = $kary->id;

    //             $data = DB::select("
    //                 select * from employee_attendance_detail_range(?,?,?)
    //             ", [$date_start, $date_end, $kary_id]);

    //             $jadwalHariList = $kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day');

    //             $cutiList = t_cuti::where('m_kary_id', $kary_id)
    //                 ->with('alasan')
    //                 ->where('status', 'APPROVED')
    //                 ->get();

    //             $liburList = m_libur_nasional::whereBetween('tanggal', [$date_start, $date_end])->get();

    //             foreach ($data as $dt) {
                    
    //                 $tanggal = Carbon::parse($dt->all_days_of_range)->format('Y-m-d');
    //                 $absensi = json_decode($dt->absensi, true);

    //                 // 1. Ambil jadwal hari ini SEGERA setelah loop dimulai
    //                 $jadwal = $jadwalHariList[$dt->day_name_idn] ?? null;

    //                 // 2. Tentukan tipe hari awal
    //                 $tipeHari = $jadwal->tipe_hari ?? 'LIBUR/OFF'; 

    //                 // 3. Cek Libur Nasional (Overwrites tipe hari)
    //                 $libur = $liburList->where('tanggal', $tanggal)->first();
    //                 $isLibur = !empty($libur);
    //                 if ($isLibur) {
    //                     $tipeHari = 'LIBUR (' . $libur->desc . ')'; 
    //                 }

    //                 $isCuti = false;
    //                 $alasan_cuti = "-";
    //                 $det_approval_cuti = null;

    //                 foreach ($cutiList as $cuti) {
    //                     $from = Carbon::parse($cuti->date_from)->format('Y-m-d');
    //                     $to   = Carbon::parse($cuti->date_to)->format('Y-m-d');

    //                     if ($tanggal >= $from && $tanggal <= $to) {
    //                         $isCuti = true;
    //                         $alasan_cuti = $cuti->alasan?->value;
    //                         // $approval_cuti = generate_approval::where('form_name', 't_cuti')->where('trx_id', $cuti->id);
    //                         $det_approval_cuti = generate_approval_det::whereHas('generate_approval', function($q) use ($cuti){
    //                             $q->where('form_name', 't_cuti')->where('trx_id', $cuti->id);
    //                         })->where('action_type', 'APPROVED')->first();
    //                     }
    //                 }

    //                 // Ambil jadwal hari ini
    //                 $jadwal = $jadwalHariList[$dt->day_name_idn] ?? null;

    //                 $checkin = $absensi['checkin_time'] ?? '';
    //                 $checkout = $absensi['checkout_time'] ?? '';

    //                 $rows[] = [
    //                     'ID KARYAWAN'    => json_decode($dt->kary)->nik ?? '',
    //                     'NAMA'           => json_decode($dt->kary)->nama_lengkap ?? '',
    //                     'UNIT'           => $kary?->m_dir?->nama ?? '',
    //                     'JABATAN'        => $kary?->m_divisi?->nama ?? '',
    //                     'TANGGAL'        => $dt->date_to_idn ? Carbon::parse($dt->date_to_idn)->format('d-m-Y') 
    //                         : '-',  
    //                     'HARI'           => $dt->day_name_idn,
    //                     'TIPE_HARI'      => $tipeHari ?? '-',
    //                     'STATUS'         => $absensi['status'] ?? '-',
    //                     'CHECKIN_TIME'   => $checkin ,    
    //                     'CHECKIN_SCOPE'  => ($absensi['checkin_on_scope'] ?? null) ? 'IN SCOPE' : 'OUT SCOPE',
    //                     'CHECKIN_NOTE'   => $absensi['catatan_in'] ?? '-',
    //                     'CHECKOUT_TIME'  => $checkout ,
    //                     'CHECKOUT_SCOPE' => ($absensi['checkout_on_scope'] ?? null) ? 'IN SCOPE' : 'OUT SCOPE',
    //                     'CHECKOUT_NOTE'  => $absensi['catatan_out'] ?? '-',
    //                     'IJIN'           => $alasan_cuti,
    //                     'CATATAN_APPROVAL' => $det_approval_cuti ? $det_approval_cuti->action_note : '',
    //                 ];
    //             }
    //         }

    //         $export = new class(collect($rows)) implements FromCollection, WithHeadings, WithStyles {

    //             protected $data;
    //             public function __construct($data) { $this->data = $data; }
    //             public function collection() { return $this->data; }

    //             public function headings(): array
    //             {
    //                 return [
    //                     'ID KARYAWAN',
    //                     'NAMA',
    //                     'UNIT',
    //                     'JABATAN',
    //                     'TANGGAL',
    //                     'HARI',
    //                     'TIPE HARI',
    //                     'STATUS',
    //                     'CHECKIN TIME',
    //                     'CHECKIN SCOPE',
    //                     'CHECKIN NOTE',
    //                     'CHECKOUT TIME',
    //                     'CHECKOUT SCOPE',
    //                     'CHECKOUT NOTE',
    //                     'IJIN',
    //                     'CATATAN APPROVAL',
    //                 ];
    //             }

    //             public function styles(Worksheet $sheet)
    //             {
    //                 foreach ($this->data as $index => $row) {
    //                     $rowIndex = $index + 2; // Baris data dimulai dari baris ke-2

    //                     // Cek jika salah satu (Checkin atau Checkout) berada di luar scope
    //                     $isOutScope = ($row['CHECKIN_SCOPE'] === 'OUT SCOPE' || $row['CHECKOUT_SCOPE'] === 'OUT SCOPE');

    //                     if ($isOutScope) {
    //                         // Mewarnai satu baris penuh dari kolom A sampai P
    //                         $sheet->getStyle('A' . $rowIndex . ':P' . $rowIndex)->applyFromArray([
    //                             'font' => [
    //                                 'color' => ['argb' => 'FFFF0000'], // Teks jadi merah
    //                                 'bold'  => true,
    //                             ],
    //                             'fill' => [
    //                                 'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //                                 'startColor' => ['argb' => 'FFFFEBEB'], // Background merah sangat muda agar teks tetap terbaca
    //                             ],
    //                         ]);
    //                     }
    //                 }

    //                 return [];
    //             }
    //         };

    //         $fileName = "presensi_{$date_start}_{$date_end}.xlsx";
    //         return Excel::download($export, $fileName);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Export gagal: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function public_exportPresensi()
    {
        try {
            $req = request();
            $date_start = Carbon::parse($req->date_start)->format('Y-m-d');
            $date_end   = Carbon::parse($req->date_end)->format('Y-m-d');

            // dd($req);
            $karyQuery = m_kary::with(['t_jadwal_kerja.t_jadwal_kerja_det_hari', 'm_dir', 'm_divisi']);

            if ($req->filled('kary_id')) {
                $ids = array_map('intval', explode(',', $req->kary_id));
                $karyQuery->whereIn('id', $ids);
            } else {
                if ($req->filled('m_divisi_id')) $karyQuery->where('m_divisi_id', $req->m_divisi_id);
                if ($req->filled('m_dir_id')) $karyQuery->where('m_dir_id', $req->m_dir_id);
            }

            if ($req->filled('is_active')) {
                // $isActive = (boolean)($req->is_active);
                $isActive = filter_var($req->is_active, FILTER_VALIDATE_BOOLEAN);
                $karyQuery->where('is_active', $isActive);
                // dd($isActive);
            }else{
                $karyQuery->where('is_active', true);
            }


            $karyawanList = $karyQuery->get();
            $rows = [];

            foreach ($karyawanList as $kary) {
                $data = DB::select("select * from employee_attendance_detail_range(?,?,?)", [$date_start, $date_end, $kary->id]);
                $jadwalHariList = $kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day');
                $liburList = m_libur_nasional::whereBetween('tanggal', [$date_start, $date_end])->get();

                $cutiList = t_cuti::where('m_kary_id', $kary->id)
                    ->with('alasan')
                    ->where('status', 'APPROVED')
                    ->get();

                foreach ($data as $dt) {
                    $tanggal = Carbon::parse($dt->all_days_of_range)->format('Y-m-d');
                    $absensi = json_decode($dt->absensi, true);
                    $jadwal = $jadwalHariList[$dt->day_name_idn] ?? null;
                    
                    // Tentukan Tipe Hari & Libur
                    $liburNasional = $liburList->where('tanggal', $tanggal)->first();
                    $tipeHari = $liburNasional ? 'LIBUR (' . $liburNasional->desc . ')' : ($jadwal->tipe_hari ?? 'LIBUR');

                    // Cek Cuti
                    $isCuti = false;
                    $alasan_cuti = "-";
                    $det_approval_cuti = null;
                    foreach ($cutiList as $cuti) {
                        $from = Carbon::parse($cuti->date_from)->format('Y-m-d');
                        $to   = Carbon::parse($cuti->date_to)->format('Y-m-d');
                        if ($tanggal >= $from && $tanggal <= $to) {
                            $isCuti = true;
                            $alasan_cuti = $cuti->alasan?->value;
                            $det_approval_cuti = generate_approval_det::whereHas('generate_approval', function($q) use ($cuti){
                                $q->where('form_name', 't_cuti')->where('trx_id', $cuti->id);
                            })->where('action_type', 'APPROVED')->first();
                        }
                    }

                    $checkin = $absensi['checkin_time'] ?? '';
                    $checkout = $absensi['checkout_time'] ?? '';
                    $statusAbsen = $absensi['status'] ?? 'NOT ATTEND';

                    // Hitung Telat
                    $lateMinutes = 0;
                    if (!empty($checkin) && !empty($jadwal->waktu_mulai)) {
                        $waktuJadwal = Carbon::createFromFormat('H:i:s', $jadwal->waktu_mulai);
                        $waktuAktual = Carbon::createFromFormat('H:i:s', Carbon::parse($checkin)->format('H:i:s'));
                        if ($waktuAktual->gt($waktuJadwal)) {
                            $lateMinutes = $waktuJadwal->diffInMinutes($waktuAktual);
                        }
                    }

                    $earlyCheckoutMinutes = 0;

                    if (!empty($checkout) && !empty($jadwal->waktu_akhir)) {
                        $waktuJadwalPulang = Carbon::createFromFormat('H:i:s', $jadwal->waktu_akhir);
                        
                        $waktuAktualPulang = Carbon::parse($checkout);
                        $aktualPulangHms = Carbon::createFromFormat('H:i:s', $waktuAktualPulang->format('H:i:s'));

                        if ($aktualPulangHms->lt($waktuJadwalPulang)) {
                            $earlyCheckoutMinutes = $aktualPulangHms->diffInMinutes($waktuJadwalPulang);
                        }
                    }

                    // --- LOGIKA WARNA SESUAI GAMBAR ---
                    // Merah jika: Status bukan ATTEND (termasuk Libur/Minggu yang kosong) ATAU ada menit telat
                    $isHighlighted = ($statusAbsen !== 'ATTEND' || $lateMinutes > 0 || $earlyCheckoutMinutes > 0);
                    
                    // Kecuali kalau dia sedang CUTI/IJIN yang sudah APPROVED, jangan dimerahin
                    if ($isCuti) $isHighlighted = false;

                    if ($tipeHari && str_contains(strtoupper($tipeHari), 'LIBUR')) {
                        $isHighlighted = false;
                    }

                    $overtimeMinutes = 0;
                    if (!empty($checkout) && !empty($jadwal->waktu_akhir) && $statusAbsen === 'ATTEND') {
                        $waktuJadwalPulang = Carbon::createFromFormat('H:i:s', $jadwal->waktu_akhir);
                        $waktuAktualPulang = Carbon::createFromFormat('H:i:s', Carbon::parse($checkout)->format('H:i:s'));

                        if ($waktuAktualPulang->gt($waktuJadwalPulang)) {
                            $overtimeMinutes = $waktuJadwalPulang->diffInMinutes($waktuAktualPulang);                        
                            // Opsional: Biasanya perusahaan memberikan Grace Period (misal lembur baru dihitung jika > 30 menit)
                            // if ($overtimeMinutes < 30) $overtimeMinutes = 0; 
                        }
                    }

                    $rows[] = [
                        'ID KARYAWAN'    => json_decode($dt->kary)->nik ?? '',
                        'NAMA'           => json_decode($dt->kary)->nama_lengkap ?? '',
                        'UNIT'           => $kary->m_dir->nama ?? '-',
                        'JABATAN'        => $kary->m_divisi->nama ?? '-',
                        'TANGGAL'        => $dt->date_to_idn ? Carbon::parse($dt->date_to_idn)->format('d-m-Y') : '-',
                        'HARI'           => $dt->day_name_idn,
                        'TIPE HARI'      => $tipeHari,
                        'STATUS'         => $statusAbsen,
                        'CHECKIN TIME'   => $checkin ?: '-',
                        'CHECKIN SCOPE'  => ($absensi['checkin_on_scope'] ?? null) ? 'IN SCOPE' : 'OUT SCOPE',
                        'CHECKIN NOTE'   => $absensi['catatan_in'] ?? '-',
                        'CHECKOUT TIME'  => $checkout ?: '-',
                        'CHECKOUT SCOPE' => ($absensi['checkout_on_scope'] ?? null) ? 'IN SCOPE' : 'OUT SCOPE',
                        'CHECKOUT NOTE'  => $absensi['catatan_out'] ?? '-',
                        'TERLAMBAT'      => $lateMinutes > 0 ? $lateMinutes : '-',
                        'CHECKOUT AWAL'  => $earlyCheckoutMinutes > 0 ? $earlyCheckoutMinutes : '-',
                        'IJIN'           => $alasan_cuti,
                        'OVERTIME MINUTE' => $overtimeMinutes,
                        'CATATAN APPROVAL' => $det_approval_cuti ? $det_approval_cuti->action_note : '-',
                        'IS_HIGHLIGHTED' => $isHighlighted,
                    ];
                }
            }

            $export = new class(collect($rows)) implements FromCollection, WithHeadings, WithStyles {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { 
                    return $this->data->map(function($item) {
                        $res = $item; unset($res['IS_HIGHLIGHTED']); return $res;
                    });
                }
                public function headings(): array {
                    return [
                        'ID KARYAWAN', 'NAMA', 'UNIT', 'JABATAN', 'TANGGAL', 'HARI', 'TIPE HARI', 'STATUS',
                        'CHECKIN TIME', 'CHECKIN SCOPE', 'CHECKIN NOTE', 'CHECKOUT TIME', 'CHECKOUT SCOPE',
                        'CHECKOUT NOTE', 'TERLAMBAT', 'CHECKOUT AWAL', 'IJIN', 'OVERTIME MINUTE', 'CATATAN APPROVAL'
                    ];
                }
                public function styles(Worksheet $sheet) {
                    $sheet->getStyle('A1:S1')->getFont()->setBold(true);
                    foreach ($this->data as $index => $row) {
                        $rowIndex = $index + 2;
                        if ($row['IS_HIGHLIGHTED']) {
                            $sheet->getStyle('A' . $rowIndex . ':S' . $rowIndex)->applyFromArray([
                                'font' => ['color' => ['argb' => 'FFFF0000'], 'bold' => true],
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFFFEBEB'],
                                ],
                            ]);
                        }
                    }
                    return [];
                }
            };

        // Menggunakan format: presensi_detail_2026-01-01_s_d_2026-01-31.xlsx
        return Excel::download($export, "presensi_detail_{$date_start}_s_d_{$date_end}.xlsx");

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function public_exportPresensiRekap()
    {
        try {
            $req = request();
            $date_start = Carbon::parse($req->date_start)->format('Y-m-d');
            $date_end   = Carbon::parse($req->date_end)->format('Y-m-d');

            $liburNasional = m_libur_nasional::whereBetween('tanggal', [$date_start, $date_end])
                    ->pluck('tanggal')
                    ->toArray();
                    
            $karyQuery = m_kary::with(['t_jadwal_kerja.t_jadwal_kerja_det_hari', 'm_dir', 'm_divisi']);

            if ($req->filled('kary_id')) {
                $ids = array_map('intval', explode(',', $req->kary_id));
                $karyQuery->whereIn('id', $ids);
            } else {
                if ($req->filled('m_divisi_id')) $karyQuery->where('m_divisi_id', $req->m_divisi_id);
                if ($req->filled('m_dir_id')) $karyQuery->where('m_dir_id', $req->m_dir_id);
            }

            $karyawanList = $karyQuery->get();
            $rows = [];

            foreach ($karyawanList as $kary) {
                $data = DB::select("select * from employee_attendance_detail_range(?,?,?)", [$date_start, $date_end, $kary->id]);
                $jadwalHariList = $kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day');

                // --- AMBIL DATA CUTI/IJIN DAN JADIKAN ARRAY TANGGAL ---
                $cutiList = t_cuti::where('m_kary_id', $kary->id)
                    ->where('status', 'APPROVED')
                    ->where(function($q) use ($date_start, $date_end) {
                        // Ambil cuti yang bersinggungan dengan periode tarikan
                        $q->where('date_from', '<=', $date_end)
                            ->where('date_to', '>=', $date_start);
                    })->get();

                $arrayTanggalCuti = [];
                foreach ($cutiList as $cuti) {
                    $start = Carbon::parse(max($cuti->date_from, $date_start));
                    $end = Carbon::parse(min($cuti->date_to, $date_end));
                    // Looping tanggal cuti dan masukkan ke array
                    for ($d = $start; $d->lte($end); $d->addDay()) {
                        $arrayTanggalCuti[] = $d->format('Y-m-d');
                    }
                }
                $arrayTanggalCuti = array_unique($arrayTanggalCuti);

                $rekap = [
                    'total_hari_kerja' => 0,
                    'total_hadir'      => 0,
                    'total_ijin_cuti'  => 0,
                    'total_alpha'      => 0, // <-- TAMBAHAN BARU
                    'freq_terlambat'   => 0,
                    'durasi_terlambat' => 0,
                    'freq_pulang_cepat'=> 0,
                    'detail_ijin'      => [],
                ];

                foreach ($data as $dt) {
                    $absensi = json_decode($dt->absensi, true);
                    $jadwal = $jadwalHariList[$dt->day_name_idn] ?? null;
                    $tanggalCurrent = Carbon::parse($dt->all_days_of_range)->format('Y-m-d');

                    $isLiburNasional = in_array($tanggalCurrent, $liburNasional);
                    $tipeHari = $isLiburNasional ? 'LIBUR' : ($jadwal->tipe_hari ?? 'LIBUR');
                    
                    if ($tipeHari == 'KERJA') {
                        $rekap['total_hari_kerja']++;

                        if (($absensi['status'] ?? '') == 'ATTEND') {
                            $rekap['total_hadir']++;

                            // --- HITUNG TERLAMBAT MANUAL ---
                            $jamMasukJadwal = $jadwal->waktu_mulai ?? null;
                            $jamMasukAktual = $absensi['checkin_time'] ?? null;

                            if ($jamMasukJadwal && $jamMasukAktual) {
                                $waktuJadwal = Carbon::createFromFormat('H:i:s', $jamMasukJadwal);
                                $waktuAktual = Carbon::parse($jamMasukAktual);

                                if ($waktuAktual->format('H:i:s') > $waktuJadwal->format('H:i:s')) {
                                    $diff = $waktuJadwal->diffInMinutes(Carbon::createFromFormat('H:i:s', $waktuAktual->format('H:i:s')));
                                    $rekap['freq_terlambat']++;
                                    $rekap['durasi_terlambat'] += $diff;
                                }
                            }

                            // --- HITUNG PULANG CEPAT MANUAL ---
                            $jamPulangJadwal = $jadwal->waktu_akhir ?? null;
                            $jamPulangAktual = $absensi['checkout_time'] ?? null;

                            if ($jamPulangJadwal && $jamPulangAktual) {
                                $waktuJadwal = Carbon::createFromFormat('H:i:s', $jamPulangJadwal);
                                $waktuAktual = Carbon::parse($jamPulangAktual);
                                $waktuAktualFormatted = Carbon::createFromFormat('H:i:s', $waktuAktual->format('H:i:s'));

                                if ($waktuAktualFormatted->lt($waktuJadwal)) {
                                    $rekap['freq_pulang_cepat']++;
                                }
                            }
                        } else {
                            // --- JIKA TIDAK ATTEND DI HARI KERJA ---
                            if (in_array($tanggalCurrent, $arrayTanggalCuti)) {
                                // Jika tanggal ini ada di daftar ijin/cuti
                                $rekap['total_ijin_cuti']++;
                            } else {
                                // Jika tidak attend & tidak ada ijin/cuti -> ALPHA / TANPA KETERANGAN
                                $rekap['total_alpha']++;
                            }
                        }
                    }
                }

                $rows[] = [
                    'ID KARYAWAN'       => $kary->kode,
                    'NAMA'              => $kary->nama_lengkap,
                    'UNIT'              => $kary->m_dir->nama ?? '-',
                    'PERIODE'           => "$date_start s/d $date_end",
                    'HARI KERJA'        => $rekap['total_hari_kerja'],
                    'TOTAL HADIR'       => $rekap['total_hadir'],
                    'TOTAL IJIN'        => $rekap['total_ijin_cuti'],
                    'TANPA KETERANGAN'  => $rekap['total_alpha'], // <-- TAMBAHAN BARU DI EXCEL
                    'FREQ TERLAMBAT'    => $rekap['freq_terlambat'],
                    'FREQ TERLAMBAT COUNT' => "kali",
                    'DURASI TERLAMBAT'  => $rekap['durasi_terlambat'],
                    'DURASI TERLAMBAT COUNT' => "menit",
                    'PULANG CEPAT'      => $rekap['freq_pulang_cepat'],
                    'PULANG CEPAT COUNT'=> "kali",
                    'PERSENTASE HADIR'  => ($rekap['total_hari_kerja'] > 0) 
                                            ? round(($rekap['total_hadir'] / $rekap['total_hari_kerja']) * 100, 2) . "%" 
                                            : "0%"
                ];
            }

            $export = new class(collect($rows)) implements FromCollection, WithHeadings, WithStyles {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    // Tambahkan Header TANPA KETERANGAN
                    return ['ID KARYAWAN', 'NAMA', 'UNIT', 'PERIODE', 'HARI KERJA', 'HADIR', 'IJIN', 'TANPA KETERANGAN', 'FREQ LATE', 'FREQ KALI', 'DURASI LATE', 'DURASI MENIT', 'EARLY OUT', 'CEPAT KALI', 'PRESENTASE'];
                }
                public function styles(Worksheet $sheet) {
                    // Rubah rentang style huruf tebal sampai kolom O karena ada tambahan 1 kolom
                    $sheet->getStyle('A1:O1')->getFont()->setBold(true);
                    return [];
                }
            };

            return Excel::download($export, "rekap_presensi_{$date_start}.xlsx");

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }
//   public function public_exportPresensiRekap()
//   {
//       try {
//           $req = request();
//           $date_start = Carbon::parse($req->date_start)->format('Y-m-d');
//           $date_end   = Carbon::parse($req->date_end)->format('Y-m-d');

//           $liburNasional = m_libur_nasional::whereBetween('tanggal', [$date_start, $date_end])
//                 ->pluck('tanggal') // Kita hanya butuh list tanggalnya
//                 ->toArray();
//           $karyQuery = m_kary::with(['t_jadwal_kerja.t_jadwal_kerja_det_hari', 'm_dir', 'm_divisi']);

//           if ($req->filled('kary_id')) {
//               $ids = array_map('intval', explode(',', $req->kary_id));
//               $karyQuery->whereIn('id', $ids);
//           } else {
//               if ($req->filled('m_divisi_id')) $karyQuery->where('m_divisi_id', $req->m_divisi_id);
//               if ($req->filled('m_dir_id')) $karyQuery->where('m_dir_id', $req->m_dir_id);
//           }

//           $karyawanList = $karyQuery->get();
//           $rows = [];

//           foreach ($karyawanList as $kary) {
//               $data = DB::select("select * from employee_attendance_detail_range(?,?,?)", [$date_start, $date_end, $kary->id]);
//               $jadwalHariList = $kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day');

//               $rekap = [
//                   'total_hari_kerja' => 0,
//                   'total_hadir'      => 0,
//                   'total_ijin_cuti'  => 0,
//                   'freq_terlambat'   => 0,
//                   'durasi_terlambat' => 0,
//                   'freq_pulang_cepat'=> 0,
//                   'detail_ijin'      => [],
//               ];

//               foreach ($data as $dt) {
//                   $absensi = json_decode($dt->absensi, true);
//                   $jadwal = $jadwalHariList[$dt->day_name_idn] ?? null;
//                   $tipeHari = $jadwal->tipe_hari ?? 'LIBUR';
//                   $tanggalCurrent = Carbon::parse($dt->all_days_of_range)->format('Y-m-d');
//                   //dd($jadwal);

//                   $isLiburNasional = in_array($tanggalCurrent, $liburNasional);
//                   $tipeHari = $isLiburNasional ? 'LIBUR' : ($jadwal->tipe_hari ?? 'LIBUR');
                  
//                   if ($tipeHari == 'KERJA') {
//                       $rekap['total_hari_kerja']++;
//                   }

//                   if (($absensi['status'] ?? '') == 'ATTEND') {
//                       $rekap['total_hadir']++;

//                       // --- HITUNG TERLAMBAT MANUAL ---
//                       $jamMasukJadwal = $jadwal->waktu_mulai ?? null;
//                       $jamMasukAktual = $absensi['checkin_time'] ?? null;

//                      if ($jamMasukJadwal && $jamMasukAktual) {
//                           // Pastikan kita hanya membandingkan JAM:MENIT:DETIK
//                           $waktuJadwal = Carbon::createFromFormat('H:i:s', $jamMasukJadwal);
//                           $waktuAktual = Carbon::parse($jamMasukAktual);

//                           // Bandingkan hanya jam dan menitnya saja
//                           if ($waktuAktual->format('H:i:s') > $waktuJadwal->format('H:i:s')) {
//                               $diff = $waktuJadwal->diffInMinutes(Carbon::createFromFormat('H:i:s', $waktuAktual->format('H:i:s')));
//                               $rekap['freq_terlambat']++;
//                               $rekap['durasi_terlambat'] += $diff;
//                           }
//                       }

//                       // --- HITUNG PULANG CEPAT MANUAL ---
//                       $jamPulangJadwal = $jadwal->waktu_akhir ?? null;
//                       $jamPulangAktual = $absensi['checkout_time'] ?? null;

//                       if ($jamPulangJadwal && $jamPulangAktual) {
//                           $waktuJadwal = Carbon::createFromFormat('H:i:s', $jamPulangJadwal);
                          
//                           $waktuAktual = Carbon::parse($jamPulangAktual);
//                           $waktuAktualFormatted = Carbon::createFromFormat('H:i:s', $waktuAktual->format('H:i:s'));

//                           if ($waktuAktualFormatted->lt($waktuJadwal)) {
//                               $rekap['freq_pulang_cepat']++;
//                           }
//                       }
//                   }

//                   $rekap['total_ijin_cuti'] = t_cuti::where('m_kary_id', $kary->id)
//                     ->where('status', 'APPROVED')
//                     ->where(function($q) use ($date_start, $date_end) {
//                         $q->whereBetween('date_from', [$date_start, $date_end])
//                           ->orWhereBetween('date_to', [$date_start, $date_end]);
//                     })
//                     ->count();
//               }

//               $rows[] = [
//                   'ID KARYAWAN'       => $kary->kode,
//                   'NAMA'              => $kary->nama_lengkap,
//                   'UNIT'              => $kary->m_dir->nama ?? '-',
//                   'PERIODE'           => "$date_start s/d $date_end",
//                   'HARI KERJA'        => $rekap['total_hari_kerja'],
//                   'TOTAL HADIR'       => $rekap['total_hadir'],
//                   'TOTAL IJIN'        => $rekap['total_ijin_cuti'],
//                   'FREQ TERLAMBAT'    => $rekap['freq_terlambat'],
//                   'FREQ TERLAMBAT COUNT'         => "kali",
//                   'DURASI TERLAMBAT'  => $rekap['durasi_terlambat'],
//                   'DURASI TERLAMBAT COUNT'      => "menit",
//                   'PULANG CEPAT'      => $rekap['freq_pulang_cepat'],
//                   'PULANG CEPAT COUNT'        => "kali",
//                   'PERSENTASE HADIR'  => ($rekap['total_hari_kerja'] > 0) 
//                                           ? round(($rekap['total_hadir'] / $rekap['total_hari_kerja']) * 100, 2) . "%" 
//                                           : "0%"
//               ];
//           }

//           $export = new class(collect($rows)) implements FromCollection, WithHeadings, WithStyles {
//               protected $data;
//               public function __construct($data) { $this->data = $data; }
//               public function collection() { return $this->data; }
//               public function headings(): array {
//                   return ['ID KARYAWAN', 'NAMA', 'UNIT', 'PERIODE', 'HARI KERJA', 'HADIR', 'IJIN', 'FREQ LATE', 'FREQ KALI', 'DURASI LATE', 'DURASI MENIT', 'EARLY OUT', 'CEPAT KALI', 'PRESENTASE'];
//               }
//               public function styles(Worksheet $sheet) {
//                   $sheet->getStyle('A1:N1')->getFont()->setBold(true);
//                   return [];
//               }
//           };

//           return Excel::download($export, "rekap_presensi_{$date_start}.xlsx");

//       } catch (\Exception $e) {
//           return response()->json(['error' => $e->getMessage()], 500);
//       }
//   }

  public function public_exportRealisasiLembur()
  {
      try {
          $req = request();
          $date_start = Carbon::parse($req->date_start)->format('Y-m-d');
          $date_end   = Carbon::parse($req->date_end)->format('Y-m-d');

          $lemburList = t_lembur::with(['m_kary.m_dir', 'm_kary.m_divisi', 'm_kary.t_jadwal_kerja.t_jadwal_kerja_det_hari'])
              ->whereBetween('tanggal', [$date_start, $date_end])
              ->where('status', 'APPROVED')
              ->get();

          $rows = [];

          foreach ($lemburList as $lembur) {
              $kary_id = $lembur->m_kary_id;
              $tanggal = Carbon::parse($lembur->tanggal)->format('Y-m-d');

              // 1. Hitung DURASI REQ (Jam Mulai s/d Jam Selesai dari tabel lembur)
              $jamMulaiReq = $lembur->jam_mulai;
              $jamSelesaiReq = $lembur->jam_selesai;
              $durasiReqJam = 0;

              if ($jamMulaiReq && $jamSelesaiReq) {
                  $startReq = Carbon::parse($tanggal . ' ' . $jamMulaiReq);
                  $endReq = Carbon::parse($tanggal . ' ' . $jamSelesaiReq);
                  
                  // Jika lembur melewati tengah malam, tambahkan 1 hari pada endReq
                  if ($endReq->lt($startReq)) {
                      $endReq->addDay();
                  }
                  
                  $durasiReqJam = round($startReq->diffInMinutes($endReq) / 60, 2);
              }

              // 2. Ambil data absensi aktual
              $absensiData = DB::select("select * from employee_attendance_detail_range(?,?,?)", [$tanggal, $tanggal, $kary_id]);
              $dt = $absensiData[0] ?? null;
              $absensi = $dt ? json_decode($dt->absensi, true) : null;

              // 3. Ambil Jam Pulang Jadwal
              $jadwalHariList = $lembur->m_kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day');
              $jadwalHariIni = $jadwalHariList[$dt->day_name_idn] ?? null;
              $jamPulangJadwal = $jadwalHariIni->waktu_akhir ?? null; 
              
              $checkoutActual = $absensi['checkout_time'] ?? null;
              $durasiRealisasiJam = 0;

              // 4. Hitung REALISASI (Jam Pulang Jadwal s/d Checkout Aktual)
              if ($jamPulangJadwal && $checkoutActual) {
                  $waktuPulangJadwal = Carbon::parse($tanggal . ' ' . $jamPulangJadwal);
                  $waktuCheckoutActual = Carbon::parse($checkoutActual);

                  if ($waktuCheckoutActual->gt($waktuPulangJadwal)) {
                      $durasiRealisasiJam = round($waktuPulangJadwal->diffInMinutes($waktuCheckoutActual) / 60, 2);
                  }
              }

              $rows[] = [
                  'ID KARYAWAN'       => $lembur->m_kary->kode ?? '-',
                  'NAMA'              => $lembur->m_kary->nama_lengkap ?? '-',
                  'TANGGAL'           => $tanggal,
                  'JAM MULAI (REQ)'   => $jamMulaiReq,
                  'JAM SELESAI (REQ)' => $jamSelesaiReq,
                  'DURASI REQ (JAM)'  => $durasiReqJam,
                  'JAM PULANG JADWAL' => $jamPulangJadwal ?: '-',
                  'CHECKOUT AKTUAL'   => $checkoutActual ?: 'TIDAK ABSEN',
                  'REALISASI (JAM)'   => $durasiRealisasiJam,
                  'SELISIH (JAM)'     => round($durasiRealisasiJam - $durasiReqJam, 2),
                  'STATUS ABSEN'      => $absensi['status'] ?? '-',
                  'ALASAN LEMBUR'     => $lembur->keterangan,
              ];
          }

          $export = new class(collect($rows)) implements FromCollection, WithHeadings {
              protected $data;
              public function __construct($data) { $this->data = $data; }
              public function collection() { return $this->data; }
              public function headings(): array {
                  return [
                      'ID KARYAWAN', 'NAMA', 'TANGGAL', 'MULAI REQ', 'SELESAI REQ', 
                      'DURASI REQ (JAM)', 'JAM PULANG JADWAL', 'CHECKOUT AKTUAL', 
                      'REALISASI (JAM)', 'SELISIH', 'STATUS ABSEN', 'ALASAN'
                  ];
              }
          };

          return Excel::download($export, "realisasi_lembur_detail_{$date_start}.xlsx");

      } catch (\Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }

  public function public_exportPelanggaranPresensiOld()
  {
      try {
          $req = request();
          $date_start = Carbon::parse($req->date_start)->format('Y-m-d');
          $date_end   = Carbon::parse($req->date_end)->format('Y-m-d');

          // 1. Inisialisasi Query Builder dengan eager loading t_cuti
          $karyQuery = m_kary::with([
              'm_dir', 
              'm_divisi', 
              't_jadwal_kerja.t_jadwal_kerja_det_hari',
              't_cuti' => function($q) use ($date_start, $date_end) {
                  $q->where('status', 'APPROVED')
                    ->where(function($query) use ($date_start, $date_end) {
                        $query->whereBetween('date_from', [$date_start, $date_end])
                              ->orWhereBetween('date_to', [$date_start, $date_end]);
                    });
              }
          ]);

          // 2. Terapkan Filter Karyawan / Divisi / Direktorat
          if ($req->filled('kary_id')) {
              $ids = array_map('intval', explode(',', $req->kary_id));
              $karyQuery->whereIn('id', $ids);
          } else {
              if ($req->filled('m_divisi_id')) $karyQuery->where('m_divisi_id', $req->m_divisi_id);
              if ($req->filled('m_dir_id')) $karyQuery->where('m_dir_id', $req->m_dir_id);
          }

          $karyawanList = $karyQuery->get();
          $rows = [];

          foreach ($karyawanList as $kary) {
              $data = DB::select("select * from employee_attendance_detail_range(?,?,?)", [$date_start, $date_end, $kary->id]);
              $jadwalHariList = $kary->t_jadwal_kerja->t_jadwal_kerja_det_hari->keyBy('day');
              
              // Ambil daftar tanggal cuti karyawan ini agar tidak perlu query berulang di dalam loop hari
              $listTanggalCuti = [];
              foreach ($kary->t_cuti as $c) {
                  $period = CarbonPeriod::create($c->date_from, $c->date_to);
                  foreach ($period as $date) {
                      $listTanggalCuti[] = $date->format('Y-m-d');
                  }
              }

              foreach ($data as $dt) {
                  $tanggal = Carbon::parse($dt->all_days_of_range)->format('Y-m-d');
                  $absensi = json_decode($dt->absensi, true);
                  $jadwal = $jadwalHariList[$dt->day_name_idn] ?? null;
                  
                  if (($jadwal->tipe_hari ?? '') !== 'KERJA') continue;

                  // --- CEK APAKAH SEDANG CUTI ---
                  if (in_array($tanggal, $listTanggalCuti)) continue;

                  $checkin  = $absensi['checkin_time'] ?? null;
                  $checkout = $absensi['checkout_time'] ?? null;
                  $inScope  = $absensi['checkin_on_scope'] ?? true; 
                  $outScope = $absensi['checkout_on_scope'] ?? true;

                  $pelanggaran = [];

                  if (!$checkin) {
                      $pelanggaran[] = "TIDAK ABSEN MASUK";
                  }

                  if (!$checkout && $checkin) {
                      $pelanggaran[] = "TIDAK ABSEN PULANG";
                  }

                  if (($checkin && !$inScope) || ($checkout && !$outScope)) {
                      $pelanggaran[] = "ABSEN DI LUAR LOKASI";
                  }

                  if (count($pelanggaran) > 0) {
                      $lat  = $absensi['checkin_lat'] ?? null;
                      $long = $absensi['checkin_long'] ?? null;

                      $rows[] = [
                          'ID KARYAWAN'  => $kary->kode,
                          'NAMA'         => $kary->nama_lengkap,
                          'UNIT'         => $kary->m_dir->nama ?? '-',
                          'TANGGAL'      => Carbon::parse($dt->all_days_of_range)->format('d-m-Y'),
                          'HARI'         => $dt->day_name_idn,
                          'JENIS PELANGGARAN' => implode(", ", $pelanggaran),
                          'JAM MASUK'    => $checkin ?: '-',
                          'JAM PULANG'   => $checkout ?: '-',
                          'KOORDINAT IN' => ($lat && $long) ? $lat . ',' . $long : '-',
                          'KETERANGAN'   => ($absensi['catatan_in'] ?? '') . " " . ($absensi['catatan_out'] ?? '')
                      ];
                  }
              }
          }

          $export = new class(collect($rows)) implements FromCollection, WithHeadings, WithStyles {
              protected $data;
              public function __construct($data) { $this->data = $data; }
              public function collection() { return $this->data; }
              public function headings(): array {
                  return ['ID KARYAWAN', 'NAMA', 'UNIT', 'TANGGAL', 'HARI', 'JENIS PELANGGARAN', 'JAM MASUK', 'JAM PULANG', 'KOORDINAT', 'CATATAN'];
              }
              public function styles(Worksheet $sheet) {
                  $sheet->getStyle('A1:J1')->getFont()->setBold(true);
                  foreach ($this->data as $index => $row) {
                      $sheet->getStyle('F' . ($index + 2))->getFont()->getColor()->setARGB('FFFF0000');
                  }
                  return [];
              }
          };

          return Excel::download($export, "laporan_pelanggaran_{$date_start}.xlsx");

      } catch (\Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }

  public function public_exportPelanggaranPresensi()
  {
     try {

            $req = request();
            $date_start = Carbon::parse($req->date_start)->format('Y-m-d');
            $date_end   = Carbon::parse($req->date_end)->format('Y-m-d');
            
            $fileName = 'laporan_pelanggaran_absensi_' . Carbon::now()->format('Ymd_His') . '.xlsx';
           
            $data = t_cuti::with([
                'm_kary.m_divisi',
                'm_dir',
                'alasan' => function ($q) { $q->select('id', 'value'); },
            ])
            ->where(function ($query) use ($date_start, $date_end) {
                $query->whereBetween('date_from', [$date_start, $date_end])
                    ->orWhereBetween('date_to', [$date_start, $date_end])
                    ->orWhere(function ($q) use ($date_start, $date_end) {
                        $q->where('date_from', '<=', $date_start)
                            ->where('date_to', '>=', $date_end);
                    });
            })
            ->whereHas('alasan', function ($q) {
                $q->whereRaw('LOWER(value) = ?', ['dispensasi']); 
            })
            ->whereHas('m_kary', function ($karyQuery) use ($req) {
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
            })
            ->get()
            ->map(function ($cuti) {
                return [
                    'NOMOR' => $cuti->nomor ?? '',
                    'NAMA_KARYAWAN' => $cuti->m_kary?->nama_lengkap ?? '',
                    'JABATAN' => $cuti->m_kary?->m_divisi?->nama ?? '',
                    'UNIT' => $cuti->m_kary?->m_dir?->nama ?? '',
                    'ALASAN' => $cuti->alasan?->value ?? '',
                    'TANGGAL_DARI' => $cuti->date_from ? Carbon::parse($cuti->date_from)->format('Y-m-d') : '',
                    'TANGGAL_SAMPAI' => $cuti->date_to ? Carbon::parse($cuti->date_to)->format('Y-m-d') : '',
                    'KETERANGAN' => $cuti->keterangan ?? '',
                    'STATUS' => $cuti->status ?? '',
                    'DIBUAT_OLEH' => $cuti->creator?->name ?? '-',
                    'DIBUAT_PADA' => $cuti->created_at ? $cuti->created_at->format('Y-m-d H:i:s') : '',
                ];
            });

            $export = new class($data) implements FromCollection, WithHeadings {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array
                {
                    return [
                        'NOMOR',
                        'NAMA KARYAWAN',
                        'JABATAN',
                        'UNIT',
                        'TIPE CUTI',
                        'TANGGAL DARI',
                        'TANGGAL SAMPAI',
                        'KETERANGAN',
                        'STATUS',
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