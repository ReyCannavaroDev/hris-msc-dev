  @verbatim
  <div class="flex flex-col gap-y-3">
    <div class="flex gap-x-4 px-2">
      <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white text-sm">
        <div class="mb-4">
          <h1 class="text-[20px] mb-4 font-bold text-gray-800">
            Laporan Lembur Otomatis Absensi
          </h1>
          <hr>
        </div>

        <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
          <!-- Tipe Export -->
          <div class="col-span-2">
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

          <!-- Periode -->
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Periode</label>
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

          <!-- Unit -->
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Unit / Direktorat</label>
            <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable:true }" :value="values.dir_id"
              :check="false" @input="(v)=>{
                values.dir_id = v
              }" displayField="nama" valueField="id" :api="{
                  url: `${store.server.url_backend}/operation/m_dir`,
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest:true,
                    single:true,
                    transform:false,
                  }
              }" placeholder="Pilih Unit" fa-icon="search" :check="true" />
          </div>

          <!-- Divisi -->
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Jabatan / Divisi</label>
            <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable:true }" :value="values.divisi_id"
              :check="false" @input="(v)=>{
                values.divisi_id = v
              }" displayField="nama" valueField="id" :api="{
                  url: `${store.server.url_backend}/operation/m_divisi`,
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest:true,
                    single:true,
                    where:`this.is_active='true'`,
                    transform:false,
                  }
              }" fa-icon="search" :check="true" placeholder="Pilih Jabatan" />
          </div>

          <!-- Karyawan -->
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Karyawan</label>
            <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable:true }" :value="values.m_kary_id"
              :check="false" @input="(v)=>{
                values.m_kary_id = v
              }" displayField="nama_lengkap" valueField="id" :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest:true,
                    searchfield: 'id, nama_lengkap, nik',
                    transform:false,
                  }
              }" fa-icon="search" :check="true" placeholder="Pilih Karyawan" />
          </div>
        </div>

        <div class="flex flex-row justify-end space-x-[20px] mt-[1.5em] px-4">
          <button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white font-semibold px-[36.5px] py-[10px] rounded-[4px] ">
            {{ values.tipe?.toLowerCase() === 'html' ? 'View' : 'Export' }}
          </button>
        </div>

        <!-- Preview Section -->
        <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
          <hr class="mb-6">
          <div id="exportTable" class="w-full overflow-auto border border-[#CACACA] rounded-md">
            <table class="w-full overflow-x-auto table-auto border border-[#CACACA] text-sm">
              <thead>
                <tr class="border">
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">No</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[10%]">NIK</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[20%]">Nama</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[15%]">Unit</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[15%]">Jabatan</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Tanggal</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Hari</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Tipe Hari</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Jadwal Pulang</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Checkout</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-right border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Durasi (Jam)</th>
                  <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-right border bg-[#f8f8f8] border-[#CACACA] w-[10%] pr-4">Nominal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item,idx in dataLembur" :key="idx" class="hover:bg-amber-50 border-t border-[#CACACA]">
                  <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-700">{{idx+1}}</td>
                  <td class="border border-[#CACACA] px-3 py-3 text-gray-700">{{item.nik}}</td>
                  <td class="border border-[#CACACA] px-3 py-3 font-medium text-gray-900">{{item.nama}}</td>
                  <td class="border border-[#CACACA] px-3 py-3 text-gray-600">{{item.unit}}</td>
                  <td class="border border-[#CACACA] px-3 py-3 text-gray-600">{{item.jabatan}}</td>
                  <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{item.tanggal}}</td>
                  <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{item.hari}}</td>
                  <td class="text-center border border-[#CACACA] px-3 py-3">
                    <span :class="item.tipe_hari === 'KERJA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-0.5 rounded text-xs font-semibold">
                      {{item.tipe_hari}}
                    </span>
                  </td>
                  <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-600">{{item.jam_selesai_jadwal}}</td>
                  <td class="text-center border border-[#CACACA] px-3 py-3 text-gray-700 font-medium">{{item.checkout_aktual}}</td>
                  <td class="text-right border border-[#CACACA] px-3 py-3 font-medium text-gray-800">{{item.jam_lembur}} Jam</td>
                  <td class="text-right border border-[#CACACA] px-3 py-3 pr-4 font-bold text-green-600">Rp {{item.nominal_lembur.toLocaleString('id-ID')}}</td>
                </tr>
                <tr v-if="!dataLembur.length">
                  <td colspan="12" class="text-center text-gray-500 py-12 italic border border-[#CACACA] bg-gray-50">Tidak ada data lembur otomatis pada periode dan filter terpilih.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
  @endverbatim
