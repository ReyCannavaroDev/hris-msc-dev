@verbatim
<div class="bg-white p-6 rounded-md text-sm">
  <div class="grid grid-cols-4 gap-3 mb-4">
    <div>
      <label class="font-semibold block mb-1 text-gray-700">Periode</label>
      <FieldX :bind="{ readonly: false , required: true}" class="w-full py-2 !mt-0"
        :value="headerValues.month" :check="false" type="month" placeholder="Periode" @input="(v)=>{
          headerValues.month = v
        }" />
    </div>

    <div>
      <label class="font-semibold block mb-1 text-gray-700">Unit / Direktorat</label>
      <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable:true }" :value="headerValues.dir_id"
        :check="false" @input="(v)=>{
          headerValues.dir_id = v
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

    <div>
      <label class="font-semibold block mb-1 text-gray-700">Jabatan / Divisi</label>
      <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable:true }" :value="headerValues.divisi_id"
        :check="false" @input="(v)=>{
          headerValues.divisi_id = v
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

    <div>
      <label class="font-semibold block mb-1 text-gray-700">Karyawan</label>
      <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: false, clearable:true }" :value="headerValues.m_kary_id"
        :check="false" @input="(v)=>{
          headerValues.m_kary_id = v
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

  <div class="h-[550px] mt-4">
    <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
      <!-- Empty slot header to prevent default add/download buttons -->
      <template #header>
        <div class="flex gap-x-2"></div>
      </template>
    </TableApi>
  </div>
</div>
@endverbatim
