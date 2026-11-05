@verbatim
<div class="flex flex-col rounded-xl shadow-sm bg-white border border-gray-200 overflow-hidden">
  <div class="px-6 py-4 border-b bg-gradient-to-r from-gray-50 to-gray-100">
    <h1 class="text-lg font-semibold text-gray-800">List Kontrak Karyawan</h1>
  </div>

  <div class="flex items-center gap-3 px-6 pt-4">
    <label class="text-sm text-gray-600 font-medium whitespace-nowrap">Periode:</label>
    <div class="-mt-5 flex-1 max-w-[30%]">
      <FieldX type="month"
        class="rounded-md text-sm w-full px-2 py-1.5 focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition"
        :value="values.periode" @input="v => values.periode = v" />
    </div>
  </div>


  <div class="px-5 mt-3 w-full bg-white">
    <div class="border border-gray-200 rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full table-fixed border-collapse">
          <thead class="bg-gray-100 text-gray-700 text-sm sticky top-0 z-10">
            <tr>
              <th class="p-3 text-center font-medium border-b border-gray-200 w-[5%]">No</th>
              <th class="p-3 text-left font-medium border-b border-gray-200 w-[45%]">Nama Karyawan</th>
              <th class="p-3 text-center font-medium border-b border-gray-200 w-[25%]">Tanggal Start</th>
              <th class="p-3 text-center font-medium border-b border-gray-200 w-[25%]">Tanggal End</th>
            </tr>
          </thead>
        </table>

        <div class="max-h-[300px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
          <table class="w-full table-fixed border-collapse">
            <tbody>
              <tr v-for="(item, i) in karyawan" :key="i" v-if="karyawan.length > 0" @click="selectKaryawan(item)"
                class="hover:bg-blue-50 border-b border-gray-100 transition cursor-pointer">
                <td class="p-3 text-xs text-center text-gray-700 w-[5%]">{{ i + 1 }}</td>
                <td class="p-3 text-xs text-gray-700 w-[45%]">{{ item.nama ?? '-' }}</td>
                <td class="p-3 text-xs text-center text-gray-700 w-[25%]">{{ item.tgl_start ?? '-' }}</td>
                <td class="p-3 text-xs text-center text-gray-700 w-[25%]">{{ item.tgl_end ?? '-' }}</td>
              </tr>

              <tr v-else>
                <td colspan="4" class="text-center py-6 text-gray-400 text-sm">
                  No data to show
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

  <div class="px-6 pt-6">
    <h1 class="text-base font-semibold text-gray-800 mb-3">
      Detail Kontrak {{ values.nama_karyawan ? values.nama_karyawan : '' }}
    </h1>


    <div class="overflow-auto rounded-md border border-gray-200 shadow-sm">
      <table class="min-w-full table-auto text-sm">
        <thead class="bg-gray-100 text-gray-600 text-xs font-medium">
          <tr>
            <th class="p-2 border-b border-gray-200 w-[5%] text-center">No</th>
            <th class="p-2 border-b border-gray-200 text-center">Unit</th>
            <th class="p-2 border-b border-gray-200 text-center">Jabatan</th>
            <th class="p-2 border-b border-gray-200 text-center">Status Karyawan</th>
            <th class="p-2 border-b border-gray-200 text-center">Tanggal Mulai</th>
            <th class="p-2 border-b border-gray-200 text-center">Tanggal Selesai</th>
            <th class="p-2 border-b border-gray-200 text-center">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in detailArr" :key="item.id" v-if="detailArr.length > 0"
            class="border-b border-gray-100 hover:bg-gray-50 transition">
            <td class="text-xs text-center p-2 text-gray-700">{{ i + 1 }}.</td>
            <td class="text-xs text-center p-2 text-gray-700">
              <FieldSelect
                :bind="{ disabled: !actionText, clearable:false }"
                :value="item.m_dir_id" @input="v=>item.m_dir_id=v"
                :errorText="formErrors.m_dir_id?'failed':''" 
                :hints="formErrors.m_dir_id"
                valueField="id" displayField="nama"
                :api="{
                    url: `${store.server.url_backend}/operation/m_dir`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      simplest:true,
                      transform:false,
                      join:false
                    }
                }"
                placeholder="" label="" fa-icon="" :check="false"
              />
              
            </td>
            <td class="text-xs text-center p-2 text-gray-700">{{ item.m_dir_id ?? '-' }}</td>
            <td class="text-xs text-center p-2 text-gray-700">{{ item.tipe_karyawan_id ?? '-' }}</td>
            <td class="text-xs text-center p-2 text-gray-700">{{ item.tgl_awal ?? '-' }}</td>
            <td class="text-xs text-center p-2 text-gray-700">{{ item.tgl_akhir ?? '-' }}</td>
            <td class="text-xs text-center p-2 text-gray-700">
              {{ item.status == 1 ? 'Aktif' : 'Non Aktif' }}
            </td>
          </tr>
          <tr v-else>
            <td colspan="7" class="text-center py-5 text-gray-400 text-sm">No data to show</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>


  <div v-show="route.query.is_approval" class="px-6 py-6 grid grid-cols-2 gap-6 text-sm">
    <table class="w-full border border-gray-200 rounded-md overflow-hidden text-gray-700">
      <tr class="bg-gray-50 border-b">
        <td class="px-3 py-2 font-semibold w-1/3">Nomor</td>
        <td class="px-3 py-2">{{ values.approval?.nomor ?? '-' }}</td>
      </tr>
      <tr class="border-b">
        <td class="px-3 py-2 font-semibold">Tanggal</td>
        <td class="px-3 py-2">{{ values.approval?.created_at ?? '-' }}</td>
      </tr>
      <tr class="border-b">
        <td class="px-3 py-2 font-semibold">Pemohon</td>
        <td class="px-3 py-2">{{ values.approval?.creator ?? '-' }}</td>
      </tr>
      <tr>
        <td class="px-3 py-2 font-semibold">Status</td>
        <td class="px-3 py-2">{{ values.approval?.status ?? '-' }}</td>
      </tr>
    </table>
  </div>

  <hr class="border-gray-200 my-4">

  <div class="px-6 text-xs text-gray-500 space-y-1">
    <p v-if="values.created_id">Created by {{values.created_id}} on {{values.created_at}}</p>
    <p v-if="values.edited_id">Last edited by {{values.edited_id}} on {{values.edited_at}}</p>
  </div>

  <div class="flex justify-end items-center gap-2 px-6 py-4 border-t bg-gray-50">
    <button @click="onPost" v-show="values['status.key1']=='DRAFT' && values.id && actionText" class="bg-orange-600 text-white font-medium px-4 py-2 rounded-md hover:bg-orange-500 transition">
      <icon fa="location-arrow" /> Post
    </button>
    <button v-show="route.query.is_approval" @click="onProcess('APPROVE')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-500">Approve</button>
    <button v-show="route.query.is_approval" @click="onProcess('REJECT')" class="bg-rose-600 text-white px-4 py-2 rounded-md hover:bg-rose-500">Reject</button>
    <button v-show="route.query.is_approval" @click="onProcess('REVISE')" class="bg-amber-500 text-white px-4 py-2 rounded-md hover:bg-amber-400">Revise</button>
    <button @click="onBack(true)" v-show="actionText" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-500">
      <icon fa="times" /> Cancel
    </button>
    <button @click="onSave" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-500">
      <icon fa="save" /> Simpan
    </button>
  </div>
</div>
@endverbatim