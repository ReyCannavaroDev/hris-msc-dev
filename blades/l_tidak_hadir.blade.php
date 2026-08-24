@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Laporan Karyawan Tidak Hadir
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Tipe Export</label>
          <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.tipe"
            :errorText="formErrors.tipe ? 'failed' : ''" @input="v => values.tipe = v" :hints="formErrors.tipe"
            :check="false" label="" :options="['Excel', 'HTML']" placeholder="Pilih Tipe Export" valueField="key"
            displayField="key" />
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

        <!-- <div class="grid grid-cols-2 gap-2" v-if="values.tipe_report === 'Laporan Absensi Karyawan Group'">
          <div class="col-span-2">
            <label class="font-semibold">Periode
                  </label>
            <FieldX :bind="{ readonly: openDateSelected ? true : false , required: true} "
              v-if="values.tipe_report != 'Laporan Absensi Karyawan Rekap Tidak Absen' " class="w-full py-2 !mt-0"
              :value="values.periode" :check="false" type="month" label="" @input="(v)=>{
                      values.periode = v}"/>
            <FieldX :bind="{ readonly: openDateSelected ? true : false , required: true}"
              v-if="values.tipe_report === 'Laporan Absensi Karyawan Rekap Tidak Absen'" class="w-full py-2 !mt-0"
              :value="values.date" :check="false" type="date" label="" @input="(v)=>{
                      values.date = v
                  }" />
          </div>
        </div> -->

        <!-- Periode input aktif hanya untuk tipe report Rekap -->
        <!-- <div class="grid grid-cols-2 gap-2" v-if="values.tipe_report === 'Laporan Absensi Karyawan Rekap'">
          <div class="col-span-2">
            <label class="font-semibold">Periode <span class="text-red-500">*</span></label>
            <FieldX :bind="{ readonly: false, required: true }"
              class="w-full py-2 !mt-0"
              :value="values.periode"
              :check="false"
              type="month"
              label=""
              placeholder="MM/YYYY"
              :errorText="formErrors.periode ? 'failed' : ''"
              @input="v => values.periode = v"
              :hints="formErrors.periode" />
          </div>
        </div> -->

        <div>
          <label class="font-semibold">Unit</label>
          <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.m_dir_id"
            :errorText="formErrors.m_dir_id ? 'failed' : ''" @input="v => values.m_dir_id = v"
            :hints="formErrors.m_dir_id" :check="false" label="" placeholder="Pilih Unit" valueField="id"
            displayField="nama" :api="{
                      url: `${store.server.url_backend}/operation/m_dir`,
                      headers: { 
                          'Content-Type': 'Application/json', 
                          Authorization: `${store.user.token_type} ${store.user.token}`
                      },
                      params: {
                          single: true,
                          join: false,                    
                          //where: `m_divisi_id=${values.m_divisi_id ?? 0} AND this.is_active='true'`
                      }
                  }" />
        </div>

        <div>
          <label class="font-semibold">Jabatan</label>
          <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.m_divisi_id"
            :errorText="formErrors.m_divisi_id ? 'failed' : ''" @input="v => values.m_divisi_id = v"
            :hints="formErrors.m_divisi_id" :check="false" label="" placeholder="Pilih Jabatan" valueField="id"
            displayField="nama" :api="{
                      url: `${store.server.url_backend}/operation/m_divisi`,
                      headers: { 
                          'Content-Type': 'Application/json', 
                          Authorization: `${store.user.token_type} ${store.user.token}`
                      },
                      params: {
                          single: true,
                          join: false,
                          where: `this.is_active='true'`
                      }
                  }" />
        </div>

        <div
          v-if="values.tipe_report !== 'Laporan Absensi Karyawan Group' && values.tipe_report !== 'Laporan Absensi Karyawan Rekap Tidak Absen'">
          <label class="font-semibold">Karyawan</label>
          <FieldSelect class="col-span-12 !mt-2 w-full" :bind="{clearable:false , multiple: true}"
            :value="values.m_kary_id" @input="v=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''"
            :hints="formErrors.m_kary_id" valueField="id" displayField="nama_lengkap" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                //where: `this.m_divisi_id = ${values.m_divisi_id} AND this.m_dept_id = ${values.m_dept_id}`,
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="font-semibold">Status Karyawan</label>
          <FieldSelect 
            class="w-full py-2 !mt-0" 
            :value="values.is_active"
            :errorText="formErrors.is_active ? 'failed' : ''" 
            @input="v => values.is_active = v"
            :hints="formErrors.is_active" 
            :check="false" 
            label="" 
            placeholder="Pilih Status" 
            valueField="id"
            displayField="nama" 
            :options="[
              { id: 'true', nama: 'Aktif' },
              { id: 'false', nama: 'Tidak Aktif' }
            ]" 
          />
        </div>


      </div>
      <div class="flex flex-row justify-end space-x-[20px] mt-[1em]">
        <button 
          :disabled="isRequesting"
          @click="onGenerate" 
          class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] ">
          <template v-if="isRequesting">
            Memproses...
          </template>
          <template v-else>
            {{ values.tipe?.toLowerCase() === 'html' ? 'View' : 'Export' }}
          </template>
        </button>
      </div>
      <!-- END COLUMN -->
      <!-- ACTION BUTTON START -->
      <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
        <hr>
        <hr class="mb-6">
        <div class="flex justify-between items-center mb-3">
          <h2 class="font-bold text-gray-700 text-base">Hasil Laporan Karawan Tidak Hadir</h2>
          <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded">
            Total Data: {{ dataThr.length }} entri
          </span>
        </div>

        <div id="exportTable" class="w-full overflow-auto border border-[#CACACA] rounded-md">
          <table class="w-full overflow-x-auto table-auto border border-[#CACACA] text-sm">
            <thead>
              <tr class="border">
                <!-- Kolom Umum (Selalu Tampil) -->
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[4%]">No</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[12%]">ID Karyawan</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[16%]">Nama Karyawan</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[12%]">Unit</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[12%]">Jabatan</th>

                <!-- Kolom Khusus: Rentang Tanggal -->
                <th v-if="values.tipe_periode === 'Rentang Tanggal'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Tanggal</th>
                <th v-if="values.tipe_periode === 'Rentang Tanggal'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Hari</th>
                <th v-if="values.tipe_periode === 'Rentang Tanggal'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[24%]">Keterangan</th>

                <!-- Kolom Khusus: Bulan -->
                <th v-if="values.tipe_periode === 'Bulan'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Periode</th>
                <th v-if="values.tipe_periode === 'Bulan'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[7%]">Hari Kerja</th>
                <th v-if="values.tipe_periode === 'Bulan'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[7%]">Tdk Hadir</th>
                <th v-if="values.tipe_periode === 'Bulan'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[7%]">Izin</th>
                <th v-if="values.tipe_periode === 'Bulan'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[7%]">Alpha</th>
                <th v-if="values.tipe_periode === 'Bulan'" class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[16%]">Ringkasan</th>
              </tr>
            </thead>
            <tbody>
              <!-- Looping Data -->
              <tr v-for="(item, idx) in dataThr" :key="idx" 
                  :class="['hover:bg-amber-50 border-t border-[#CACACA]']">
                
                <!-- Data Umum -->
                <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-700">{{ idx + 1 }}</td>
                <td class="border border-[#CACACA] px-3 py-3 text-gray-700 font-mono text-xs">{{ item['ID KARYAWAN'] }}</td>
                <td class="border border-[#CACACA] px-3 py-3 font-medium text-gray-900">{{ item['NAMA KARYAWAN'] }}</td>
                <td class="border border-[#CACACA] px-3 py-3 text-gray-600">{{ item['UNIT'] }}</td>
                <td class="border border-[#CACACA] px-3 py-3 text-gray-600">{{ item['JABATAN'] }}</td>

                <!-- Data Khusus: Rentang Tanggal -->
                <td v-if="values.tipe_periode === 'Rentang Tanggal'" class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{ item['TANGGAL'] }}</td>
                <td v-if="values.tipe_periode === 'Rentang Tanggal'" class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{ item['HARI'] }}</td>
                <td v-if="values.tipe_periode === 'Rentang Tanggal'" class="border border-[#CACACA] px-3 py-3">
                  <span v-if="item['KETERANGAN'] !== '-'" class="bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded text-xs font-medium">
                    {{ item['KETERANGAN'] }}
                  </span>
                  <span v-else class="bg-red-100 text-red-800 px-2 py-0.5 rounded text-xs font-bold">
                    Alpha
                  </span>
                </td>

                <!-- Data Khusus: Bulan -->
                <td v-if="values.tipe_periode === 'Bulan'" class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{ item['PERIODE'] }}</td>
                <td v-if="values.tipe_periode === 'Bulan'" class="text-center border border-[#CACACA] px-3 py-3 text-gray-600 font-semibold">{{ item['HARI KERJA'] }}</td>
                <td v-if="values.tipe_periode === 'Bulan'" class="text-center border border-[#CACACA] px-3 py-3 text-gray-800 font-bold">{{ item['TOTAL TIDAK HADIR'] }}</td>
                <td v-if="values.tipe_periode === 'Bulan'" class="text-center border border-[#CACACA] px-3 py-3 text-orange-600 font-bold">{{ item['TOTAL IZIN'] }}</td>
                <td v-if="values.tipe_periode === 'Bulan'" class="text-center border border-[#CACACA] px-3 py-3 text-red-600 font-bold">{{ item['TOTAL ALPHA'] }}</td>
                <td v-if="values.tipe_periode === 'Bulan'" class="border border-[#CACACA] px-3 py-3 text-gray-600 text-xs">{{ item['RINGKASAN'] }}</td>
              </tr>
              
              <!-- Tampilan Jika Data Kosong -->
              <tr v-if="!dataThr.length">
                <!-- Gunakan colspan dinamis berdasarkan mode yang dipilih -->
                <td :colspan="values.tipe_periode === 'Rentang Tanggal' ? 8 : 11" class="text-center text-gray-500 py-12 italic border border-[#CACACA] bg-gray-50">
                  Tidak ada data karyawan tidak hadir pada periode dan filter terpilih.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </div>

</div>
</div>
@endverbatim