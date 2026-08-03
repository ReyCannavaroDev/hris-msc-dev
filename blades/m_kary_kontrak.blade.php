<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-[5px] border-white">
  <div class="flex items-center justify-between border-b !mt-3 pl-3 pr-3">
    <!-- LEFT: tabs -->
    <div class="flex space-x-5">
      <button
      @click="changeTab(0)"
      class="pb-2 pt-2 transition-all inline-block"
      :class="activeTabIndex === 0
        ? 'text-black font-semibold border-b-2 border-blue-600'
        : 'text-gray-400 font-normal'"
    >
      List karyawan 
    </button>

      <button
      @click="changeTab(1)"
      class="pb-2 pt-2 transition-all inline-block"
      :class="activeTabIndex === 1
        ? 'text-black font-semibold border-b-2 border-blue-600'
        : 'text-gray-400 font-normal'"
    >
      List perpanjangan kontrak
    </button>
    </div>

    <div class="flex items-center gap-3">
      <FieldX v-if="activeTabIndex === 0" type="month" :value="startMonth"
        :errorText="formErrors.start_month ? 'failed' : ''" :hints="formErrors.start_month" placeholder="Periode Awal"
        label="Periode Awal" class="pb-5" :check="false" @input="v => updateTanggal('start_month', v)" />

      <FieldX v-if="activeTabIndex === 0" type="month" :value="endMonth"
        :errorText="formErrors.end_month ? 'failed' : ''" :hints="formErrors.end_month" placeholder="Periode Akhir"
        label="Periode Akhir" class="pb-5" :check="false" @input="v => updateTanggal('end_month', v)" />

      <button
    v-if="activeTabIndex === 0"
    @click="downloadExcelKont"
    class="flex items-center bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md py-2 px-3 transition"
  >
    <icon fa="download" />
  </button>

    </div>

    <div v-if="activeTabIndex === 1" class="flex items-center gap-3">
      <!-- <FieldX v-if="activeTabIndex === 1" type="month" :value="startMonth1"
        :errorText="formErrors.start_month ? 'failed' : ''" :hints="formErrors.start_month" placeholder="Periode Awal"
        label="Periode Awal" class="pb-5" :check="false" @input="v => updateTanggal('start_month', v)" />

      <FieldX v-if="activeTabIndex === 1" type="month" :value="endMonth1"
        :errorText="formErrors.end_month ? 'failed' : ''" :hints="formErrors.end_month" placeholder="Periode Akhir"
        label="Periode Akhir" class="pb-5" :check="false" @input="v => updateTanggal('end_month', v)" /> -->

      <button
    v-if="activeTabIndex === 1"
    @click="downloadExcel"
    class="flex items-center bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md py-2 px-3 transition"
  >
    <icon fa="download" />
  </button>

    </div>

  </div>

  <!-- :actions="landingKaryEx.actions" -->
  <TableApi ref="apiTable" v-show="activeTabIndex===0" :api="landingKaryEx.api" :columns="landingKaryEx.columns"
    class="max-h-[450px]">
  </TableApi>
  <TableApi ref="apiTable2" v-show="activeTabIndex===1" :api="landing.api" :columns="landing.columns"
    :actions="landing.actions" class="max-h-[450px]">
  </TableApi>
</div>

@else

<!-- CONTENT -->
@verbatim

<div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white ">

  <div class="flex items-center gap-3 mb-2">
    <button
    class="py-1 px-2 rounded transition-all text-blue-900 bg-white border border-blue-900 duration-300 hover:text-white hover:bg-blue-600"
    @click="onBack"
  >
    <icon fa="arrow-left" size="sm" />
  </button>
    <h1 class="text-[24px] font-bold">
      Form Perpanjang Kontrak
    </h1>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9">
    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldSelect :bind="{ disabled: true, clearable:false }" class="col-span-12 !mt-0 w-full"
          :value="values.m_karyawan_id" @input="v=>values.m_karyawan_id=v"
          :errorText="formErrors.m_karyawan_id?'failed':''" label="" placeholder="Pilih Unit"
          :hints="formErrors.m_karyawan_id" :api="{
          url: `${store.server.url_backend}/operation/m_kary`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: { simplest:true, where: `this.is_active = 'true'` }
        }" valueField="id" displayField="nama_depan" :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Unit<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
          :value="values.m_dir_id" @input="v=>values.m_dir_id=v" :errorText="formErrors.m_dir_id?'failed':''"
          @update:valueFull="(objVal)=>{ $log('dir',objVal.nama); values.m_dir_nama = objVal.nama }" label=""
          placeholder="Pilih Unit" :hints="formErrors.m_dir_id" :api="{
          url: `${store.server.url_backend}/operation/m_dir`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: { simplest:true, where: `this.is_active = 'true'` }
        }" valueField="id" displayField="nama" :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Jabatan<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
          :value="values.m_divisi_id" @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''"
          @update:valueFull="(dt)=>{ $log('jabatan',dt.nama); values.nameDiv = dt.nama }" label=""
          placeholder="Pilih Jabatan" :hints="formErrors.m_divisi_id" :api="{
          url: `${store.server.url_backend}/operation/m_divisi`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: { simplest:true, where: `this.is_active = 'true'` }
        }" valueField="id" displayField="nama" :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Tipe Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
          :value="values.tipe_karyawan_id" label="" placeholder="Tipe Karyawan" @input="v=>values.tipe_karyawan_id=v"
          :errorText="formErrors.tipe_karyawan_id?'failed':''"
          @update:valueFull="(objVal)=>{ values.tipeKary = objVal.value }" :hints="formErrors.tipe_karyawan_id"
          valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest: true,
            transform: false,
            where: `this.group='TIPE KARYAWAN' AND this.is_active='true'`,
            join: true,
            selectfield: 'this.id, this.code, this.value, this.is_active'
          }
        }" :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Status<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldX class="col-span-12 !mt-0 w-full" :bind="{ readonly: true }" :value="values.status"
          :errorText="formErrors.status?'failed':''" @input="v=>values.status=v" :hints="formErrors.status"
          placeholder="" label="" fa-icon="" :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Template Kontrak<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldUpload :bind="{ disabled: !actionText}" class="col-span-12 !mt-0 w-full" :value="values.contract_template"
          @input="(v)=>values.contract_template=v" :maxSize="10"
          :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
            url: `${store.server.url_backend}/operation/t_extend_kontrak/upload`,
            headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
            params: { field: 'contract_template' },
            onsuccess: response=>response,
            onerror:(error)=>{},
           }" :hints="formErrors.contract_template" placeholder="" label="" fa-icon="upload" accept="*"
          :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Kontrak Full<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldUpload :bind="{ disabled: !actionText}" class="col-span-12 !mt-0 w-full" :value="values.contract_signed"
          @input="(v)=>values.contract_signed=v" :maxSize="10"
          :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
            url: `${store.server.url_backend}/operation/t_extend_kontrak/upload`,
            headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
            params: { field: 'contract_signed' },
            onsuccess: response=>response,
            onerror:(error)=>{},
           }" :hints="formErrors.contract_signed" placeholder="" label="" fa-icon="upload" accept="*" :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Durasi Kontrak<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
          label="" placeholder="Pilih Durasi Kontrak" :value="values.duration" @input="v=>values.duration=v"
          :options="[{label: '7 Hari', value: 7}, { label: '3 Bulan', value: 3 },{ label: '6 Bulan ', value: 6 },{ label: '12 Bulan ', value: 12 }]"
          :errorText="formErrors.duration?'failed':''" :hints="formErrors.duration" valueField="value"
          displayField="label" :check="false" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Tanggal Awal<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldX type="date" :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
          :value="values.tgl_awal" label="" placeholder="Tuliskan Tanggal" @input="v=>values.tgl_awal=v" :check="false"
          :errorText="formErrors.tgl_awal?'failed':''" :hints="formErrors.tgl_awal" />
      </div>
    </div>

    <div>
      <div class="grid grid-cols-12 items-center">
        <label class="col-span-12">Tanggal Akhir<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <FieldX type="date" :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
          :value="values.tgl_akhir" label="" placeholder="Tuliskan Tanggal" @input="v=>values.tgl_akhir=v"
          :check="false" :errorText="formErrors.tgl_akhir?'failed':''" :hints="formErrors.tgl_akhir" />
      </div>
    </div>

    <div type="textarea" class="grid grid-cols-12 items-center">
      <label type="textarea" class="col-span-12 font-semibold">Catatan</label>
      <FieldX :bind="{ readonly: false }" class="col-span-12 !mt-0 w-full" :value="values.catatan"
        :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v" :hints="formErrors.catatan"
        :check="false" label="" placeholder="Tuliskan catatan" />
    </div>

    <div v-show="route.query.is_approval" type="textarea" class="grid grid-cols-12 items-center">
      <label class="col-span-12 font-semibold">Catatan Approval<label class="text-red-500 space-x-0 pl-0">*</label></label>
      <FieldX :bind="{ readonly: false }" class="col-span-12 !mt-0 w-full" :value="values.catatan_approval"
        :errorText="formErrors.catatan_approval?'failed':''" @input="v=>values.catatan_approval=v"
        :hints="formErrors.catatan_approval" :check="false" label="" placeholder="Tuliskan catatan" />
    </div>

    <div class="md:col-span-2">
      <h2 class="font-semibold text-[20px]">Detail Kontrak</h2>

      <TableStatic customClass="h-50vh" ref="detail" :value="detailKont" :columns="[
        {      headerName: 'No',
      valueGetter: p => p.node.rowIndex + 1,
      width: 60,
      sortable: false,
      resizable: true,
      filter: false,
      wrapText: true,
      cellClass: ['justify-center', 'bg-gray-50']},
        { flex: 1, headerName: 'Unit', field: 'm_dir.nama', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Jabatan', field: 'm_divisi.nama', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Status Karyawan', field: 'tipe_karyawan.value', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Tanggal Mulai', field: 'tgl_awal', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Tanggal Selesai', field: 'tgl_akhir', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Status', field: 'status', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'], cellRenderer: (params) => { return params.value == 1 ? 'Aktif' : 'Non Aktif' } }
      ]">
      </TableStatic>
    </div>

    <div class="flex justify-end col-span-1 md:col-span-2">
      <button v-show="route.query.is_approval" class="mx-1 bg-green-500 text-white hover:bg-green-600 rounded-[4px] px-[36.5px] py-[5px]" @click="onProcess('approve')">
              Approve
            </button>
      <button v-show="route.query.is_approval" class="mx-1 bg-rose-500 text-white hover:bg-rose-600 rounded-[4px] px-[36.5px] py-[5px]" @click="onProcess('reject')">
              Reject
            </button>
      <button v-show="route.query.is_approval" class="mx-1 bg-amber-500 text-white hover:bg-amber-600 rounded-[4px] px-[36.5px] py-[5px]" @click="onProcess('revise')">
              Revise
            </button>
      <button v-show="route.query.action?.toLowerCase() === 'verifikasi'" @click="posted" class="bg-orange-500 hover:bg-orange-600 text-white px-[36.5px] py-[5px] font-semibold rounded-[4px] ">
            Posted
          </button>
      <button v-show="actionText" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[5px] font-semibold rounded-[4px]">
            Simpan
          </button>
    </div>

  </div>
  <!-- <div>
    <button v-show="route.query.is_approval" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium" @click="onProcess('approve')">Approve</button>
    <button v-show="route.query.is_approval" class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-2 rounded-md font-medium" @click="onProcess('reject')">Reject</button>
    <button v-show="route.query.is_approval" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 rounded-md font-medium" @click="onProcess('revise')">Revise</button>
    <button v-show="route.query.action?.toLowerCase() === 'verifikasi'" @click="posted" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-md font-medium">Posted</button>
    <button v-show="actionText" @click="onSave" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-md font-medium">Simpan</button>
  </div> -->
</div>
@endverbatim
@endif