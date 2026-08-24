@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-2 font-bold text-gray-800">
          Laporan Statistik Penggajian
        </h1>
        <p class="text-sm text-gray-500 mb-4">
          Analisis statistik makro pengeluaran gaji karyawan, perbandingan antar unit/departemen/jabatan, serta breakdown komponen upah & potongan.
        </p>
        <hr>
      </div>

      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[16px] px-4">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold block mb-1 text-gray-700">Tipe Output / Export <span class="text-red-500">*</span></label>
          <FieldSelect 
            :bind="{ readonly: false, clearable: false }" 
            class="w-full py-2 !mt-0" 
            :value="values.tipe"
            :errorText="formErrors.tipe ? 'failed' : ''" 
            @input="v => values.tipe = v" 
            :hints="formErrors.tipe"
            :check="false" 
            label="" 
            :options="['HTML', 'Excel', 'PDF']" 
            placeholder="Pilih Tipe Output" 
            valueField="key"
            displayField="key" 
          />
        </div>

        <div>
          <label class="font-semibold block mb-1 text-gray-700">Pengelompokan Statistik (Group By) <span class="text-red-500">*</span></label>
          <FieldSelect 
            :bind="{ readonly: false, clearable: false }" 
            class="w-full py-2 !mt-0" 
            :value="values.group_by"
            :errorText="formErrors.group_by ? 'failed' : ''" 
            @input="v => values.group_by = v" 
            :hints="formErrors.group_by"
            :check="false" 
            label="" 
            placeholder="Pilih Pengelompokan" 
            valueField="id"
            displayField="nama" 
            :options="[
              { id: 'unit', nama: 'Per Unit / Direktorat' },
              { id: 'divisi', nama: 'Per Divisi' },
              { id: 'dept', nama: 'Per Departemen' },
              { id: 'posisi', nama: 'Per Jabatan / Posisi' },
              { id: 'global', nama: 'Seluruh Perusahaan (Global Total)' }
            ]" 
          />
        </div>

        <!-- Filter Periode Rentang Tanggal -->
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Periode Dari <span class="text-red-500">*</span></label>
            <FieldX 
              type="date" 
              :bind="{ readonly: false, required: true }" 
              class="w-full py-2 !mt-0" 
              :value="values.periode_from" 
              label="" 
              placeholder="DD/MM/YYYY" 
              :errorText="formErrors.periode_from ? 'failed' : ''"
              @input="v => values.periode_from = v" 
              :hints="formErrors.periode_from" 
              :check="false" 
            />
          </div>
          <div>
            <label class="font-semibold block mb-1 text-gray-700">Periode Sampai <span class="text-red-500">*</span></label>
            <FieldX 
              type="date" 
              :bind="{ readonly: false, required: true }" 
              class="w-full py-2 !mt-0" 
              :value="values.periode_to" 
              label="" 
              placeholder="DD/MM/YYYY" 
              :errorText="formErrors.periode_to ? 'failed' : ''"
              @input="v => values.periode_to = v" 
              :hints="formErrors.periode_to" 
              :check="false" 
            />
          </div>
        </div>

        <div>
          <label class="font-semibold block mb-1 text-gray-700">Unit / Direktorat</label>
          <FieldSelect 
            :bind="{ readonly: false }" 
            class="w-full py-2 !mt-0" 
            :value="values.m_dir_id"
            :errorText="formErrors.m_dir_id ? 'failed' : ''" 
            @input="v => { values.m_dir_id = v; values.m_divisi_id = null; values.m_dept_id = null; }"
            :hints="formErrors.m_dir_id" 
            :check="false" 
            label="" 
            placeholder="Semua Unit" 
            valueField="id"
            displayField="nama" 
            :api="{
              url: `${store.server.url_backend}/operation/m_dir`,
              headers: { 
                'Content-Type': 'Application/json', 
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              params: {
                single: true,
                join: false
              }
            }" 
          />
        </div>

        <div>
          <label class="font-semibold block mb-1 text-gray-700">Divisi</label>
          <FieldSelect 
            :bind="{ readonly: false }" 
            class="w-full py-2 !mt-0" 
            :value="values.m_divisi_id"
            :errorText="formErrors.m_divisi_id ? 'failed' : ''" 
            @input="v => { values.m_divisi_id = v; values.m_dept_id = null; }" 
            :hints="formErrors.m_divisi_id" 
            :check="false" 
            label="" 
            placeholder="Semua Divisi" 
            valueField="id"
            displayField="nama" 
            :api="{
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
            }" 
          />
        </div>

        <div>
          <label class="font-semibold block mb-1 text-gray-700">Departemen</label>
          <FieldSelect 
            :bind="{ readonly: false }" 
            class="w-full py-2 !mt-0" 
            :value="values.m_dept_id"
            :errorText="formErrors.m_dept_id ? 'failed' : ''" 
            @input="v => values.m_dept_id = v" 
            :hints="formErrors.m_dept_id" 
            :check="false" 
            label="" 
            placeholder="Semua Departemen" 
            valueField="id"
            displayField="nama" 
            :api="{
              url: `${store.server.url_backend}/operation/m_dept`,
              headers: { 
                'Content-Type': 'Application/json', 
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              params: {
                single: true,
                join: false,
                where: `this.is_active='true'`
              }
            }" 
          />
        </div>

      </div>

      <div class="flex flex-row justify-end space-x-[20px] mt-[1.5em]">
        <button 
          :disabled="isRequesting"
          @click="onGenerate" 
          class="bg-blue-600 hover:bg-blue-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] font-semibold shadow-sm flex items-center gap-2">
          <template v-if="isRequesting">
            <span>Memproses...</span>
          </template>
          <template v-else>
            <icon v-if="values.tipe?.toLowerCase() === 'html'" fa="chart-bar" />
            <icon v-else fa="file-excel" />
            <span>{{ values.tipe?.toLowerCase() === 'html' ? 'Tampilkan Statistik' : 'Export Excel' }}</span>
          </template>
        </button>
      </div>

      <!-- HASIL STATISTIK CONTAINER -->
      <div class="overflow-x-auto mt-6 mb-4 px-2" v-show="exportHtml">
        <hr class="mb-4">
        <div id="exportTable">
        </div>
      </div>

    </div>
  </div>
</div>
@endverbatim