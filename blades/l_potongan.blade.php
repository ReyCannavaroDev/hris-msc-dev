@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Laporan Potongan
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Final Gaji</label>
          <FieldPopup :value="values.f_id" :errorText="formErrors.f_id ? 'failed' : ''" @input="v => values.f_id = v"
            :hints="formErrors.f_id" class="w-full py-2 !mt-0" valueField="id" displayField="desc" :api="{
                url: `${store.server.url_backend}/operation/t_final_gaji`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  //where: `this.m_posisi_id=${values.m_posisi_id ?? 0}`,
                  //searchfield: 'this.nik, this.nama_lengkap, this.nama_depan, this.nama_belakang, m_zona.nama, m_dir.nama, m_divisi.nama, m_dept.nama'
                }
              }" placeholder="Cari Final Gaji" label="" :check="false" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'nomor',
                headerName: 'Nomor',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'desc',
                headerName: 'Deskripsi',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              ]" />
        </div>

       <div>
          <label class="font-semibold">Status Karyawan</label>
          <FieldSelect 
            :bind="{ readonly: !actionText }" 
            class="w-full py-2 !mt-0" 
            
            :value="values.is_active" 
            @input="v => values.is_active = v"
            
            :errorText="formErrors.is_active ? 'failed' : ''" 
            :hints="formErrors.is_active" 
            :check="false" 
            label="" 
            placeholder="Pilih Status" 
            valueField="id"
            displayField="nama" 
            
            :options="[
              { id: null, nama: 'Tampilkan Semua' },
              { id: 'true', nama: 'Aktif' },
              { id: 'false', nama: 'Nonaktif' }
            ]" 
          />
      </div>

      </div>
      <!-- END COLUMN -->
      <div class="flex flex-row justify-end space-x-[20px] mt-[1em]">
        <button @click="onGenerate"
    class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] ">
    Download
  </button>
      </div>

      <!-- ACTION BUTTON START -->
      <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
        <hr>
        <div id="exportTable">
        </div>
      </div>
    </div>
  </div>
</div>
</div>
@endverbatim