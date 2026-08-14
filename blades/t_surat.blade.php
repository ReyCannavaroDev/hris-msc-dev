<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="max-h-[450px]">
  </TableApi>
</div>
@else
<!-- CONTENT -->
@verbatim
<div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Surat Menyurat</h1>
        <p class="text-gray-100">Pembuatan Surat & Tanda Tangan Digital</p>
      </div>
    </div>
  </div>
  
  <div class="p-4 grid <md:grid-cols-1 grid-cols-2 gap-4">
    <!-- Fields for HR -->
    <div v-show="!isSigning">
      <FieldPopup
          :bind="{ readonly: !actionText || isSigning }" class="w-full mt-3"
          :value="values.m_karyawan_id" @input="(v)=>values.m_karyawan_id=v"
          :errorText="formErrors.m_karyawan_id?'failed':''" 
          :hints="formErrors.m_karyawan_id" 
          valueField="id" displayField="nik"
          :api="{
            url: `${store.server.url_backend}/operation/m_kary`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              simplest:true,
              searchfield: 'this.nik, m_dir.nama, m_divisi.nama, this.nama_lengkap'
            }
          }"
          placeholder="Cari Nomor Induk Karyawan" label="Nomer Induk Karyawan" :check="false" 
          :columns="[{
            headerName: 'No', valueGetter:(p)=>p.node.rowIndex + 1, width: 60
          },
          { flex: 1, field: 'nik', headerName: 'NIK' },
          { flex: 1, field: 'nama_lengkap', headerName: 'Nama' }
          ]"
        />
    </div>

    <div v-show="!isSigning">
        <FieldSelect
          :bind="{ disabled: !actionText || isSigning, clearable:false }" class="w-full mt-3"
          :value="values.jenis_surat" @input="v=>values.jenis_surat=v"
          :errorText="formErrors.jenis_surat?'failed':''" 
          label="Jenis Surat" placeholder="Pilih Jenis"
          :hints="formErrors.jenis_surat"
          :options="[{label:'Surat Peringatan', value:'PERINGATAN'}, {label:'Surat Penghargaan', value:'PENGHARGAAN'}, {label:'Surat Skorsing', value:'SKORSING'}]"
          valueField="value" displayField="label" :check="false"
        />
    </div>

    <div v-show="!isSigning && values.jenis_surat == 'PERINGATAN'">
        <FieldSelect
          :bind="{ disabled: !actionText || isSigning, clearable:true }" class="w-full mt-3"
          :value="values.level_surat" @input="v=>values.level_surat=v"
          :errorText="formErrors.level_surat?'failed':''" 
          label="Level SP" placeholder="Pilih Level"
          :hints="formErrors.level_surat"
          :options="[{label:'SP 1', value:'SP 1'}, {label:'SP 2', value:'SP 2'}, {label:'SP 3', value:'SP 3'}]"
          valueField="value" displayField="label" :check="false"
        />
    </div>

    <div v-show="!isSigning">
        <FieldX :bind="{ readonly: !actionText || isSigning }" type="date" class="w-full mt-3"
          :value="values.tanggal_terbit" :errorText="formErrors.tanggal_terbit?'failed':''"
          @input="v=>values.tanggal_terbit=v" :hints="formErrors.tanggal_terbit" :check="false"
          label="Tanggal Terbit" placeholder="Pilih Tanggal"
        />
    </div>

    <div class="col-span-2" v-show="!isSigning">
        <FieldX :bind="{ readonly: !actionText || isSigning }" type="textarea" class="w-full mt-3"
          :value="values.alasan" :errorText="formErrors.alasan?'failed':''"
          @input="v=>values.alasan=v" :hints="formErrors.alasan" :check="false"
          label="Alasan / Keterangan" placeholder="Keterangan Surat"
        />
    </div>

    <div class="col-span-2" v-show="!isSigning">
        <FieldUpload class="w-full mt-3" :bind="{ readonly: !actionText || isSigning }"
          :value="values.file_dokumen" @input="(v)=>values.file_dokumen=v" :maxSize="10"
          :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]"
          :api="{
            url: `${store.server.url_backend}/operation/t_surat/upload`,
            headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
            params: { field: 'file_dokumen' }
           }"
           :hints="formErrors.file_dokumen" placeholder="" label="File Dokumen (PDF/JPG)"
           fa-icon="upload" accept="*" :check="false" 
        />
    </div>

    <!-- SIGNATURE SECTION -->
    <div v-show="isSigning || values.is_signed" class="col-span-2 bg-yellow-50 p-4 border rounded-md mt-4">
      <h2 class="font-bold text-lg mb-2"><i class="fa fa-pen"></i> Tanda Tangan Karyawan</h2>
      
      <div v-if="!values.is_signed && isSigning">
        <p class="text-sm mb-2 text-gray-600">Silakan gambar tanda tangan Anda di kotak berikut, <b>ATAU</b> unggah foto tanda tangan Anda:</p>
        
        <div class="flex gap-4 items-start">
          <div>
            <div class="border-2 border-dashed border-gray-400 bg-white w-[300px] h-[150px] relative" style="touch-action: none;">
              <canvas id="signatureCanvas" width="300" height="150"></canvas>
            </div>
            <button @click="clearSignature" class="mt-2 text-red-500 text-sm hover:underline"><i class="fa fa-eraser"></i> Hapus / Ulangi</button>
          </div>

          <div class="border-l pl-4 border-gray-300 w-full max-w-xs">
            <p class="font-bold text-gray-700 text-sm mb-2">Atau Upload Gambar TTD:</p>
            <FieldUpload class="w-full mt-0" :bind="{ readonly: false }"
              :value="values.uploaded_signature" @input="(v)=>values.uploaded_signature=v" :maxSize="5"
              :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]"
              :api="{
                url: `${store.server.url_backend}/operation/t_surat/upload`,
                headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                params: { field: 'signature_img' }
               }"
               placeholder="" label="Upload Foto TTD (JPG/PNG)"
               fa-icon="upload" accept="image/*" :check="false" 
            />
          </div>
        </div>
      </div>

      <div v-else-if="values.is_signed">
        <p class="text-green-600 font-semibold mb-2">Telah ditandatangani pada: {{ values.signed_at }}</p>
        <img :src="values.signature_img && values.signature_img.includes('data:image') ? values.signature_img : (store.server.url_backend + '/' + values.signature_img)" class="border border-gray-300 w-[300px] h-[150px] object-contain bg-white" />
      </div>
    </div>
    
  </div>

  <div class="bg-gray-100 p-4 flex justify-end">
    <button v-show="isSigning && !values.is_signed" @click="submitSignature" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium mr-2">Submit Tanda Tangan</button>
    <button v-show="actionText && !isSigning" @click="onSave" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium">Simpan</button>
  </div>
</div>
@endverbatim
@endif