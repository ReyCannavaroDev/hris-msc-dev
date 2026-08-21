@if(!$req->has('id'))

@verbatim
<div class="bg-white p-6 rounded-xl flex justify-center flex-col">
  <div class="grid grid-cols-2 w-full text-sm overflow-x-auto">
          <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 0}"
            @click="activeTabIndex = 0"
          >
            Absen
          </button>
          <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 1}"
            @click="activeTabIndex = 1"
          >
            Daftar Absensi
          </button>
        </div>
  <div v-show="activeTabIndex === 0">
    <!-- Card Pengingat Jadwal Kerja Harian -->
    <div v-if="form.jadwal" class="mt-4 w-full max-w-xl mx-auto">
      <div 
        class="rounded-xl p-4 shadow-sm border transition-all duration-300"
        :class="{
          'bg-blue-50 border-blue-200 text-blue-900': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'not attend',
          'bg-amber-50 border-amber-200 text-amber-900': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'working',
          'bg-emerald-50 border-emerald-200 text-emerald-900': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'attend',
          'bg-gray-50 border-gray-200 text-gray-800': form.jadwal.is_libur
        }"
      >
        <div class="flex items-center justify-between border-b pb-2 mb-3" :class="{
          'border-blue-200': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'not attend',
          'border-amber-200': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'working',
          'border-emerald-200': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'attend',
          'border-gray-200': form.jadwal.is_libur
        }">
          <div class="flex items-center gap-2">
            <span class="p-1.5 rounded-lg text-white text-xs" :class="{
              'bg-blue-600': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'not attend',
              'bg-amber-600': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'working',
              'bg-emerald-600': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'attend',
              'bg-gray-500': form.jadwal.is_libur
            }">
              <icon fa="calendar-check" />
            </span>
            <span class="font-bold text-sm">Pengingat Jadwal Kerja Hari Ini</span>
          </div>
          <span 
            class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
            :class="{
              'bg-blue-200 text-blue-800': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'not attend',
              'bg-amber-200 text-amber-800': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'working',
              'bg-emerald-200 text-emerald-800': !form.jadwal.is_libur && form.attending?.toLowerCase() === 'attend',
              'bg-gray-200 text-gray-700': form.jadwal.is_libur
            }"
          >
            {{ form.jadwal.nama_jadwal }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-2 text-center" v-if="!form.jadwal.is_libur && form.jadwal.waktu_mulai">
          <div class="bg-white bg-opacity-70 rounded-lg p-2 border border-gray-100 shadow-2xs">
            <p class="text-xs text-gray-500 font-medium">Jam Masuk (Checkin)</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">
              <icon fa="right-to-bracket" class="text-blue-500 mr-1 text-xs" />
              {{ form.jadwal.waktu_mulai }} WIB
            </p>
            <span v-if="form.jadwal.checkin_time" class="text-[11px] text-green-600 font-semibold block mt-0.5">
              ✓ Absen: {{ form.jadwal.checkin_time }}
            </span>
          </div>
          <div class="bg-white bg-opacity-70 rounded-lg p-2 border border-gray-100 shadow-2xs">
            <p class="text-xs text-gray-500 font-medium">Jam Pulang (Checkout)</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">
              <icon fa="right-from-bracket" class="text-red-500 mr-1 text-xs" />
              {{ form.jadwal.waktu_akhir }} WIB
              <span v-if="form.jadwal.is_hari_berikutnya" class="text-[10px] text-orange-600 block">(Hari Berikutnya)</span>
            </p>
            <span v-if="form.jadwal.checkout_time" class="text-[11px] text-green-600 font-semibold block mt-0.5">
              ✓ Absen: {{ form.jadwal.checkout_time }}
            </span>
          </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-medium mt-2 pt-1">
          <icon fa="circle-info" class="text-sm shrink-0" />
          <p>{{ form.jadwal.pesan_pengingat }}</p>
        </div>
      </div>
    </div>

    <h1 class="font-semibold text-xl mt-6 text-center">{{form.attending?.toLowerCase() === 'not attend' ? 'Absen Checkin' : (form.attending?.toLowerCase() === 'working' ? 'Absen Checkout' : 'Sudah Absen')}} </h1>
      <div class="mt-4 lg:mt-6">
        <video style="transform: scaleX(-1)" v-show="!isImage" v-if="form.attending?.toLowerCase() !== 'attend'" ref="videoElement" autoplay playsinline muted class="rounded-xl h-full lg:h-[20rem] m-auto"></video>
        <!-- <div v-if="isImage" class="bg-gray-600 rounded-xl"></div> -->
        <!-- <div v-else class="bg-gray-700 m-auto rounded-xl w-full h-full lg:w-[426px] lg:h-[320px]"></div> -->
        <img v-show="isImage" id="imgElem" class="w-full lg:max-w-[426px] h-full lg:max-h-[320px] m-auto rounded-xl"></img>
      </div>
      <div class="flex mt-4 justify-center space-x-4 lg:mt-6">
        <div v-show="form.attending?.toLowerCase() !== 'attend'">
          <button v-show="!isImage" @click="capture" class="bg-blue-600 hover:bg-blue-700 text-white w-fit px-6 py-2 rounded-lg m-auto">Capture</button>
          <button v-show="isImage" @click="recapture" class="bg-yellow-600 hover:bg-yellow-700 text-white w-fit px-6 py-2 rounded-lg m-auto">Recapture</button>
        </div>
        <div v-show="form.attending?.toLowerCase() !== 'attend'">
          <button @click="postAttend" v-show="isImage" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">Absen {{form.attending?.toLowerCase() === 'not attend' ? 'Checkin' :'Checkout'}}</button>
        </div>
        <div v-show="form.attending?.toLowerCase() === 'attend'">
          <button @click="activeTabIndex = 1" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Lihat Detail Absen</button>
        </div>
      </div>
      
      <!-- Fitur Istirahat -->
      <div v-show="form.attending?.toLowerCase() === 'working'" class="w-full max-w-md mx-auto mt-6 p-4 border border-yellow-400 bg-yellow-50 rounded-lg">
        <h2 class="font-bold text-gray-700 mb-2 text-center"><i class="fa fa-coffee"></i> Lapor Jam Istirahat</h2>
        
        <div v-if="!form.istirahat_tipe" class="flex justify-center space-x-3">
          <button @click="postIstirahat('KELUAR')" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm"><i class="fa fa-sign-out-alt"></i> Keluar Istirahat</button>
          <button @click="postIstirahat('DI_KANTOR')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm"><i class="fa fa-building"></i> Tetap di Kantor</button>
        </div>
        
        <div v-else-if="form.istirahat_tipe === 'KELUAR' && !form.istirahat_end" class="text-center">
          <p class="text-sm text-gray-600 mb-3">Anda sedang istirahat keluar sejak <b class="text-yellow-700">{{ form.istirahat_start }}</b></p>
          <button @click="postIstirahatEnd()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm"><i class="fa fa-check"></i> Selesai Istirahat</button>
        </div>
        
        <div v-else class="text-center text-green-700 font-semibold text-sm">
          <i class="fa fa-check-circle"></i> Istirahat telah dilaporkan 
          <span v-if="form.istirahat_durasi">(Durasi: {{ form.istirahat_durasi }} menit)</span>
        </div>
        <p class="text-xs text-gray-500 mt-2 text-center">* Wajib diisi sebelum melakukan absen pulang (Checkout).</p>
      </div>
      <div class="flex justify-between mt-6">
        <div class="flex space-x-2 items-center">
          <icon fa="calendar" class="text-blue-600"/>
          <h2 class="text-md lg:pr-10 text-gray-700">{{form.day}}, {{form.tanggal}}</h2>
        </div>
        <div class="flex space-x-2 items-center">
          <icon fa="clock" class="text-blue-600"/>
          <h2 class="text-md lg:pr-10 text-gray-700">{{form.currentTime}}</h2>
        </div>
    </div>
    <hr>
    <div class="px-4">
      <table class="mt-2 lg:block hidden">
        <tr>
          <td class="align-top font-semibold">Lokasi</td>
          <td class="px-2 align-top font-semibold">:</td>
          <td class="align-top pb-2">{{form.address}}</td>
        </tr>
        <tr>
          <td class="align-top font-semibold">Keterangan</td>
          <td class="px-2 align-top font-semibold">:</td>
          <td :class="form.distance_check ? 'text-green-600 align-top':'text-red-600 align-top'">{{form.distance_check ? 'On Scope' : 'Out Scope'}}</td>
        </tr>
      </table>
      <table class="mt-4 block lg:hidden">
        <tr>
          <td colspan="2" class="align-top font-semibold">Lokasi :</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td class="align-top pb-6">{{form.address}}</td>
        </tr>
        <tr>
          <td colspan="2" class="align-top font-semibold">Keterangan :</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td :class="form.distance_check ? 'text-green-600 align-top':'text-red-600 align-top'">{{form.distance_check ? 'On Scope' : 'Out Scope'}}</td>
        </tr>
      </table>
    </div>
  </div>
  <div v-show="activeTabIndex === 1">
    <div class="hidden lg:block my-8 lg:flex space-x-6 items-center">
      <label>Bulan :</label>
      <FieldSelect
      class="w-[20%]"
        :bind="{ disabled: false, clearable:false }"
        :value="form.month" @input="v=>form.month=v"
        valueField="id" displayField="name"
        :options="listMonths"
        @update:valueFull="(e)=>{
          getDetailAbsen(form.year,e.id)
        }"
        placeholder="Pilih Bulan" label="":check="false"
      />
      <label>Tahun :</label>
      <FieldSelect
        :bind="{ disabled: false, clearable:false }"
        :value="form.year" @input="v=>{form.year=v}"
        valueField="key" displayField="key"
        :options="listTahun"
        @update:valueFull="(e)=>{
          getDetailAbsen(e.id,form.month)
        }"
        placeholder="Tahun" label="":check="false"
        class="w-[20%]"
      />
    </div>
    <div class="block lg:hidden my-8 grid grid-rows-2 gap-4">
      <div class="flex space-x-3 items-center">
        <label>Bulan :</label>
        <FieldSelect
        class="w-[50%]"
          :bind="{ disabled: false, clearable:false }"
          :value="form.month" @input="v=>form.month=v"
          valueField="id" displayField="name"
          :options="listMonths"
          @update:valueFull="(e)=>{
            getDetailAbsen(form.year,e.id)
          }"
          placeholder="Pilih Bulan" label="":check="false"
        />
      </div>
      <div class="flex space-x-3 items-center">
        <label>Tahun :</label>
        <FieldSelect
          :bind="{ disabled: false, clearable:false }"
          :value="form.year" @input="v=>{form.year=v}"
          valueField="key" displayField="key"
          :options="listTahun"
          @update:valueFull="(e)=>{
            getDetailAbsen(e.id,form.month)
          }"
          placeholder="Tahun" label="":check="false"
          class="w-[50%]"
        />
      </div>
    </div>
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-4">
      <div @click=(tampilkanModal(item)) class="border-2 p-4 space-y-3 rounded hover:bg-gray-200 delay-100 cursor-pointer hover:border-gray-400" v-for="(item, index) in listDetail" :key="index">
        <span v-if="item.type?.toLowerCase() === 'hari kerja'" :class="item.status?.toLowerCase() === 'attend' ? 'bg-green-200 text-green-800' : (item.status?.toLowerCase() === 'working' ? 'bg-blue-200 text-blue-800' : 'bg-red-200 text-red-800')" class="font-semibold px-4 py-1 rounded">{{item.status?.toLowerCase() === 'attend' ? 'Hadir' : (item.status?.toLowerCase() === 'working' ? 'Belum Check Out' : 'Tidak Hadir')}}</span>
        <span v-else class="font-semibold px-4 py-1 rounded bg-gray-200 text-gray-800">Hari Libur</span>
        <h1>{{item.day_name_idn}}, {{removeStrip(item.date_to_idn)}}</h1>
        <div class="flex space-x-4">
          <span>In : {{item.checkin_time ? item.checkin_time : '-'}}</span>
          <span>Out : {{item.checkout_time ? item.checkout_time : '-'}}</span>
        </div>
      </div>
    </div>
  </div>
  <!-- <img v-if="capturedImage" :src="capturedImage" alt="Captured Image"> -->
</div>
<div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50" id="modal">
    <!-- Modal Overlay (background) -->
    <div class="fixed inset-0 bg-black opacity-50" id="modal"></div>

    <!-- Modal Content -->
    <div class="bg-white w-[90%] lg:w-[70%] rounded shadow-lg z-10 overflow-auto max-h-[70%]">
        <div class="flex justify-between items-center px-[30px] py-[27px] border-b">
          <h2 class="text-2xl font-semibold">Detail</h2>
            <icon fa="remove" class="cursor-pointer text-[30px] font-normal text-[#8F8F8F]"  @click="showModal=false"/>
        </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 text-[14px] gap-x-[29px] gap-y-[20px] px-[10px] lg:px-[30px] py-[27px]">
        <div class="flex space-x-4">
          <div class="w-[60%] lg:w-[40%]">
            <img v-if="dataDetail.checkin_foto" :src="`${dataDetail.checkin_foto}`"class="!mt-2 w-full lg:w-[166px]">
            <div v-else class="h-[166px] bg-gray-500 w-full lg:w-[166px] rounded-[10px]"></div>
          </div>
          <table class="w-full lg:block hidden table-auto">
            <tr class="h-fit">
              <td class="w-[30%] align-top h-fit font-semibold">Alamat Checkin</td>
              <td class="align-top h-fit px-2 w-[10%] font-semibold">:</td>
              <td class="align-top h-fit pb-2">{{dataDetail.checkin_address ? dataDetail.checkin_address : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Jam Checkin</td>
              <td class="align-top h-fit px-2 w-[10%] font-semibold">:</td>
              <td class="align-top h-fit pb-2">{{dataDetail.checkin_time ? dataDetail.checkin_time : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Office</td>
              <td class="align-top h-fit px-2 w-[10%] font-semibold">:</td>
              <td :class="dataDetail.checkin_region?.toLowerCase() === 'in scope' ? 'text-green-600' : (dataDetail.checkin_region?.toLowerCase() === 'out scope' ? 'text-red-600' : 'text-black')" class="align-top h-fit pb-2">{{dataDetail.checkin_region ? dataDetail.checkin_region : '-'}}</td>
            </tr>
          </table>
          <table class="w-full block lg:hidden table-auto">
            <tr>
              <td class="w-[30%] align-top h-fit font-semibold">Alamat Checkin :</td>
            </tr>
            <tr>
              <td class="align-top h-fit pb-2">{{dataDetail.checkin_address ? dataDetail.checkin_address : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Jam Checkin :</td>
            </tr>
            <tr>
              <td class="align-top h-fit pb-2">{{dataDetail.checkin_time ? dataDetail.checkin_time : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Office :</td>
            </tr>
            <tr>
              <td :class="dataDetail.checkin_region?.toLowerCase() === 'in scope' ? 'text-green-600' : (dataDetail.checkin_region?.toLowerCase() === 'out scope' ? 'text-red-600' : 'text-black')" class="align-top h-fit pb-2">{{dataDetail.checkin_region ? dataDetail.checkin_region : '-'}}</td>
            </tr>
          </table>
        </div>
        <div class="flex space-x-4">
          <div class="w-[60%] lg:w-[40%]">
            <img v-if="dataDetail.checkout_foto" :src="`${dataDetail.checkout_foto}`"class="!mt-2 w-full lg:w-[166px]">
            <div v-else class="h-[166px] bg-gray-500 w-full lg:w-[166px] rounded-[10px]"></div>
          </div>
          <table class="w-full lg:block hidden table-auto">
            <tr class="h-fit">
              <td class="w-[30%] align-top h-fit font-semibold">Alamat Checkout</td>
              <td class="align-top px-2 h-fit w-[10%] font-semibold">:</td>
              <td class="align-top h-fit pb-2">{{dataDetail.checkout_address ? dataDetail.checkout_address : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Jam Checkin</td>
              <td class="align-top px-2 h-fit w-[10%] font-semibold">:</td>
              <td class="align-top h-fit pb-2">{{dataDetail.checkout_time ? dataDetail.checkout_time : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Office</td>
              <td class="align-top px-2 h-fit w-[10%] font-semibold">:</td>
              <td :class="dataDetail.checkout?.toLowerCase() === 'in scope' ? 'text-green-600' : 'text-red-600' " class="align-top h-fit pb-2">{{dataDetail.checkout_region ? dataDetail.checkout_region : '-'}}</td>
            </tr>
          </table>
          <table class="w-full block lg:hidden table-auto">
            <tr>
              <td class="w-[30%] align-top h-fit font-semibold">Alamat Checkout :</td>
            </tr>
            <tr>
              <td class="align-top h-fit pb-2">{{dataDetail.checkout_address ? dataDetail.checkout_address : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Jam Checkout :</td>
            </tr>
            <tr>
              <td class="align-top h-fit pb-2">{{dataDetail.checkout_time ? dataDetail.checkout_time : '-'}}</td>
            </tr>
            <tr>
              <td class="align-top h-fit font-semibold">Office :</td>
            </tr>
            <tr>
              <td :class="dataDetail.checkout_region?.toLowerCase() === 'in scope' ? 'text-green-600' : (dataDetail.checkout_region?.toLowerCase() === 'out scope' ? 'text-red-600' : 'text-black')" class="align-top h-fit pb-2">{{dataDetail.checkout_region ? dataDetail.checkout_region : '-'}}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
@endverbatim
@else


@verbatim

<div>
  <div class="flex flex-col bg-white p-6 w-full h-full">
    <Writer :value="values.content" 
    @input="$log('halo')" />
  </div>
</div>
@endverbatim
@endif