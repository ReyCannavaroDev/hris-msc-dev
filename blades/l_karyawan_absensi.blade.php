@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Laporan Absensi Karyawan
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
        <!-- START COLUMN -->
        <div class="col-span-2">
          <label class="font-semibold">Tipe Report</label>
          <FieldSelect :bind="{ readonly: !actionText, clearable: false }" class="w-full py-2 !mt-0"
            :value="values.tipe_report" :errorText="formErrors.tipe_report ? 'failed' : ''"
            @input="v => values.tipe_report = v" @update:valueFull="(objVal)=>{
                  resetValuesPeriode()
                }" :hints="formErrors.tipe_report" :check="false" label=""
            :options="['Laporan Absensi Karyawan Detail', 'Laporan Rekap Absensi','Laporan Realisasi Lembur', 'Laporan Pelanggaran Absensi']" placeholder="Pilih Tipe Report Absensi" valueField="key"
            displayField="key" />
        </div>
        <div>
          <label class="font-semibold">Tipe Export</label>
          <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.tipe"
            :errorText="formErrors.tipe ? 'failed' : ''" @input="v => values.tipe = v" :hints="formErrors.tipe"
            :check="false" label="" :options="['Excel']" placeholder="Pilih Tipe Export" valueField="key"
            displayField="key" />
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

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-semibold">Periode Start
              <label class="text-red-500 space-x-0 pl-0">*</label>
            </label>
            <FieldX type="date" :bind="{ readonly: false }" class="w-full py-2 !mt-0" :value="values.date_start"
              label="" placeholder="DD/MM/YY" :errorText="formErrors.date_start?'failed':''"
              @input="v => { values.date_start = v; onDateStartChange(v) }" :hints="formErrors.date_start"
              :check="false" />
          </div>
          <div>
            <label class="font-semibold">Periode End</label>
            <FieldX type="date" :bind="{ readonly: false }" class="w-full py-2 !mt-0" :value="values.date_end" label=""
              placeholder="DD/MM/YY" :errorText="formErrors.date_end ? 'failed' : ''" @input="v => values.date_end = v"
              :hints="formErrors.date_end" :check="false" />
          </div>
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
      <div class="flex flex-row justify-end space-x-[20px] mt-[1em]">
        <button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] ">
          {{ values.tipe?.toLowerCase() === 'html' ? 'View' : 'Export' }}
        </button>
      </div>
      <!-- END COLUMN -->
      <!-- ACTION BUTTON START -->
      <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
        <hr>
        <div id="exportTable" class="w-[100%] mt-6 h-screen overflow-auto">
        </div>
      </div>

    </div>

  </div>

</div>
</div>
@endverbatim