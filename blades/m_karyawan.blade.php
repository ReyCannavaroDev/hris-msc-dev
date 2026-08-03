@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <!-- <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Active</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inactive</button>
      </div> -->
    </div>
    <div class="flex gap-3">

      <!-- Create New -->
      <RouterLink :to="$route.path + '/create?' + Date.now()" class="inline-flex items-center gap-2 border border-blue-600 text-blue-600 bg-white 
           hover:bg-blue-600 hover:text-white transition-all duration-300 
           rounded-lg py-1 px-2 shadow-sm hover:shadow-md">
        <icon fa="plus" class="text-sm" />
        <span>Create New</span>
      </RouterLink>

      <!-- Upload File -->
      <label
    for="fileUpload"
    class="inline-flex items-center gap-2 cursor-pointer border border-green-600 
           text-green-600 bg-white hover:bg-green-600 hover:text-white 
           transition-all duration-300 rounded-lg py-1 px-2 shadow-sm hover:shadow-md"
  >
    <icon fa="upload" class="text-sm" />
    <span>Upload File</span>
  </label>

      <input
    id="fileUpload"
    type="file"
    class="sr-only"
    @change="(e) => uploadFile(e.target.files[0])"
  />

      <!-- Download Excel -->
      <button
    @click="downloadExcel"
    class="inline-flex items-center gap-2 bg-yellow-500 text-white 
           hover:bg-yellow-600 transition-all duration-300 
           font-medium rounded-lg py-1 px-2 shadow-sm hover:shadow-md"
  >
    <icon fa="download" class="text-sm" />
    <span>Download</span>
  </button>

    </div>


  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="max-h-[450px]">
    <!-- <template #header>
    </template> -->
  </TableApi>
</div>
@else

@verbatim

<div class="flex flex-col gap-y-2 scroll-auto max-h-[470px]">

  <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white ">

    <!-- HEADER START -->
    <div class="flex flex-col items-start mb-2">
      <h1 class="text-[24px] mb-[10px] font-bold">
        Form Karyawan
      </h1>
    </div>
    <!-- HEADER END -->
    <div class="flex items-stretch w-full text-sm overflow-x-auto">
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 0}"
            @click="activeTabIndex = 0"
          >
            Informasi
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 8}"
            @click="activeTabIndex = 8"
          >
            Kontrak
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 1}"
            @click="activeTabIndex = 1"
          >
            Pendidikan
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 hover:border-blue-600 hover:text-blue-600 duration-300 border-gray-100 p-3"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 2}"
            @click="activeTabIndex = 2"
          >
            Keluarga
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 3}"
            @click="activeTabIndex = 3"
          >
            Pelatihan
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 4}"
            @click="activeTabIndex = 4"
          >
            Prestasi
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 5}"
            @click="activeTabIndex = 5"
          >
            Organisasi
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 6}"
            @click="activeTabIndex = 6"
          >
            Bahasa
          </button>
      <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:border-blue-600 hover:text-blue-600 duration-300"
            :class="{'border-blue-600 text-blue-600 font-bold': activeTabIndex === 7}"
            @click="activeTabIndex = 7"
          >
            Pengalaman Kerja
          </button>
    </div>

    <!-- Form Informasi -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 0">
      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Unit<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.m_dir_id" @input="v=>values.m_dir_id=v" :errorText="formErrors.m_dir_id?'failed':''"
            @update:valueFull="(objVal)=>{
          values.m_dir_id = null
        }" label="" placeholder="Pilih Unit" :hints="formErrors.m_dir_id" :api="{
          url: `${store.server.url_backend}/operation/m_dir`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest:true,
            where: `this.is_active = 'true'`
          }
        }" valueField="id" displayField="nama" :check="false" />
        </div>
      </div>

      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Jabatan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.m_divisi_id" @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''"
            @update:valueFull="(objVal)=>{
          values.m_dept_id = null
        }" label="" placeholder="Pilih Jabatan" :hints="formErrors.m_divisi_id" :api="{
          url: `${store.server.url_backend}/operation/m_divisi`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest:true,
            where: `this.is_active = 'true'`
          }
        }" valueField="id" displayField="nama" :check="false" />
        </div>
      </div>

      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tipe Jam Kerja<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tipe_jam_kerja_id" @input="v => values.tipe_jam_kerja_id = v"
            :errorText="formErrors.tipe_jam_kerja_id ? 'failed' : ''" label="" placeholder="Pilih Tipe Jam Kerja"
            :hints="formErrors.tipe_jam_kerja_id" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            where:`this.group='TIPEJAM' AND this.is_active='true'`,
            join:true, 
            selectfield: 'this.id, this.code, this.value, this.is_active'
          }
        }" valueField="id" displayField="value" :check="false" />
        </div>
      </div>

      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Jadwal Kerja<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldPopup :bind="{ readonly: !actionText }" class="col-span-12 !mt-0 w-full"
            :value="values.t_jadwal_kerja_id" @input="(v)=>values.t_jadwal_kerja_id=v"
            :errorText="formErrors.t_jadwal_kerja_id?'failed':''" :hints="formErrors.t_jadwal_kerja_id" valueField="id"
            displayField="nomor" @update:valueFull="(objVal)=>{  
                  values.t_jadwal_kerja_ket = objVal.keterangan
                }" :api="{
                  url: `${store.server.url_backend}/operation/t_jadwal_kerja`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    where: `this.status = 'POSTED' AND tipe_jam_kerja.id = ${values.tipe_jam_kerja_id}`,
                    searchfield:'this.id, this.nomor, this.keterangan',
                  }
                }" placeholder="Pilih Jadwal Kerja" label="" :check="false" :columns="[{
                  headerName: 'No',
                  valueGetter:(p)=>p.node.rowIndex + 1,
                  width: 60,
                  sortable: false, resizable: false, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                {
                  flex: 1,
                  field: 'nomor',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                },
                {
                  flex: 1,
                  field: 'keterangan',
                  sortable: false, resizable: true, filter: 'ColFilter', wrapText: true,
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                }
                ]" />
        </div>
        <i class="mt-2">{{values.t_jadwal_kerja_ket}}</i>
      </div>

      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tipe Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tipe_karyawan_id" @input="v => values.tipe_karyawan_id = v"
            :errorText="formErrors.tipe_karyawan_id ? 'failed' : ''" label="" placeholder="Pilih Tipe Karyawan"
            :hints="formErrors.tipe_karyawan_id" :api="{
                      url: `${store.server.url_backend}/operation/m_general`,
                      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: {
                        where:`this.group='TIPE KARYAWAN' AND this.is_active='true'`,
                        join:true, 
                        selectfield: 'this.id, this.code, this.value, this.is_active'
                      }
                  }" valueField="id" displayField="value" :check="false" />
        </div>
      </div>

      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Status<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.is_active" @input="v=>values.is_active=v" :errorText="formErrors.is_active?'failed':''"
            :hints="formErrors.is_active" label="" placeholder="Pilih Status"
            :options="[{'id' : 1 , 'key' : 'Active'},{'id': 0, 'key' : 'InActive'}]" valueField="id" displayField="key"
            :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 gap-2 items-center">
          <label class="col-span-12">Kontrak Berjalan<span class="text-red-500">*</span></label>
          <div class="col-span-6">
            <label class="block text-sm font-medium">Bulan</label>
            <FieldX :bind="{ readonly: true }" class="!mt-0 w-full" :value="values.total_bulan"
              placeholder="Auto Field By System" :errorText="formErrors.total_bulan ? 'failed' : ''"
              @input="v => values.total_bulan = v" :hints="formErrors.total_bulan" :check="false" />
          </div>

          <div class="col-span-6">
            <label class="block text-sm font-medium">Tahun</label>
            <FieldX :bind="{ readonly: true }" class="!mt-0 w-full" :value="values.total_tahun"
              placeholder="Auto Field By System" :errorText="formErrors.total_tahun ? 'failed' : ''"
              @input="v => values.total_tahun = v" :hints="formErrors.total_tahun" :check="false" />
          </div>
        </div>
      </div>

      <div>
        <label class="col-span-12">Can Outscope<label class="text-red-500 space-x-0 pl-0">*</label></label>
        <div class="mt-2 flex w-40 mt-8">
          <div class="flex-auto">
            <i class="text-red-500">Tidak</i>
          </div>
          <div class="flex-auto">
            <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': values.can_outscope === false}"
                role="switch"
                id="can_outscope_for_click"
                :disabled="!actionText"
                v-model="values.can_outscope"
                />
          </div>
          <div class="flex-auto">
            <i class="text-green-500">Ya</i>
          </div>
        </div>
      </div>

      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Standard Gaji<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.m_standart_gaji_id" @input="v => values.m_standart_gaji_id = v"
            :errorText="formErrors.m_standart_gaji_id ? 'failed' : ''" label="" placeholder="Pilih Tipe Karyawan"
            :hints="formErrors.m_standart_gaji_id" :api="{
                      url: `${store.server.url_backend}/operation/m_standart_gaji`,
                      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: {
                        //where:`this.group='TIPE KARYAWAN' AND this.is_active='true'`,
                        //join:true, 
                        //selectfield: 'this.id, this.code, this.value, this.is_active'
                      }
                  }" valueField="id" displayField="desc" :check="false" />
        </div>
      </div>

      <!-- NOT PROFILE -->
      <div v-if="!isProfile && is_superadmin === true"></div>
      <h2 class="font-bold text-[18px] ">Data Karyawan</h2>
      <div></div>


      <div>
        <div class="grid grid-cols-12 gap-2 items-center">
          <div class="col-span-6">
            <label class="block text-sm">ID Karyawan</label>
            <FieldX :bind="{ readonly: true }" class="!mt-0 w-full" :value="values.kode" placeholder="Autogenerated"
              label="" :errorText="formErrors.kode ? 'failed' : ''" @input="v => values.kode = v"
              :hints="formErrors.kode" :check="false" />
          </div>

          <div class="col-span-6">
            <label class="block text-sm">NIK</label>
            <FieldX :bind="{ readonly: !actionText }" class="!mt-0 w-full" :value="values.nik"
              placeholder="Masukan Nomor Induk Kependudukan" label="" :errorText="formErrors.nik ? 'failed' : ''"
              @input="v => values.nik = v" :hints="formErrors.nik" :check="false" />
          </div>
        </div>
      </div>

      <!-- <div class="col-span-8 md:col-span-6 gap-x-4">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Nama Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText && !isProfile }" type="text" class="col-span-6 !mt-0 w-full"
              :value="values.kode" label="" placeholder="Masukan Nomor Induk Karyawan"
              :errorText="formErrors.kode?'failed':''" @input="v=>values.kode=v" :hints="formErrors.kode"
              :check="false" />

            <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-6 !mt-0 w-full"
              :value="values.nik" label="" placeholder="Tuliskan NIK"
              :errorText="formErrors.nik?'failed':''" @input="v=>values.nik=v"
              :hints="formErrors.nik" :check="false" />
          </div>
        </div> -->

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Atasan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldPopup :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="values.atasan_id" @input="(v)=>values.atasan_id=v" :errorText="formErrors.atasan_id?'failed':''"
            :hints="formErrors.atasan_id" valueField="id" displayField="nama_lengkap" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  where: `this.is_active = true`,
                  searchfield: 'this.nik, this.nama_lengkap, this.nama_depan, this.nama_belakang, m_zona.nama, m_dir.nama, m_divisi.nama, m_dept.nama'
                }
              }" placeholder="Cari Nomor Induk Karyawan" label="" :check="false" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'nik',
                wrapText:true,
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-end']
              },
              {
                flex: 1,
                field: 'nama_lengkap',
                wrapText:true,
                headerName: 'Nama Karyawan',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                field: 'm_zona.nama',
                wrapText:true,
                headerName: 'Zona',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'm_dir.nama',
                headerName: 'Direktorat',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'm_divisi.nama',
                headerName: 'Divisi',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'm_dept.nama',
                headerName: 'Departemen',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              ]" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Pilih Lokasi</span></label>
          <FieldSelect class="w-full !mt-0 col-span-12" :bind="{ disabled: !actionText && !isProfile, clearable:false }"
            :value="values.presensi_lokasi_default_id" @input="v=>values.presensi_lokasi_default_id=v"
            :errorText="formErrors.presensi_lokasi_default_id?'failed':''"
            :hints="formErrors.presensi_lokasi_default_id" label="" valueField="id" displayField="nama" :api="{
                    url: `${store.server.url_backend}/operation/presensi_lokasi`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      selectfield: 'this.id, this.nama, this.lat, this.long'
                    }
                  }" placeholder="Pilih Master Lokasi" :check="false" />
        </div>
      </div>

      <!-- <div>
        <div class="grid grid-cols-12 gap-x-2 items-center">
          <label class="col-span-12">Nama Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-6 !mt-0 w-full"
            :value="values.nama_depan" label="" placeholder="Tuliskan Nama Depan"
            :errorText="formErrors.nama_depan?'failed':''" @input="v=>values.nama_depan=v"
            :hints="formErrors.nama_depan" :check="false" />
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-6 !mt-0 w-full"
            :value="values.nama_belakang" label="" placeholder="Tuliskan Nama Belakang"
            :errorText="formErrors.nama_belakang?'failed':''" @input="v=>values.nama_belakang=v"
            :hints="formErrors.nama_belakang" :check="false" />
        </div>
      </div> -->

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nama Lengkap<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="values.nama_lengkap" label="" placeholder="Tuliskan Nama Panggilan Karyawan"
            :errorText="formErrors.nama_lengkap?'failed':''" @input="v=>values.nama_lengkap=v"
            :hints="formErrors.nama_lengkap" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nama Panggilan Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="values.nama_panggilan" label="" placeholder="Tuliskan Nama Panggilan Karyawan"
            :errorText="formErrors.nama_panggilan?'failed':''" @input="v=>values.nama_panggilan=v"
            :hints="formErrors.nama_panggilan" :check="false" />
        </div>
      </div>


      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Jenis Kelamin<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.jk_id" label="" placeholder="Pilih Jenis Kelamin" @input="v=>values.jk_id=v"
            :errorText="formErrors.jk_id?'failed':''" :hints="formErrors.jk_id" valueField="id" displayField="value"
            :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    where: `this.group='JENIS KELAMIN' AND this.is_active='true'`,
                    join: true,
                    selectfield: 'this.id, this.code, this.value, this.is_active'
                  }
                }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tempat, Tanggal Lahir<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-6 !mt-0 w-full"
            :value="values.tempat_lahir" @input="v=>values.tempat_lahir=v"
            :errorText="formErrors.tempat_lahir?'failed':''" :hints="formErrors.tempat_lahir" label=""
            placeholder="Pilih Kota" valueField="value" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      join: true,
                      where: `this.group='KOTA'`,
                      paginate: 1000
                    }
                  }" :check="false" />
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="date" class="col-span-6 !mt-0 w-full"
            :value="values.tgl_lahir" label="" placeholder="Pilih Tanggal" :errorText="formErrors.tgl_lahir?'failed':''"
            @input="v=>values.tgl_lahir=v" :hints="formErrors.tgl_lahir" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Alamat Tinggal<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="textarea" class="col-span-12 !mt-0 w-full"
            :value="values.alamat_domisili" label="" placeholder="Tuliskan Alamat"
            :errorText="formErrors.alamat_domisili?'failed':''" @input="v=>values.alamat_domisili=v"
            :hints="formErrors.alamat_domisili" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Provinsi<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.provinsi_id" @input="v=>values.provinsi_id=v" :errorText="formErrors.provinsi_id?'failed':''"
            @update:valueFull="(objVal)=>{
                    values.kota_id = '',
                    values.kecamatan_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.provinsi_id" label="" placeholder="Pilih Provinsi" valueField="id"
            displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genProvinsi',
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.kota_id" @input="v=>values.kota_id=v" :errorText="formErrors.kota_id?'failed':''"
            @update:valueFull="(objVal)=>{
                    values.kecamatan_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.kota_id" label="" placeholder="Pilih Kota" valueField="id" displayField="value"
            :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKota',
                      provinsi_id: values.provinsi_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Kecamatan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.kecamatan_id" @input="v=>values.kecamatan_id=v"
            :errorText="formErrors.kecamatan_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kode_pos = ''
                  }" :hints="formErrors.kecamatan_id" label="" placeholder="Pilih Kecamatan" valueField="id"
            displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKecamatan',
                      kota_id: values.kota_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Kode Pos<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.kode_pos" label="" placeholder="Tuliskan Kode Pos"
            :errorText="formErrors.kode_pos?'failed':''" @input="v=>values.kode_pos=v" :hints="formErrors.kode_pos"
            :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No. Telepon<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.no_tlp" label="" placeholder="Tuliskan Nomer Telepon"
            :errorText="formErrors.no_tlp?'failed':''" @input="v=>values.no_tlp=v" :hints="formErrors.no_tlp"
            :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No. Telepon Lainnya</label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.no_tlp_lainnya" label="" placeholder="Tuliskan Nomer Telepon Lainnya"
            :errorText="formErrors.no_tlp_lainnya?'failed':''" @input="v=>values.no_tlp_lainnya=v"
            :hints="formErrors.no_tlp_lainnya" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No. Telepon Darurat<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.no_darurat" label="" placeholder="Tuliskan Nomer Telepon Darurat"
            :errorText="formErrors.no_darurat?'failed':''" @input="v=>values.no_darurat=v"
            :hints="formErrors.no_darurat" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nama Kontak Darurat<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="values.nama_kontak_darurat" label="" placeholder="Tuliskan Nama Kontak Darurat"
            :errorText="formErrors.nama_kontak_darurat?'failed':''" @input="v=>values.nama_kontak_darurat=v"
            :hints="formErrors.nama_kontak_darurat" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Hubungan Dengan Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="values.hub_dgn_karyawan" label="" placeholder="Tulis Hubungan Dengan Karyawan"
            :errorText="formErrors.hub_dgn_karyawan?'failed':''" @input="v=>values.hub_dgn_karyawan=v"
            :hints="formErrors.hub_dgn_karyawan" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Agama<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.agama_id" @input="v=>values.agama_id=v" :errorText="formErrors.agama_id?'failed':''"
            :hints="formErrors.agama_id" label="" placeholder="Pilih Agama" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='AGAMA' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Golongan Darah<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.gol_darah_id" @input="v=>values.gol_darah_id=v"
            :errorText="formErrors.gol_darah_id?'failed':''" :hints="formErrors.gol_darah_id" label=""
            placeholder="Pilih Golongan Darah" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='GOLONGAN DARAH' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Status Pernikahan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.status_nikah_id" @input="v=>values.status_nikah_id=v"
            :errorText="formErrors.status_nikah_id?'failed':''" :hints="formErrors.status_nikah_id" label=""
            placeholder="Pilih Status Pernikahan" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='STATUS NIKAH' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Jumlah Tanggungan<label class="text-red-500 space-x-0 pl-0"></label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tanggungan_id" @input="v=>values.tanggungan_id=v"
            :errorText="formErrors.tanggungan_id?'failed':''" :hints="formErrors.tanggungan_id" label=""
            placeholder="Pilih Tanggungan" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='TANGGUNGAN' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
      </div>
      <h2 class="font-bold text-[18px]">Berkas Karyawan</h2>
      <div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Foto Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <div class="col-span-12 flex items-center">
            <input :disabled="!actionText && !isProfile ? true : false" ref="refPasFoto" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.pas_foto}" id="inputPasFoto" @change="imageChange">

          </div>
          <img :src="urlPasFoto" class="col-span-12 !mt-0 w-[231px]">
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Foto KTP<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <div class="col-span-12 flex items-center">
            <input :disabled="!actionText && !isProfile ? true : false" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.ktp_foto}" id="inputKTPFoto" @change="imageChange">

          </div>
          <img :src="urlKTPFoto" class="col-span-12 !mt-0 w-[231px]">
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Alamat Sesuai KTP<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="textarea" class="col-span-12 !mt-0 w-full"
            :value="values.alamat_asli" label="" placeholder="Tuliskan Alamat Sesuai KTP"
            :errorText="formErrors.alamat_asli?'failed':''" @input="v=>values.alamat_asli=v"
            :hints="formErrors.alamat_asli" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Foto Kartu Keluarga<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <div class="col-span-12 flex items-center">
            <input :disabled="!actionText && !isProfile ? true : false" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.kk_foto}" id="inputKKFoto" @change="imageChange">

          </div>
          <img :src="urlKKFoto" class="col-span-12 !mt-0 w-[231px]">
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No. Kartu Keluarga<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.kk_no" label="" placeholder="Tuliskan Nomor Kartu Keluarga"
            :errorText="formErrors.kk_no?'failed':''" @input="v=>values.kk_no=v" :hints="formErrors.kk_no"
            :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Foto NPWP<label class="text-red-500 space-x-0 pl-0"></label></label>
          <div class="col-span-12 flex items-center">
            <input :disabled="!actionText && !isProfile ? true : false" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.npwp_foto}" id="inputNPWPFoto" @change="imageChange">

          </div>
          <img :src="urlNPWPFoto" class="col-span-12 !mt-0 w-[231px]">
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No. NPWP<label class="text-red-500 space-x-0 pl-0"></label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.npwp_no" label="" placeholder="Tuliskan Nomor Pokok Wajib Pajak"
            :errorText="formErrors.npwp_no?'failed':''" @input="v=>values.npwp_no=v" :hints="formErrors.npwp_no"
            :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tanggal Berlaku NPWP<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="date" class="col-span-12 !mt-0 w-full"
            :value="values.npwp_tgl_berlaku" label="" placeholder="Masukan Tanggal Berlaku NPWP"
            :errorText="formErrors.npwp_tgl_berlaku?'failed':''" @input="v=>values.npwp_tgl_berlaku=v"
            :hints="formErrors.npwp_tgl_berlaku" :check="false" />
        </div>
      </div>
      <!-- <div >
            <div class="grid grid-cols-12 items-center">
              <label class="col-span-12">Foto BPJS<label class="text-red-500 space-x-0 pl-0">*</label></label>
              <div class="col-span-12 flex items-center">
                <input :disabled="!actionText && !isProfile ? true : false" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.bpjs_foto}" id="inputBPJSFoto" @change="imageChange">
                <svg v-show="formErrors.bpjs_foto" class="svg-inline--fa fa-circle-exclamation fa-fw page-length-selector fa-md absolute right-2 fa-sm fa-fw text-red-400" aria-labelledby="svg-inline--fa-title-TuHui35w8qVB" data-prefix="fas" data-icon="circle-exclamation" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <title class="" id="svg-inline--fa-title-TuHui35w8qVB">failed</title>
                  <path class="" fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"></path>
                </svg>
              </div>
              <img :src="urlBPJSFoto" class="col-span-12 !mt-0 w-[231px]">
            </div>
          </div> -->
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No. BPJS Kesehatan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.bpjs_no_kesehatan" label="" placeholder="Tuliskan Nomor BPJS"
            :errorText="formErrors.bpjs_no_kesehatan?'failed':''" @input="v=>values.bpjs_no_kesehatan=v"
            :hints="formErrors.bpjs_no_kesehatan" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No. BPJS Ketenagakerjaan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.bpjs_no_ketenagakerjaan" label="" placeholder="Tuliskan Nomor BPJS"
            :errorText="formErrors.bpjs_no_ketenagakerjaan?'failed':''" @input="v=>values.bpjs_no_ketenagakerjaan=v"
            :hints="formErrors.bpjs_no_ketenagakerjaan" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tipe BPJS<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.bpjs_tipe_id" @input="v=>values.bpjs_tipe_id=v"
            :errorText="formErrors.bpjs_tipe_id?'failed':''" :hints="formErrors.bpjs_tipe_id" label=""
            placeholder="Pilih Tipe BPJS" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='TIPE BPJS' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Berkas Pendukung Lainnya<label class="text-red-500 space-x-0 pl-0"></label></label>
          <FieldUpload class="col-span-12 !mt-0 w-full" :bind="{ readonly: !actionText && !isProfile }"
            :value="values.berkas_lain" @input="(v)=>values.berkas_lain=v" :maxSize="10"
            :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                  url: `${store.server.url_backend}/operation/m_kary_det_kartu/upload`,
                  headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: { field: 'berkas_lain' },
                  onsuccess: response=>response,
                  onerror:(error)=>{},
                 }" :hints="formErrors.berkas_lain" label="" placeholder="Upload Berkas" fa-icon="upload"
            accept="application/pdf" :check="false" />

          <!-- <div class="col-span-12 flex items-center">
                <input type="file" accept="application/pdf" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.berkas_lain}" id="inputBerkasLainFoto" @change="imageChange">
                <svg v-show="formErrors.berkas_lain" class="svg-inline--fa fa-circle-exclamation fa-fw page-length-selector fa-md absolute right-2 fa-sm fa-fw text-red-400" aria-labelledby="svg-inline--fa-title-TuHui35w8qVB" data-prefix="fas" data-icon="circle-exclamation" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <title class="" id="svg-inline--fa-title-TuHui35w8qVB">failed</title>
                  <path class="" fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"></path>
                </svg>
              </div> -->
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Keterangan<label class="text-red-500 space-x-0 pl-0"></label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="textarea" class="col-span-12 !mt-0 w-full"
            :value="values.desc_file" label="" placeholder="Tuliskan Keterangan"
            :errorText="formErrors.desc_file?'failed':''" @input="v=>values.desc_file=v" :hints="formErrors.desc_file"
            :check="false" />
        </div>
      </div>

      <div></div>
      <h2 class="font-bold text-[18px]">Ukuran</h2>
      <div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Ukuran Baju<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.uk_baju" @input="v=>values.uk_baju=v" :errorText="formErrors.uk_baju?'failed':''"
            :hints="formErrors.uk_baju" label="" placeholder="Pilih Ukuran Baju" valueField="key" displayField="key"
            :options="['S', 'M', 'L', 'XL', 'XXL', 'XXXL']" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Ukuran Celana<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.uk_celana" label="" placeholder="Tuliskan Ukuran Celana"
            :errorText="formErrors.uk_celana?'failed':''" @input="v=>values.uk_celana=v" :hints="formErrors.uk_celana"
            :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Ukuran Sepatu<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.uk_sepatu" label="" placeholder="Tuliskan Ukuran Sepatu"
            :errorText="formErrors.uk_sepatu?'failed':''" @input="v=>values.uk_sepatu=v" :hints="formErrors.uk_sepatu"
            :check="false" />
        </div>
      </div>
      <div>
      </div>

      <h2 class="font-bold text-[18px] ">Pembayaran</h2>
      <div>
      </div>
      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Periode Gaji<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.periode_gaji_id" @input="v=>values.periode_gaji_id=v"
            :errorText="formErrors.periode_gaji_id?'failed':''" :hints="formErrors.periode_gaji_id" label=""
            placeholder="Pilih Periode Gaji" valueField="id" displayField="value" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    join: true,
                    where: `this.group='PERIODE GAJI' AND this.is_active='true'`,
                  }
                }" :check="false" />
        </div>
      </div>
      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tipe Pembayaran<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tipe_id" @input="v=>values.tipe_id=v" :errorText="formErrors.tipe_id?'failed':''"
            :hints="formErrors.tipe_id" label="" placeholder="Pilih Tipe Pembayaran" valueField="id"
            displayField="value" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    join: true,
                    where: `this.group='TIPE PEMBAYARAN' AND this.is_active='true'`,
                  }
                }" :check="false" />
        </div>
      </div>

      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Metode Pembayaran<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.metode_id" @input="v=>values.metode_id=v" :errorText="formErrors.metode_id?'failed':''"
            :hints="formErrors.metode_id" label="" placeholder="Pilih Metode Pembayaran" valueField="id"
            displayField="value" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    join: true,
                    where: `this.group='METODE PEMBAYARAN' AND this.is_active='true'`,
                  }
                }" :check="false" />
        </div>
      </div>
      <div v-if="!isProfile">
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nama Bank<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.bank_id" @input="v=>values.bank_id=v" :errorText="formErrors.bank_id?'failed':''"
            :hints="formErrors.bank_id" label="" placeholder="Pilih Bank" valueField="id" displayField="value" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    join: true,
                    where: `this.group='BANK' AND this.is_active='true'`,
                  }
                }" :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nomor Rekening<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="values.no_rek" label="" placeholder="Tuliskan Nomor Rekening"
            :errorText="formErrors.no_rek?'failed':''" @input="v=>values.no_rek=v" :hints="formErrors.no_rek"
            :check="false" />
        </div>
      </div>
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Atas Nama<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="values.atas_nama_rek" label="" placeholder="Tuliskan Atas Nama Pemilik Rekening"
            :errorText="formErrors.atas_nama_rek?'failed':''" @input="v=>values.atas_nama_rek=v"
            :hints="formErrors.atas_nama_rek" :check="false" />
        </div>
      </div>
    </div>

    <!-- Form Kontrak -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 8">
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Unit<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesKontrak.m_dir_id" @input="v=>valuesKontrak.m_dir_id=v"
            :errorText="formErrors.m_dir_id?'failed':''"
            @update:valueFull="(objVal)=>{ $log('dir',objVal.nama); valuesKontrak.m_dir_nama = objVal.nama }" label=""
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
            :value="valuesKontrak.m_divisi_id" @input="v=>valuesKontrak.m_divisi_id=v"
            :errorText="formErrors.m_divisi_id?'failed':''"
            @update:valueFull="(dt)=>{ $log('jabatan',dt.nama); valuesKontrak.nameDiv = dt.nama }" label=""
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
            :value="valuesKontrak.tipe_karyawan_id" label="" placeholder="Tipe Karyawan"
            @input="v=>valuesKontrak.tipe_karyawan_id=v" :errorText="formErrorsPend.tipe_karyawan_id?'failed':''"
            @update:valueFull="(objVal)=>{ valuesKontrak.tipeKary = objVal.value }"
            :hints="formErrorsPend.tipe_karyawan_id" valueField="id" displayField="value" :api="{
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
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            label="" placeholder="Pilih Tahun Masuk" :value="valuesKontrak.status" @input="v=>valuesKontrak.status=v"
            :options="[{ label: 'Aktif', value: 1 },{ label: 'Non Aktif', value: 0 }]"
            :errorText="formErrorsPend.status?'failed':''" :hints="formErrorsPend.status" valueField="value"
            displayField="label" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Durasi Kontrak<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            label="" placeholder="Pilih Durasi Kontrak" :value="valuesKontrak.duration"
            @input="v=>valuesKontrak.duration=v"
            :options="[{label: '7 Hari', value: 7}, { label: '3 Bulan', value: 3 },{ label: '6 Bulan ', value: 6 },{ label: '12 Bulan ', value: 12 }]"
            :errorText="formErrorsPend.duration?'failed':''" :hints="formErrorsPend.duration" valueField="value"
            displayField="label" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tanggal Awal<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX type="date" :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="valuesKontrak.tgl_awal" label="" placeholder="Tuliskan Tanggal" @input="v=>valuesKontrak.tgl_awal=v"
            :check="false" :errorText="formErrorsPend.tgl_awal?'failed':''" :hints="formErrorsPend.tgl_awal" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tanggal Akhir<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX type="date" :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="valuesKontrak.tgl_akhir" label="" placeholder="Tuliskan Tanggal"
            @input="v=>valuesKontrak.tgl_akhir=v" :check="false" :errorText="formErrorsPend.tgl_akhir?'failed':''"
            :hints="formErrorsPend.tgl_akhir" />
        </div>
      </div>

      <div class="md:col-span-2">
        <button
          :disabled="!actionText && !isProfile ? true : false"
          @click="addKontrak"
          type="button"
          class="mr-[15px] mb-3 bg-[#005FBF] hover:bg-[#0055ab] text-white py-[9px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="plus" /> <span>Add to List</span>
        </button>
        <!-- <TableStatic customClass="h-50vh" ref="detail" :value="detailKont" :columns="[
        { headerName: 'No', cellRenderer: !actionText && !isProfile?null:'ButtonGrid', valueGetter:p=>p.node.rowIndex + 1, cellRendererParams: !actionText && !isProfile?null:{ showValue: true, icon: 'times', class: 'btn-text-danger', click:(app)=>{ if (app && app.params) { const row = app.params.node.data; swal.fire({ icon: 'warning', showDenyButton: true, text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?` }).then((res) => { if (res.isConfirmed) { detailKont = detailKont.filter((e) => e._id != app.params.node.data._id); app.params.api.applyTransaction({ remove: [app.params.node.data] }); } }); } } }, width: 60, sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['justify-center', 'bg-gray-50'] },
        { flex: 1, headerName: 'Unit', field: 'm_dir_nama', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Jabatan', field: 'nameDiv', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Status Karyawan', field: 'tipeKary', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Tanggal Mulai', field: 'tgl_awal', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Tanggal Selesai', field: 'tgl_akhir', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'] },
        { flex: 1, headerName: 'Status', field: 'status', sortable: false, resizable: true, filter: false, wrapText: true, cellClass: ['!border-gray-200', 'justify-center'], cellRenderer: (params) => { return params.value == 1 ? 'Aktif' : 'Non Aktif' } }
      ]">
          <template #header>
           
            <button
          :disabled="!actionText && !isProfile ? true : false"
          @click="detailPendidikan = []"
          type="button"
          class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="trash" /> <span>Remove</span>
        </button>
          </template>
        </TableStatic> -->

        <table class="w-full overflow-x-auto table-auto border border-[#CACACA] pt-4">
          <thead>
            <tr class="border">
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                No</td>
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Unit</td>
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Jabatan</td>
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Status Karyawan</td>
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Tanggal Mulai</td>
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Tanggal Selesai</td>
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Status</td>
              <td
                class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
              </td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in detailKont" :key="item.id" class="border-t">
              <td class="text-[12px] text-center border border-[#CACACA]">
                {{ i + 1 }}.
              </td>
              <td class="text-[12px] text-left border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" :value="item.m_dir_id"
                  @input="v=>item.m_dir_id=v" :errorText="formErrors.m_dir_id?'failed':''"
                  @update:valueFull="(objVal)=>{ $log('dir',objVal.nama); item.m_dir_nama = objVal.nama }" label=""
                  placeholder="Pilih Unit" :hints="formErrors.m_dir_id" :api="{
                    url: `${store.server.url_backend}/operation/m_dir`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: { simplest:true, where: `this.is_active = 'true'` }
                  }" valueField="id" displayField="nama" :check="false" />
              </td>
              <td class="text-[12px] text-left border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }"
                  class="col-span-12 !mt-0 w-full" :value="item.m_divisi_id" @input="v=>item.m_divisi_id=v"
                  :errorText="formErrors.m_divisi_id?'failed':''"
                  @update:valueFull="(dt)=>{ $log('jabatan',dt.nama); item.nameDiv = dt.nama }" label=""
                  placeholder="Pilih Jabatan" :hints="formErrors.m_divisi_id" :api="{
                    url: `${store.server.url_backend}/operation/m_divisi`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: { simplest:true, where: `this.is_active = 'true'` }
                  }" valueField="id" displayField="nama" :check="false" />
              </td>
              <td class="text-[12px] text-left border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }"
                  class="col-span-12 !mt-0 w-full" :value="item.tipe_karyawan_id" label="" placeholder="Tipe Karyawan"
                  @input="v=>item.tipe_karyawan_id=v" :errorText="formErrorsPend.tipe_karyawan_id?'failed':''"
                  @update:valueFull="(objVal)=>{ item.tipeKary = objVal.value }"
                  :hints="formErrorsPend.tipe_karyawan_id" valueField="id" displayField="value" :api="{
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
              </td>
              <td class="text-[12px] text-left w-[15%] border border-[#CACACA]">
                <FieldX type="date" :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
                  :value="item.tgl_awal" label="" placeholder="Tuliskan Tanggal" @input="v=>item.tgl_awal=v"
                  :check="false" :errorText="formErrorsPend.tgl_awal?'failed':''" :hints="formErrorsPend.tgl_awal" />
              </td>
              <td class="text-[12px] text-right border border-[#CACACA]">
                <FieldX type="date" :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
                  :value="item.tgl_akhir" label="" placeholder="Tuliskan Tanggal" @input="v=>item.tgl_akhir=v"
                  :check="false" :errorText="formErrorsPend.tgl_akhir?'failed':''" :hints="formErrorsPend.tgl_akhir" />
              </td>
              <td class="text-[12px] text-left border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" label=""
                  placeholder="Pilih Status" :value="item.status" @input="v=>item.status=v"
                  :options="[{ label: 'Aktif', value: true },{ label: 'Non Aktif', value: false }]"
                  :errorText="formErrorsPend.status?'failed':''" :hints="formErrorsPend.status" valueField="value"
                  displayField="label" :check="false" />
              </td>
              <td>
                <div class="flex justify-center">
                  <button type="button" @click="removeDetail(i)" :disabled="!actionText">
                      <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                      </svg>
                </button>
                </div>
              </td>
            </tr>
            <tr v-if="detailKont.length === 0" class="text-center">
              <td colspan="7" class="py-[20px]">
                No data to show
              </td>
            </tr>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Form Pendidikan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 1">
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tingkat<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.tingkat_id" @input="v=>valuesPendidikan.tingkat_id=v"
            @update:valueFull="(objVal)=>{ valuesPendidikan.tingkat = objVal.value }" label=""
            placeholder="Pilih Tingkat" :errorText="formErrorsPend.tingkat_id?'failed':''"
            :hints="formErrorsPend.tingkat_id" valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: { simplest:true, where:`this.group='PENDIDIKAN' AND this.is_active='true'` }
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tahun Masuk<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            label="" placeholder="Pilih Tahun Masuk" :value="valuesPendidikan.thn_masuk"
            @input="v=>valuesPendidikan.thn_masuk=v" :options="ArrTahun"
            :errorText="formErrorsPend.thn_masuk?'failed':''" :hints="formErrorsPend.thn_masuk" valueField="key"
            displayField="key" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nama Sekolah<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.nama_sekolah" @input="v=>valuesPendidikan.nama_sekolah=v" label=""
            placeholder="Tuliskan Nama Sekolah" :errorText="formErrorsPend.nama_sekolah?'failed':''"
            :hints="formErrorsPend.nama_sekolah" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Tahun Lulus<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.thn_lulus" @input="v=>valuesPendidikan.thn_lulus=v" :options="ArrTahun" label=""
            placeholder="Pilih Tahun Lulus" :errorText="formErrorsPend.thn_lulus?'failed':''"
            :hints="formErrorsPend.thn_lulus" valueField="key" displayField="key" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.kota_id" @input="v=>valuesPendidikan.kota_id=v" label="" placeholder="Pilih Kota"
            :errorText="formErrorsPend.kota_id?'failed':''" :hints="formErrorsPend.kota_id" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: { simplest:true, transform:false, join:true, where:`this.group='KOTA'`, paginate:1000 }
        }" valueField="id" displayField="value" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nilai<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.nilai" @input="v=>valuesPendidikan.nilai=v" label="" placeholder="Tuliskan Nilai"
            :errorText="formErrorsPend.nilai?'failed':''" :hints="formErrorsPend.nilai" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Jurusan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.jurusan" @input="v=>valuesPendidikan.jurusan=v" label=""
            placeholder="Tuliskan Jurusan" :errorText="formErrorsPend.jurusan?'failed':''"
            :hints="formErrorsPend.jurusan" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Pendidikan Terakhir<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <div class="col-span-12 flex items-center gap-6">
            <label class="flex items-center space-x-2">
          <input :disabled="!actionText && !isProfile" type="radio" value="1" v-model="valuesPendidikan.is_pend_terakhir" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"/>
          <span>Iya</span>
        </label>
            <label class="flex items-center space-x-2">
          <input :disabled="!actionText && !isProfile" type="radio" value="0" v-model="valuesPendidikan.is_pend_terakhir" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"/>
          <span>Tidak</span>
        </label>
          </div>
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Ijazah Terakhir<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <input
        :disabled="!actionText && !isProfile"
        ref="fileIjz"
        type="file"
        accept="application/pdf"
        class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
        :class="{'border-red-500': formErrorsPend.ijazah_foto}"
        @change="fileIjazah"
      >
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">No Ijazah<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.ijazah_no" @input="v=>valuesPendidikan.ijazah_no=v" label=""
            placeholder="Tuliskan No Ijazah" :errorText="formErrorsPend.ijazah_no?'failed':''"
            :hints="formErrorsPend.ijazah_no" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Catatan</label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="textarea" class="col-span-12 !mt-0 w-full"
            :value="valuesPendidikan.desc" @input="v=>valuesPendidikan.desc=v" label="" placeholder="Tuliskan Catatan"
            :errorText="formErrorsPend.desc?'failed':''" :hints="formErrorsPend.desc" :check="false" />
        </div>
      </div>

      <div class="md:col-span-2">
        <TableStatic customClass="h-50vh" ref="detail" :value="detailPendidikan" :columns="[
        { headerName:'No', valueGetter:p=>p.node.rowIndex + 1, width:60, cellRenderer:!actionText && !isProfile?null:'ButtonGrid',
          cellRendererParams:!actionText && !isProfile?null:{
            showValue:true, icon:'times', class:'btn-text-danger',
            click:(app)=>{ if(app && app.params){ const row=app.params.node.data; swal.fire({icon:'warning',showDenyButton:true,text:`Hapus Baris ${app.params.node.rowIndex-(-1)}?`}).then((res)=>{ if(res.isConfirmed){ detailPendidikan=detailPendidikan.filter((e)=>e._id!=row._id); app.params.api.applyTransaction({ remove:[row] }); } }); } }
          },
          sortable:false,resizable:true,filter:false,wrapText:true,cellClass:['justify-center','bg-gray-50']
        },
        { flex:1, headerName:'No Ijazah', field:'ijazah_no', cellClass:['!border-gray-200','justify-center'] },
        { flex:1, headerName:'Tingkat', field:'tingkat', cellClass:['!border-gray-200','justify-center'] },
        { flex:1, headerName:'Nama Sekolah', field:'nama_sekolah', cellClass:['!border-gray-200','justify-center'] },
        { flex:1, headerName:'Jurusan', field:'jurusan', cellClass:['!border-gray-200','justify-center'] },
        { flex:1, headerName:'Tahun Masuk', field:'thn_masuk', cellClass:['!border-gray-200','justify-center'] },
        { flex:1, headerName:'Nilai', field:'nilai', cellClass:['!border-gray-200','justify-center'] },
        { flex:1, headerName:'Pendidikan Terakhir', cellRenderer:(params)=>params.data.is_pend_terakhir==='1'?'Iya':'Tidak', cellClass:['!border-gray-200','justify-center'] },
        { flex:1, headerName:'Note', field:'desc', cellClass:['!border-gray-200','justify-center'] }
      ]">
          <template #header>
            <button :disabled="!actionText && !isProfile" @click="addPendidikan" type="button" class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
          <icon fa="plus" /> <span>Add to List</span>
        </button>
            <button :disabled="!actionText && !isProfile" @click="detailPendidikan = []" type="button" class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
          <icon fa="trash" /> <span>Remove</span>
        </button>
          </template>
        </TableStatic>
      </div>
    </div>


    <!-- Form Keluarga -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 2">
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Keluarga<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesKeluarga.keluarga_id" @input="v=>valuesKeluarga.keluarga_id=v"
            @update:valueFull="(objVal)=>{ valuesKeluarga.keluarga = objVal.value }" label=""
            placeholder="Pilih Keluarga" :errorText="formErrorsKel.keluarga_id?'failed':''"
            :hints="formErrorsKel.keluarga_id" valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          params: {
            simplest: true,
            transform: false,
            where: `this.group='HUBUNGAN KELUARGA' AND this.is_active='true'`,
            join: true,
            selectfield: 'this.id, this.code, this.value, this.is_active'
          }
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nama<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="valuesKeluarga.nama" @input="v=>valuesKeluarga.nama=v" label="" placeholder="Tuliskan Nama"
            :errorText="formErrorsKel.nama?'failed':''" :hints="formErrorsKel.nama" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Pendidikan Terakhir<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesKeluarga.pend_terakhir_id" @input="v=>valuesKeluarga.pend_terakhir_id=v"
            @update:valueFull="(objVal)=>{ valuesKeluarga.pendidikan = objVal.value }" label=""
            placeholder="Pilih Pendidikan Terakhir" :errorText="formErrorsKel.pend_terakhir_id?'failed':''"
            :hints="formErrorsKel.pend_terakhir_id" valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          params: {
            simplest: true,
            transform: false,
            where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
            join: true,
            selectfield: 'this.id, this.code, this.value, this.is_active'
          }
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Pekerjaan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesKeluarga.pekerjaan_id" @input="v=>valuesKeluarga.pekerjaan_id=v"
            @update:valueFull="(objVal)=>{ valuesKeluarga.pekerjaan = objVal.value }" label=""
            placeholder="Pilih Pekerjaan" :errorText="formErrorsKel.pekerjaan_id?'failed':''"
            :hints="formErrorsKel.pekerjaan_id" valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          params: {
            simplest: true,
            transform: false,
            where: `this.group='PEKERJAAN' AND this.is_active='true'`,
            join: true,
            selectfield: 'this.id, this.code, this.value, this.is_active'
          }
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Jenis Kelamin<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="valuesKeluarga.jk_id" @input="v=>valuesKeluarga.jk_id=v"
            @update:valueFull="(objVal)=>{ valuesKeluarga.jk = objVal.value }" label=""
            placeholder="Pilih Jenis Kelamin" :errorText="formErrorsKel.jk_id?'failed':''" :hints="formErrorsKel.jk_id"
            valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          params: {
            simplest: true,
            transform: false,
            where: `this.group='JENIS KELAMIN' AND this.is_active='true'`,
            join: true,
            selectfield: 'this.id, this.code, this.value, this.is_active'
          }
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Usia<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="col-span-12 !mt-0 w-full"
            :value="valuesKeluarga.usia" @input="v=>valuesKeluarga.usia=v" label="" placeholder="Tuliskan Usia"
            :errorText="formErrorsKel.usia?'failed':''" :hints="formErrorsKel.usia" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Catatan</label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="textarea" class="col-span-12 !mt-0 w-full"
            :value="valuesKeluarga.desc" @input="v=>valuesKeluarga.desc=v" label="" placeholder="Tuliskan Catatan"
            :errorText="formErrorsKel.desc?'failed':''" :hints="formErrorsKel.desc" :check="false" />
        </div>
      </div>

      <div class="md:col-span-2">
        <TableStatic customClass="h-50vh" :value="detailKeluarga" :columns="columnsKeluarga">
          <template #header>
            <button
          :disabled="!actionText && !isProfile"
          @click="addKeluarga"
          type="button"
          class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="plus" /> <span>Add to List</span>
        </button>
            <button
          :disabled="!actionText && !isProfile"
          @click="detailKeluarga = []"
          type="button"
          class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="trash" /> <span>Remove</span>
        </button>
          </template>
        </TableStatic>
      </div>
    </div>



    <!-- Form Pelatihan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 3">
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Nama Pelatihan<label class="text-red-500">*</label>
          </label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" label="" placeholder="Tuliskan Nama Pelatihan"
            class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.nama_pel"
            :errorText="formErrorsPel.nama_pel ? 'failed' : ''" @input="v => valuesPelatihan.nama_pel = v"
            :hints="formErrorsPel.nama_pel" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Tahun<label class="text-red-500">*</label>
          </label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable: false }" label=""
            placeholder="Pilih Tahun" class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.tahun"
            @input="v => valuesPelatihan.tahun = v" :options="ArrTahun" :errorText="formErrorsPel.tahun ? 'failed' : ''"
            :hints="formErrorsPel.tahun" valueField="key" displayField="key" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Nama Lembaga<label class="text-red-500">*</label>
          </label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" label="" placeholder="Tuliskan Nama Lembaga"
            class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.nama_lem"
            :errorText="formErrorsPel.nama_lem ? 'failed' : ''" @input="v => valuesPelatihan.nama_lem = v"
            :hints="formErrorsPel.nama_lem" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Kota<label class="text-red-500">*</label>
          </label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable: false }"
            class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.kota_id" @input="v => valuesPelatihan.kota_id = v"
            :errorText="formErrorsPel.kota_id ? 'failed' : ''"
            @update:valueFull="objVal => valuesPelatihan.kota = objVal.value" :hints="formErrorsPel.kota_id" label=""
            placeholder="Pilih Kota" valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
          params: {
            simplest: true,
            transform: false,
            join: true,
            where: `this.group='KOTA'`,
            paginate: 1000,
          },
        }" :check="false" />
        </div>
      </div>

      <div class="md:col-span-2">
        <TableStatic customClass="h-50vh" ref="detail" :value="detailPelatihan" :columns="[
        {
          headerName: 'No',
          cellRenderer: !actionText && !isProfile ? null : 'ButtonGrid',
          valueGetter: p => p.node.rowIndex + 1,
          cellRendererParams: !actionText && !isProfile ? null : {
            showValue: true,
            icon: 'times',
            class: 'btn-text-danger',
            click: (app) => {
              if (app && app.params) {
                swal.fire({
                  icon: 'warning',
                  showDenyButton: true,
                  text: `Hapus Baris ${app.params.node.rowIndex - (-1)}?`,
                }).then((res) => {
                  if (res.isConfirmed) {
                    app.params.api.applyTransaction({
                      remove: [app.params.node.data],
                    });
                    detailPelatihan = detailPelatihan.filter(
                      (e) => e._id != app.params.node.data._id
                    );
                  }
                });
              }
            },
          },
          width: 60,
          sortable: false,
          resizable: true,
          filter: false,
          cellClass: ['justify-center', 'bg-gray-50'],
        },
        {
          flex: 1,
          headerName: 'Nama Pelatihan',
          field: 'nama_pel',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
        {
          flex: 1,
          headerName: 'Nama Lembaga',
          field: 'nama_lem',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
        {
          flex: 1,
          headerName: 'Kota',
          field: 'kota',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
        {
          flex: 1,
          field: 'tahun',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
      ]">
          <template #header>
            <button
          :disabled="!actionText && !isProfile"
          @click="addPelatihan"
          type="button"
          class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="plus" />
          <span>Add to List</span>
        </button>
            <button
          :disabled="!actionText && !isProfile"
          @click="detailPelatihan = []"
          type="button"
          class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="trash" />
          <span>Remove</span>
        </button>
          </template>
        </TableStatic>
      </div>
    </div>


    <!-- Form Prestasi -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 4">
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Tingkat<span class="text-red-500">*</span>
      </label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable: false }" label=""
            placeholder="Pilih Tingkat" class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.tingkat_pres_id"
            @update:valueFull="(objVal) => { valuesPrestasi.tingkat = objVal.value }"
            @input="v => valuesPrestasi.tingkat_pres_id = v" :errorText="formErrorsPres.tingkat_pres_id ? 'failed' : ''"
            :hints="formErrorsPres.tingkat_pres_id" valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          params: {
            simplest: true,
            transform: false,
            join: true,
            where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
            paginate: 1000
          }
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Tahun<span class="text-red-500">*</span>
      </label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable: false }" label=""
            placeholder="Pilih Tahun" class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.tahun"
            @input="v => valuesPrestasi.tahun = v" :options="ArrTahun" :errorText="formErrorsPres.tahun ? 'failed' : ''"
            :hints="formErrorsPres.tahun" valueField="key" displayField="key" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Prestasi / Penghargaan<span class="text-red-500">*</span>
      </label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" label="" placeholder="Tuliskan Prestasi / Penghargaan"
            class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.nama_pres"
            @input="v => valuesPrestasi.nama_pres = v" :errorText="formErrorsPres.nama_pres ? 'failed' : ''"
            :hints="formErrorsPres.nama_pres" :check="false" />
        </div>
      </div>

      <div></div>

      <div class="md:col-span-2">
        <TableStatic customClass="h-50vh" ref="detail" :value="detailPrestasi" :columns="[
        {
          headerName: 'No',
          cellRenderer: !actionText && !isProfile ? null : 'ButtonGrid',
          valueGetter: p => p.node.rowIndex + 1,
          cellRendererParams: !actionText && !isProfile ? null : {
            showValue: true,
            icon: 'times',
            class: 'btn-text-danger',
            click: (app) => {
              if (app && app.params) {
                swal.fire({
                  icon: 'warning',
                  showDenyButton: true,
                  text: `Hapus Baris ${app.params.node.rowIndex + 1}?`
                }).then((res) => {
                  if (res.isConfirmed) {
                    app.params.api.applyTransaction({
                      remove: [app.params.node.data],
                    });
                    detailPrestasi = detailPrestasi.filter(
                      (e) => e._id != app.params.node.data._id
                    );
                  }
                });
              }
            },
          },
          width: 60,
          sortable: false,
          resizable: true,
          filter: false,
          cellClass: ['justify-center', 'bg-gray-50']
        },
        {
          flex: 1,
          headerName: 'Tingkat',
          field: 'tingkat',
          cellClass: ['!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          headerName: 'Prestasi / Penghargaan',
          field: 'nama_pres',
          cellClass: ['!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          headerName: 'Tahun',
          field: 'tahun',
          cellClass: ['!border-gray-200', 'justify-center']
        }
      ]">
          <template #header>
            <button
          :disabled="!actionText && !isProfile"
          @click="addPrestasi"
          type="button"
          class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="plus" />
          <span>Add to List</span>
        </button>
            <button
          :disabled="!actionText && !isProfile"
          @click="detailPrestasi = []"
          type="button"
          class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="trash" />
          <span>Remove</span>
        </button>
          </template>
        </TableStatic>
      </div>
    </div>


    <!-- Form Organisasi -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 5">
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Nama Organisasi<label class="text-red-500">*</label>
          </label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" label="" placeholder="Tuliskan Nama Organisasi"
            class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.nama"
            :errorText="formErrorsOrg.nama ? 'failed' : ''" @input="v => valuesOrganisasi.nama = v"
            :hints="formErrorsOrg.nama" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Tahun<label class="text-red-500">*</label>
          </label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable: false }" label=""
            placeholder="Pilih Tahun" class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.tahun"
            @input="v => valuesOrganisasi.tahun = v" :options="ArrTahun"
            :errorText="formErrorsOrg.tahun ? 'failed' : ''" :hints="formErrorsOrg.tahun" valueField="key"
            displayField="key" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">
        Jenis Organisasi<label class="text-red-500">*</label>
          </label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable: false }" label=""
            placeholder="Pilih Jenis Organisasi" class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.jenis_org_id"
            @input="v => valuesOrganisasi.jenis_org_id = v" :errorText="formErrorsOrg.jenis_org_id ? 'failed' : ''"
            @update:valueFull="objVal => valuesOrganisasi.jenis = objVal.value" :hints="formErrorsOrg.jenis_org_id"
            valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
          params: {
            simplest: true,
            transform: false,
            join: true,
            where: `this.group='JENIS ORGANISASI' AND this.is_active='true'`,
            paginate: 1000,
          },
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Kota<label class="text-red-500">*</label>
          </label>
          <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable: false }" label=""
            placeholder="Pilih Kota" class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.kota_id"
            @input="v => valuesOrganisasi.kota_id = v" :errorText="formErrorsOrg.kota_id ? 'failed' : ''"
            @update:valueFull="objVal => valuesOrganisasi.kota = objVal.value" :hints="formErrorsOrg.kota_id"
            valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
          params: {
            simplest: true,
            transform: false,
            join: true,
            where: `this.group='KOTA'`,
            paginate: 1000,
          },
        }" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Posisi<label class="text-red-500">*</label>
          </label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" class="col-span-12 !mt-0 w-full"
            :value="valuesOrganisasi.posisi" label="" placeholder="Tuliskan Posisi"
            :errorText="formErrorsOrg.posisi ? 'failed' : ''" @input="v => valuesOrganisasi.posisi = v"
            :hints="formErrorsOrg.posisi" :check="false" />
        </div>
      </div>

      <div class="md:col-span-2">
        <TableStatic customClass="h-50vh" ref="detail" :value="detailOrganisasi" :columns="[
        {
          headerName: 'No',
          cellRenderer: !actionText && !isProfile ? null : 'ButtonGrid',
          valueGetter: p => p.node.rowIndex + 1,
          cellRendererParams: !actionText && !isProfile ? null : {
            showValue: true,
            icon: 'times',
            class: 'btn-text-danger',
            click: app => {
              if (app && app.params) {
                const row = app.params.node.data
                swal.fire({
                  icon: 'warning',
                  showDenyButton: true,
                  text: `Hapus Baris ${app.params.node.rowIndex - (-1)}?`,
                }).then(res => {
                  if (res.isConfirmed) {
                    detailOrganisasi = detailOrganisasi.filter(e => e._id != app.params.node.data._id)
                    app.params.api.applyTransaction({ remove: [app.params.node.data] })
                  }
                })
              }
            },
          },
          width: 60,
          sortable: false,
          resizable: true,
          filter: false,
          cellClass: ['justify-center', 'bg-gray-50'],
        },
        {
          flex: 1,
          headerName: 'Nama Organisasi',
          field: 'nama',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
        {
          flex: 1,
          headerName: 'Jenis Organisasi',
          field: 'jenis',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
        {
          flex: 1,
          field: 'posisi',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
        {
          flex: 1,
          field: 'tahun',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
        {
          flex: 1,
          headerName: 'Kota',
          field: 'kota',
          cellClass: ['!border-gray-200', 'justify-center'],
        },
      ]">
          <template #header>
            <button
          :disabled="!actionText && !isProfile"
          @click="addOrganisasi"
          type="button"
          class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="plus" />
          <span>Add to List</span>
        </button>
            <button
          :disabled="!actionText && !isProfile"
          @click="detailOrganisasi = []"
          type="button"
          class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded"
        >
          <icon fa="trash" />
          <span>Remove</span>
        </button>
          </template>
        </TableStatic>
      </div>
    </div>


    <!-- Form Bahasa -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 6">
      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Bahasa yang Dikuasai<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" label="" placeholder="Tuliskan Bahasa Yang Dikuasai"
            class="col-span-12 !mt-0 w-full" :value="valuesBahasa.bhs_dikuasai"
            :errorText="formErrorsBhs.bhs_dikuasai?'failed':''" @input="v=>valuesBahasa.bhs_dikuasai=v"
            :hints="formErrorsBhs.bhs_dikuasai" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nilai Lisan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" label="" placeholder="Contoh: 89"
            class="col-span-12 !mt-0 w-full" :value="valuesBahasa.nilai_lisan"
            :errorText="formErrorsBhs.nilai_lisan?'failed':''" @input="v=>valuesBahasa.nilai_lisan=v"
            :hints="formErrorsBhs.nilai_lisan" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Level Lisan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" label="" placeholder="Contoh: 3-Intermediate"
            class="col-span-12 !mt-0 w-full" :value="valuesBahasa.level_lisan"
            :errorText="formErrorsBhs.level_lisan?'failed':''" @input="v=>valuesBahasa.level_lisan=v"
            :hints="formErrorsBhs.level_lisan" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Nilai Tertulis<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" label="" placeholder="Contoh: 89"
            class="col-span-12 !mt-0 w-full" :value="valuesBahasa.nilai_tertulis"
            :errorText="formErrorsBhs.nilai_tertulis?'failed':''" @input="v=>valuesBahasa.nilai_tertulis=v"
            :hints="formErrorsBhs.nilai_tertulis" :check="false" />
        </div>
      </div>

      <div>
        <div class="grid grid-cols-12 items-center">
          <label class="col-span-12">Level Tertulis<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText && !isProfile }" label="" placeholder="Contoh: 3-Intermediate"
            class="col-span-12 !mt-0 w-full" :value="valuesBahasa.level_tertulis"
            :errorText="formErrorsBhs.level_tertulis?'failed':''" @input="v=>valuesBahasa.level_tertulis=v"
            :hints="formErrorsBhs.level_tertulis" :check="false" />
        </div>
      </div>

      <div class="col-span-1 md:col-span-2">
        <div class="flex justify-end mb-3 gap-3">
          <button :disabled="!actionText && !isProfile" @click="addBahasa" type="button"
        class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-[10px] px-[20px] flex items-center justify-center space-x-2 rounded">
        <icon fa="plus" /> <span>Add to List</span>
      </button>
          <button :disabled="!actionText && !isProfile" @click="detailBahasa = []" type="button"
        class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[10px] px-[20px] flex items-center justify-center space-x-2 rounded">
        <icon fa="trash" /> <span>Remove</span>
      </button>
        </div>

        <TableStatic customClass="h-50vh" ref="detail" :value="detailBahasa" :columns="[{
              headerName: 'No',
              cellRenderer: !actionText && !isProfile?null:'ButtonGrid',
              valueGetter:p=>p.node.rowIndex + 1,
              cellRendererParams: !actionText && !isProfile?null:{
                showValue: true,
                icon: 'times',
                class: 'btn-text-danger',
                click:(app)=>{
                  if (app && app.params) {
                    swal.fire({
                      icon: 'warning', showDenyButton: true,
                      text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                    }).then((res) => {
                      if (res.isConfirmed) {
                        detailBahasa = detailBahasa.filter((e) => e._id != app.params.node.data._id)
                        app.params.api.applyTransaction({ remove: [app.params.node.data] })
                      }
                    })
                  }
                }
              },
              width: 60,
              sortable: false, resizable: true, filter: false,
              cellClass: ['justify-center', 'bg-gray-50']
            },
            {
              flex: 1,
              headerName: 'Bahasa',
              field: 'bhs_dikuasai',
              sortable: false, filter: false, resizable: true,
              cellClass: ['!border-gray-200', 'justify-center'],
            },
            {
              flex: 1,
              headerName: 'Nilai Lisan',
              field: 'nilai_lisan',
              sortable: false, filter: false, resizable: true,
              cellClass: ['!border-gray-200', 'justify-center'],
            },
            {
              flex: 1,
              headerName: 'Level Lisan',
              field: 'level_lisan',
              sortable: false, filter: false, resizable: true,
              cellClass: ['!border-gray-200', 'justify-center'],
            },
            {
              flex: 1,
              headerName: 'Nilai Tertulis',
              field: 'nilai_tertulis',
              sortable: false, filter: false, resizable: true,
              cellClass: ['!border-gray-200', 'justify-center'],
            },
            {
              flex: 1,
              headerName: 'Level Tertulis',
              field: 'level_tertulis',
              sortable: false, filter: false, resizable: true,
              cellClass: ['!border-gray-200', 'justify-center'],
            }]" />
      </div>
    </div>


    <!-- Form Pengalaman Kerja -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mt-9" v-if="activeTabIndex === 7">
      <div>
        <label class="font-semibold">Nama Perusahaan<span class="text-red-500">*</span></label>
        <FieldX :bind="{ readonly: !actionText && !isProfile }" class="w-full" :value="valuesPengalaman.instansi"
          @input="v=>valuesPengalaman.instansi=v" :errorText="formErrorsPK.instansi?'failed':''"
          :hints="formErrorsPK.instansi" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Bidang Usaha<span class="text-red-500">*</span></label>
        <FieldX :bind="{ readonly: !actionText && !isProfile }" class="w-full" :value="valuesPengalaman.bidang_usaha"
          @input="v=>valuesPengalaman.bidang_usaha=v" :errorText="formErrorsPK.bidang_usaha?'failed':''"
          :hints="formErrorsPK.bidang_usaha" :check="false" />
      </div>

      <div>
        <label class="font-semibold">No. Telp<span class="text-red-500">*</span></label>
        <FieldX :bind="{ readonly: !actionText && !isProfile }" type="number" class="w-full"
          :value="valuesPengalaman.no_tlp" @input="v=>valuesPengalaman.no_tlp=v"
          :errorText="formErrorsPK.no_tlp?'failed':''" :hints="formErrorsPK.no_tlp" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Posisi<span class="text-red-500">*</span></label>
        <FieldX :bind="{ readonly: !actionText && !isProfile }" class="w-full" :value="valuesPengalaman.posisi"
          @input="v=>valuesPengalaman.posisi=v" :errorText="formErrorsPK.posisi?'failed':''"
          :hints="formErrorsPK.posisi" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Tahun Masuk<span class="text-red-500">*</span></label>
        <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="w-full"
          :value="valuesPengalaman.thn_masuk" @input="v=>valuesPengalaman.thn_masuk=v" :options="ArrTahun"
          :errorText="formErrorsPK.thn_masuk?'failed':''" :hints="formErrorsPK.thn_masuk" valueField="key"
          displayField="key" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Tahun Keluar<span class="text-red-500">*</span></label>
        <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="w-full"
          :value="valuesPengalaman.thn_keluar" @input="v=>valuesPengalaman.thn_keluar=v" :options="ArrTahun"
          :errorText="formErrorsPK.thn_keluar?'failed':''" :hints="formErrorsPK.thn_keluar" valueField="key"
          displayField="key" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Alamat Kantor<span class="text-red-500">*</span></label>
        <FieldX :bind="{ readonly: !actionText && !isProfile }" type="textarea" class="w-full"
          :value="valuesPengalaman.alamat_kantor" @input="v=>valuesPengalaman.alamat_kantor=v"
          :errorText="formErrorsPK.alamat_kantor?'failed':''" :hints="formErrorsPK.alamat_kantor" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Kota<span class="text-red-500">*</span></label>
        <FieldSelect :bind="{ disabled: !actionText && !isProfile, clearable:false }" class="w-full"
          :value="valuesPengalaman.kota_id" @input="v=>valuesPengalaman.kota_id=v"
          :errorText="formErrorsPK.kota_id?'failed':''" :hints="formErrorsPK.kota_id" valueField="id"
          displayField="value" :api="{
        url: `${store.server.url_backend}/operation/m_general`,
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
          simplest: true,
          transform: false,
          join: true,
          where: `this.group='KOTA'`,
          paginate: 1000
        }
      }" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Surat Referensi<span class="text-red-500">*</span></label>
        <input
      :disabled="!actionText && !isProfile"
      ref="fileSurat"
      type="file"
      accept="application/pdf"
      class="w-full border rounded text-sm py-2 px-3"
      :class="{'border-red-500': formErrorsPK.surat_referensi}"
      @change="fileSrtRef"
      @input="(v)=>valuesPengalaman.surat_referensi=v"
    />
      </div>

      <div class="md:col-span-2">
        <TableStatic customClass="h-50vh" ref="detail" :value="detailPengalaman" :columns="[
      {
        headerName: 'No',
        cellRenderer: !actionText && !isProfile?null:'ButtonGrid',
        valueGetter:p=>p.node.rowIndex + 1,
        cellRendererParams: !actionText && !isProfile?null:{
          showValue: true,
          icon: 'times',
          class: 'btn-text-danger',
          click:(app)=>{
            if (app && app.params) {
              const row = app.params.node.data
              swal.fire({
                icon: 'warning', showDenyButton: true,
                text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
              }).then((res) => {
                if (res.isConfirmed) {
                  detailPengalaman = detailPengalaman.filter((e) => e._id != app.params.node.data._id)
                  app.params.api.applyTransaction({ remove: [app.params.node.data] })
                }
              })
            }
          }
        },
        width: 60,
        sortable: false, resizable: true, filter: false,
        cellClass: ['justify-center', 'bg-gray-50']
      },
      { flex: 1, headerName: 'Nama Instansi', field: 'instansi', cellClass: ['!border-gray-200','justify-center'] },
      { flex: 1, headerName: 'Bidang Usaha', field: 'bidang_usaha', cellClass: ['!border-gray-200','justify-center'] },
      { flex: 1, field: 'posisi', cellClass: ['!border-gray-200','justify-center'] },
      { flex: 1, headerName: 'Tahun Masuk', field: 'thn_masuk', cellClass: ['!border-gray-200','justify-center'] },
      { flex: 1, headerName: 'Tahun Keluar', field: 'thn_keluar', cellClass: ['!border-gray-200','justify-center'] },
      { flex: 1, headerName: 'Alamat Kantor', field: 'alamat_kantor', cellClass: ['!border-gray-200','justify-center'] }
    ]">
          <template #header>
            <div class="flex gap-3 justify-end">
              <button
            :disabled="!actionText && !isProfile"
            @click="addPengalaman"
            type="button"
            class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-2 px-4 rounded flex items-center gap-2"
          >
            <icon fa="plus" /> <span>Add to List</span>
          </button>
              <button
            :disabled="!actionText && !isProfile"
            @click="detailPengalaman = []"
            type="button"
            class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-2 px-4 rounded flex items-center gap-2"
          >
            <icon fa="trash" /> <span>Remove</span>
          </button>
            </div>
          </template>
        </TableStatic>
      </div>
    </div>

    <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">
      <button @click="onBack" v-if="!isProfile" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Batal
          </button>
      <button v-show="actionText || isProfile" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Simpan
          </button>
    </div>
    <!-- FORM END -->
  </div>
</div>

</div>
@endverbatim
@endif