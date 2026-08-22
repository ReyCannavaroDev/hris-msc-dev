@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white text-sm">
      <div class="mb-4">
        <h1 class="text-[20px] mb-4 font-bold text-gray-800">
          Laporan Keterlambatan Absensi
        </h1>
        <hr>
      </div>

      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
        <!-- Tipe Export -->
        <div>
          <label class="font-semibold block mb-1 text-gray-700">Tipe Export</label>
          <FieldSelect 
            :bind="{ readonly: false }" 
            class="w-full py-2 !mt-0"
            :value="values.tipe" 
            :errorText="formErrors.tipe ? 'failed' : ''"
            @input="v => values.tipe = v" 
            :hints="formErrors.tipe" 
            :check="false" 
            label=""
            :options="['HTML', 'Excel']"
            placeholder="Pilih Tipe Export"
            valueField="key" 
            displayField="key"
          />
        </div>

        <!-- Tipe Periode (Flexible) -->
        <div>
          <label class="font-semibold block mb-1 text-gray-700">Pilihan Periode (Flexible)</label>
          <FieldSelect 
            :bind="{ readonly: false }" 
            class="w-full py-2 !mt-0"
            :value="values.tipe_periode" 
            :errorText="formErrors.tipe_periode ? 'failed' : ''"
            @input="v => { values.tipe_periode = v; resetPeriode(); }" 
            :hints="formErrors.tipe_periode" 
            :check="false" 
            label=""
            :options="['Bulan', 'Rentang Tanggal']"
            placeholder="Pilih Mode Periode"
            valueField="key" 
            displayField="key"
          />
        </div>

        <!-- Filter Periode: Mode Bulan -->
        <div v-if="values.tipe_periode === 'Bulan'" class="col-span-2">
          <label class="font-semibold block mb-1 text-gray-700">Periode Bulan <span class="text-red-500">*</span></label>
          <FieldX :bind="{ readonly: false, required: true }" 
            class="w-full py-2 !mt-0"
            :value="values.periode" 
            :check="false" 
            type="month" 
            label=""
            @input="(v)=>{
              values.periode = v
            }" />
        </div>

        <!-- Filter Periode: Mode Rentang Tanggal -->
        <div v-if="values.tipe_periode === 'Rentang Tanggal'" class="col-span-2 grid grid-cols-2 gap-4">
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Tanggal Awal <span class="text-red-500">*</span></label>
            <FieldX type="date" :bind="{ readonly: false, required: true }" class="w-full py-2 !mt-0" :value="values.date_start"
              label="" placeholder="DD/MM/YYYY" :errorText="formErrors.date_start ? 'failed' : ''"
              @input="v => { values.date_start = v; onDateStartChange(v); }" :hints="formErrors.date_start"
              :check="false" />
          </div>
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Tanggal Akhir <span class="text-red-500">*</span></label>
            <FieldX type="date" :bind="{ readonly: false, required: true }" class="w-full py-2 !mt-0" :value="values.date_end"
              label="" placeholder="DD/MM/YYYY" :errorText="formErrors.date_end ? 'failed' : ''"
              @input="v => { values.date_end = v; onDateEndChange(v); }" :hints="formErrors.date_end"
              :check="false" />
          </div>
        </div>

        <!-- Unit / Direktorat -->
        <div>
          <label class="font-semibold block mb-1 text-gray-700">Unit / Direktorat</label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable: true }" :value="values.dir_id"
            :check="false" @input="(v)=>{
              values.dir_id = v
            }" displayField="nama" valueField="id" :api="{
                url: `${store.server.url_backend}/operation/m_dir`,
                headers: {
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest: true,
                  single: true,
                  transform: false,
                }
            }" placeholder="Semua Unit" fa-icon="search" :check="true" />
        </div>

        <!-- Jabatan / Divisi -->
        <div>
          <label class="font-semibold block mb-1 text-gray-700">Jabatan / Divisi</label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable: true }" :value="values.divisi_id"
            :check="false" @input="(v)=>{
              values.divisi_id = v
            }" displayField="nama" valueField="id" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: {
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest: true,
                  single: true,
                  where: `this.is_active='true'`,
                  transform: false,
                }
            }" fa-icon="search" :check="true" placeholder="Semua Jabatan" />
        </div>

        <!-- Pilihan Per Pengguna / Karyawan (Flexible Single/All) -->
        <div>
          <label class="font-semibold block mb-1 text-gray-700">Karyawan (Pilihan Pengguna)</label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable: true }" :value="values.m_kary_id"
            :check="false" @input="(v)=>{
              values.m_kary_id = v
            }" displayField="nama_lengkap" valueField="id" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: {
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest: true,
                  searchfield: 'id, nama_lengkap, nik, kode',
                  transform: false,
                }
            }" fa-icon="search" :check="true" placeholder="Semua Karyawan (atau cari per nama/NIK)" />
        </div>

        <!-- Status Karyawan -->
        <div>
          <label class="font-semibold block mb-1 text-gray-700">Status Karyawan</label>
          <FieldSelect 
            class="w-full py-2 !mt-0" 
            :bind="{ clearable: true }"
            :value="values.is_active"
            :errorText="formErrors.is_active ? 'failed' : ''" 
            @input="v => values.is_active = v"
            :hints="formErrors.is_active" 
            :check="false" 
            label="" 
            placeholder="Semua Status" 
            valueField="id"
            displayField="nama" 
            :options="[
              { id: 'true', nama: 'Aktif' },
              { id: 'false', nama: 'Tidak Aktif' }
            ]" 
          />
        </div>
      </div>

      <!-- Action Button -->
      <div class="flex flex-row justify-end space-x-[20px] mt-[1.5em] px-4">
        <button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white font-semibold px-[36.5px] py-[10px] rounded-[4px]">
          {{ values.tipe?.toLowerCase() === 'html' ? 'View Pratinjau' : 'Export Excel' }}
        </button>
      </div>

      <!-- Preview Section -->
      <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
        <hr class="mb-6">
        <div class="flex justify-between items-center mb-3">
          <h2 class="font-bold text-gray-700 text-base">Hasil Pratinjau Keterlambatan</h2>
          <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded">
            Total Data: {{ dataTerlambat.length }} entri
          </span>
        </div>

        <div id="exportTable" class="w-full overflow-auto border border-[#CACACA] rounded-md">
          <table class="w-full overflow-x-auto table-auto border border-[#CACACA] text-sm">
            <thead>
              <tr class="border">
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">No</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[10%]">NIK</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[18%]">Nama Karyawan</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[14%]">Unit</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[14%]">Jabatan</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Tanggal</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[8%]">Hari</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Jadwal Masuk</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Check-in Aktual</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-right border bg-[#f8f8f8] border-[#CACACA] w-[11%]">Terlambat</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item,idx in dataTerlambat" :key="idx" class="hover:bg-amber-50 border-t border-[#CACACA]">
                <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-700">{{idx+1}}</td>
                <td class="border border-[#CACACA] px-3 py-3 text-gray-700 font-mono text-xs">{{item.nik}}</td>
                <td class="border border-[#CACACA] px-3 py-3 font-medium text-gray-900">{{item.nama}}</td>
                <td class="border border-[#CACACA] px-3 py-3 text-gray-600">{{item.unit}}</td>
                <td class="border border-[#CACACA] px-3 py-3 text-gray-600">{{item.jabatan}}</td>
                <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{item.tanggal}}</td>
                <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{item.hari}}</td>
                <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-600 font-mono">{{item.jam_jadwal_masuk}}</td>
                <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-700 font-medium font-mono">{{item.checkin_aktual}}</td>
                <td class="text-right border border-[#CACACA] px-3 py-3 font-bold text-red-600">
                  {{item.menit_terlambat}} Menit
                </td>
              </tr>
              <tr v-if="!dataTerlambat.length">
                <td colspan="10" class="text-center text-gray-500 py-12 italic border border-[#CACACA] bg-gray-50">
                  Tidak ada data keterlambatan pada periode dan filter terpilih.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@endverbatim