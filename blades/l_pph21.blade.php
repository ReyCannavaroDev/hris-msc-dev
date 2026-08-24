@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Laporan PPh 21
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Tipe Export</label>
          <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.tipe"
            :errorText="formErrors.tipe ? 'failed' : ''" @input="v => values.tipe = v" :hints="formErrors.tipe"
            :check="false" label="" :options="['Excel']" placeholder="Pilih Tipe Export" valueField="key"
            displayField="key" />
        </div>

        <!-- Filter Periode Bulan -->
        <div>
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

        <div>
          <label class="font-semibold">Karyawan</label>
          <FieldSelect class="col-span-12 !mt-2 w-full" :bind="{clearable:false , multiple: true}"
            :value="values.m_kary_id" @input="v=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''"
            :hints="formErrors.m_kary_id" valueField="id" displayField="nama_lengkap" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Karyawan (Semua jika kosong)" label="" fa-icon="" :check="false" />
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
      <div class="flex flex-row justify-end space-x-[20px] mt-[1.5em]">
        <button 
          :disabled="isRequesting"
          @click="onGenerate" 
          class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] font-medium">
          <template v-if="isRequesting">
            Memproses...
          </template>
          <template v-else>
            Export Excel
          </template>
        </button>
      </div>
      <!-- END COLUMN -->
    </div>
  </div>
</div>
@endverbatim