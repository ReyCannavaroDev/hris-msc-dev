@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl h-[570px]">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <RouterLink v-if="currentMenu?.can_create||true||store.user.data.username==='developer'"
        :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="bg-blue-500 text-white hover:bg-blue-600 rounded-[6px] py-2 px-[12.5px]">
        <icon fa="bolt" />Generate Gaji
      </RouterLink>
    </template>
  </TableApi>
</div>
@else

@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Perhitungan Gaji
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-2">
        <div>
          <label class="font-semibold">Periode Awal<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldX :bind="{ readonly: !actionText , required: true}" class="w-full py-2 !mt-0"
            :value="values.periode_awal" :errorText="formErrors.periode_awal?'failed':''"
            :hints="formErrors.periode_awal" :check="false" type="date" label="" placeholder="YYYY-MM-DD" @input="(v)=>{
                //$log(v)
                values.periode_awal=v
                detailArr = []
                //$log(values.divisi)
              }" />
        </div>
        <div>
          <label class="font-semibold">Periode Akhir<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldX :bind="{ readonly: !actionText , required: true}" class="w-full py-2 !mt-0"
            :value="values.periode_akhir" :errorText="formErrors.periode_akhir?'failed':''"
            :hints="formErrors.periode_akhir" :check="false" type="date" label="" placeholder="YYYY-MM-DD" @input="(v)=>{
                //$log(v)
                values.periode_akhir=v
                detailArr = []
                //$log(values.divisi)
              }" />
        </div>

        <div>
          <label class="font-semibold">Unit<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:true }"
            :value="values.m_dir_id" :check="false" @input="(v)=>{
              //$log(v)
              values.m_dir_id=v
              detailArr = []
              //$log(values.departemen)
            }" :errorText="formErrors.m_dir_id?'failed':''" :hints="formErrors.m_dir_id" displayField="nama"
            valueField="id" :api="{
                url: `${store.server.url_backend}/operation/m_dir`,
                headers: {
                  //'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest:true,
                  single:true,
                  transform:false,
                }
            }" fa-icon="search" :check="true" />
        </div>

        <div>
          <label class="font-semibold">Jabatan<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:true }"
            :value="values.divisi" :check="false" @input="(v)=>{
              //$log(v)
              values.divisi=v
              values.m_divisi_id=v
              values.m_dept_id=''
              detailArr = []
              //$log(values.divisi)
            }" :errorText="formErrors.divisi?'failed':''" :hints="formErrors.divisi" displayField="nama"
            valueField="id" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: {
                  //'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest:true,
                  single:true,
                  where:`this.is_active='true'`,
                  transform:false,
                }
            }" fa-icon="search" :check="true" />
        </div>

        <div>
          <label class="font-semibold">Karyawan</label>
          <FieldPopup class="w-full py-2 !mt-0" :value="values.m_kary_id" @input="(v)=>values.m_kary_id=v"
            :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id" valueField="id"
            displayField="nama_lengkap" @update:valueFull="(dt) => {
              $log('dt',dt)
                if (dt) {
                  values.total_bulan = dt.total_bulan
                  values.kary_nama = dt.nama_lengkap || ''
                  values.kary_nik = dt.nik || ''
                }
              }" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: (() => {
                  let where = null

                  if (values.m_dir_id && values.m_divisi_id) {
                    where = `m_kary.m_dir_id = ${values.m_dir_id} AND m_kary.m_divisi_id = ${values.m_divisi_id}`
                  } else if (values.m_dir_id) {
                    where = `m_kary.m_dir_id = ${values.m_dir_id}`
                  } else if (values.m_divisi_id) {
                    where = `m_kary.m_divisi_id = ${values.m_divisi_id}`
                  }

                  return {
                    where,
                    scopes: 'landing',  
                    simplest: true,
                    searchfield:
                      'id, nama_lengkap, nik, m_divisi.nama, m_zona.nama, m_dir.nama'
                  }
                })()
              }" placeholder="Pilih Karyawan" label="" :check="false" :columns="[
              {
                headerName: 'No',
                valueGetter: (p) => p.node.rowIndex + 1,
                width: 60,
                sortable: false, 
                resizable: false, 
                filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'kode',
                headerName: 'ID Karyawan',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'nama_lengkap',
                headerName: 'Nama',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },         
              {
                flex: 1,
                field: 'm_dir.nama',
                headerName: 'Unit',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_divisi.nama',
                headerName: 'Jabatan',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },                                           
            ]" />
        </div>

      </div>

      <div class="flex flex-row justify-center space-x-[10px] mt-[1em]">
        <button @click="generatePerhitungan" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-[18px] py-[8px] rounded-[4px] ">
          <Icon fa="bolt"/> Generate
        </button>
        <button @click="detailArr = []" class="bg-[#EF4444] hover:bg-[#ed3232] text-white text-sm px-[18px] py-[8px] rounded-[4px] ">
          Hapus Detail
        </button>
      </div>

      <div class="mt-4">
        <table class="w-full overflow-x-auto table-auto border border-[#CACACA] " style="zoom: 80%">
          <thead>
            <tr class="border">
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                No.</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                Periode</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">
                ID Karyawan</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[20%] border bg-[#f8f8f8] border-[#CACACA]">
                Karyawan</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">
                Cabang</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">
                Jabatan</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
                Gaji Bersih</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
                Deskripsi</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                Aksi</td>
            </tr>
          </thead>
          <tbody>
            <tr v-if="detailArr.length" v-for="(item, i) in detailArr" :key="item.id"
              class="border-t hover:bg-yellow-200">
              <td class="text-center border border-[#CACACA] px-2">
                {{ i + 1 }}.
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item.periode }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item['m_kary.nik'] }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item.karyawan }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item['m_kary_dir.nama'] }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item['m_kary_divisi.nama'] }}
              </td>
              <td class="text-right border border-[#CACACA] px-2">
                {{ formatRupiah(item.netto) }}
              </td>
              <td class="border border-[#CACACA]">
                <FieldX :bind="{ readonly: !actionText}" class="!mt-0" :value="item.deskripsi"
                  @input="v=>item.deskripsi=v" type="textarea" label="" :check="false" />
              </td>
              <td class="border border-[#CACACA]">
                <div class="flex justify-center">
                  <button v-show="actionText" @click="openDetail(i)" class="rounded-lg flex items-center justify-center">
                      <icon fa="circle-info" size="lg">
                    </button>
                </div>

              </td>
            </tr>
            <tr v-else class="text-center">
              <td colspan="7" class="py-[20px]">
                No data to show
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">
        <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
          Batal
        </button>
        <button v-show="actionText" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>
@endverbatim
@endif

@verbatim
<!-- Modal Content -->
<div v-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50 px-4">
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

  <div class="relative bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-100">
      <h3 class="text-xl font-semibold text-gray-800">Rincian Gaji {{ titleOpen }}</h3>
      <button @click="closeModal" class="text-gray-400 hover:text-gray-700 transition">✕</button>
    </div>

    <div class="p-6">
      <div class="overflow-hidden border border-gray-300 rounded-lg">
        <table class="w-full border-collapse text-sm">
          <thead class="bg-gray-800 text-white sticky top-0">
            <tr>
              <th class="p-3 text-left w-1/2">Komponen</th>
              <th class="p-3 text-center w-1/6">Faktor</th>
              <th class="p-3 text-right w-1/3">Besaran</th>
            </tr>
          </thead>
        </table>

        <div class="max-h-[300px] overflow-y-auto">
          <table class="w-full border-collapse text-sm">
            <tbody class="divide-y divide-gray-200">
              <tr v-for="(a,i) in detailArrOpen.items" :key="i"
                :class="a.factor == '=' ? 'bg-gray-100 font-semibold' : 'hover:bg-gray-50 transition'">
                <td class="p-3 text-gray-700 w-1/2">{{ a.label }}</td>
                <td class="p-3 text-center text-gray-600 w-1/6">{{ a.factor }}</td>
                <td class="p-3 text-right w-1/3">
                  <FieldNumber :bind="{ readonly: !actionText }" :value="a.value"
                    :errorText="formErrors.value?'failed':''" @input="v=>a.value=v" :hints="formErrors.value" label=""
                    :check="false" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <table class="w-full border-collapse text-sm">
          <tfoot>
            <tr class="bg-red-600 text-white font-bold">
              <td class="p-3 w-1/2">Total Potongan</td>
              <td class="p-3 w-1/6 text-center">-</td>
              <td class="p-3 text-right w-1/3">
                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR'
                }).format(detailArrOpen.totalPotongan) }}
              </td>
            </tr>

            <!-- <tr class="bg-green-600 text-white font-bold">
              <td class="p-3 w-1/2">Total Bonus</td>
              <td class="p-3 w-1/6 text-center">+</td>
              <td class="p-3 text-right w-1/3">
                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(detailArrOpen.totalBonus) }}
              </td>
            </tr> -->

            <tr class="bg-blue-700 text-white font-bold">
              <td class="p-3 w-1/2">Total Gaji</td>
              <td class="p-3 w-1/6 text-center">=</td>
              <td class="p-3 text-right w-1/3">
                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR'
                }).format(detailArrOpen.totalGaji) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="flex justify-end gap-2 px-6 py-4 border-t bg-gray-50">
      <button
        @click="closeModal"
        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
      >
        Tutup
      </button>
    </div>
  </div>
</div>




@endverbatim