@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white text-sm">
      <div class="mb-4">
        <h1 class="text-[20px] mb-4 font-bold text-gray-800">
          Laporan THR
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
          <label class="font-semibold block mb-1 text-gray-700">Pilihan Agama</label>
          <FieldSelect 
            :bind="{ readonly: false, clearable: true }" 
            class="w-full py-2 !mt-0"
            :value="values.agama" 
            :errorText="formErrors.agama ? 'failed' : ''"
            @input="v => values.agama = v" 
            :hints="formErrors.agama" 
            :check="false" 
            label=""
            placeholder="Semua Agama"
            valueField="id" 
            displayField="value"
            :api="{
                url: `${store.server.url_backend}/operation/m_general`,
                headers: { 
                    'Content-Type': 'Application/json', 
                    Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                    single: true,
                    join: false,
                    // PASTIKAN NAMA GROUP-NYA SESUAI DENGAN YANG ADA DI DATABASE ANDA
                    where: `this.group='AGAMA' AND this.is_active='true'` 
                }
            }"
          />
        </div>

        
        <!-- Filter Periode: Mode Bulan -->
        <div class="col-span-2">
          <label class="font-semibold block mb-1 text-gray-700">Tanggal Cut-OFF <span class="text-red-500">*</span></label>
          <FieldX type="date" :bind="{ readonly: false, required: true }" class="w-full py-2 !mt-0" :value="values.date_cut_off"
              label="" placeholder="DD/MM/YYYY" :errorText="formErrors.date_cut_off ? 'failed' : ''"
              @input="v => { values.date_cut_off = v; onDateCutOffChange(v); }" :hints="formErrors.date_cut_off"
              :check="false" />
        </div>

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

      <!-- Action Button -->
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

      <!-- Preview Section -->
      <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
        <hr class="mb-6">
        <div class="flex justify-between items-center mb-3">
          <h2 class="font-bold text-gray-700 text-base">Hasil Pratinjau Laporan THR</h2>
          <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded">
            Total Data: {{ dataThr.length }} entri
          </span>
        </div>

        <div id="exportTable" class="w-full overflow-auto border border-[#CACACA] rounded-md">
          <table class="w-full overflow-x-auto table-auto border border-[#CACACA] text-sm">
            <thead>
              <tr class="border">
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[4%]">No</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[10%]">ID Karyawan</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[18%]">Nama Karyawan</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[12%]">Unit</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[12%]">Jabatan</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[8%]">Agama</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">Tgl Masuk</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-left border bg-[#f8f8f8] border-[#CACACA] w-[14%]">Lama Bekerja</th>
                <th class="text-[#8F8F8F] font-semibold text-[14px] px-3 py-[14.5px] text-right border bg-[#f8f8f8] border-[#CACACA] w-[12%]">THR Diterima</th>
              </tr>
            </thead>
            <tbody>
              <!-- Looping Data, ubah warna teks jadi abu-abu pudar jika THR = 0 -->
              <tr v-for="(item, idx) in dataThr" :key="idx" 
                  :class="['hover:bg-amber-50 border-t border-[#CACACA]', item['THR DITERIMA'] === 0 ? 'text-gray-400' : 'text-gray-700']">
                
                <td class="text-center border border-[#CACACA] px-3 py-3">{{ idx + 1 }}</td>
                <td class="border border-[#CACACA] px-3 py-3 font-mono text-xs">{{ item['ID KARYAWAN'] }}</td>
                
                <!-- Nama karyawan dicetak lebih tebal, kecuali jika THR 0 maka ikutan pudar -->
                <td class="border border-[#CACACA] px-3 py-3 font-medium" :class="item['THR DITERIMA'] === 0 ? 'text-gray-400' : 'text-gray-900'">
                  {{ item['NAMA KARYAWAN'] }}
                </td>
                
                <td class="border border-[#CACACA] px-3 py-3">{{ item['UNIT'] }}</td>
                <td class="border border-[#CACACA] px-3 py-3">{{ item['JABATAN'] }}</td>
                <td class="text-center border border-[#CACACA] px-3 py-3">{{ item['AGAMA'] }}</td>
                <td class="text-center border border-[#CACACA] px-3 py-3">{{ item['TANGGAL MASUK'] }}</td>
                <td class="border border-[#CACACA] px-3 py-3 text-xs">{{ item['LAMA BEKERJA'] }}</td>
                
                <!-- Format Rupiah otomatis menggunakan toLocaleString -->
                <td class="text-right border border-[#CACACA] px-3 py-3 font-semibold whitespace-nowrap">
                  Rp {{ item['THR DITERIMA'].toLocaleString('id-ID') }}
                </td>
              </tr>
              
              <!-- State Data Kosong -->
              <tr v-if="!dataThr.length">
                <td colspan="9" class="text-center text-gray-500 py-12 italic border border-[#CACACA] bg-gray-50">
                  Tidak ada data karyawan pada cut-off dan filter terpilih.
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