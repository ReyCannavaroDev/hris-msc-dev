
@verbatim
	<div class="flex flex-col gap-y-3">
		<div class="flex gap-x-4 px-2">
			<div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
				<div class="mb-4">
					<h1 class="text-[24px] mb-4 font-bold">
						Laporan Slip Gaji
					</h1>
					<hr>
				</div>
				<div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
					<!-- START COLUMN -->
					<div>
						<label class="font-semibold">Tipe Export</label>
						<FieldSelect
						:bind="{ readonly: !actionText }"
						class="w-full py-2 !mt-0"
						:value="values.tipe"
						:errorText="formErrors.tipe ? 'failed' : ''"
						@input="v => values.tipe = v"
						:hints="formErrors.tipe"
						:check="false"
						label=""
						:options="['PDF','HTML']"
						placeholder="Pilih Tipe Export"
						valueField="key"
						displayField="key"
						/>
					</div>
					<div >
						<label class="font-semibold">Pilih Final Gaji</label>
						<FieldPopup
						:value="values.f_id"
						:errorText="formErrors.f_id ? 'failed' : ''"
						@input="v => {
						values.f_id = v
						values.m_kary_id = null
						fetchKaryawanList()
						}"
						:hints="formErrors.f_id"
						class="w-full py-2 !mt-0"
						valueField="id"
						displayField="nomor"
						:api="{
						url: `${store.server.url_backend}/operation/t_final_gaji`,
						headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
						params: {
						where: values.m_dir_id ? `this.id in (select det.t_final_gaji_id from t_final_gaji_det det where det.m_kary_dir_id = ${values.m_dir_id})` : '',
						simplest:true,
						searchfield: 'this.desc, this.nomor, this.periode_awal, this.periode_akhir'
						}
						}"
						placeholder="" label="" :check="false"
						:columns="[{
						headerName: 'No',
						valueGetter:(p)=>p.node.rowIndex + 1,
						width: 60,
						sortable: false, resizable: false, filter: false,
						cellClass: ['justify-center', 'bg-gray-50']
						},
						{
						flex: 1,
						field: 'nomor',
						wrapText:true,
						sortable: false, resizable: true, filter: 'ColFilter',
						cellClass: ['border-r', '!border-gray-200', 'justify-end']
						},
						{
						flex: 1,
						field: 'desc',
						wrapText:true,
						headerName: 'Nama Karyawan',
						sortable: false, resizable: true, filter: 'ColFilter',
						cellClass: ['border-r', '!border-gray-200', 'justify-start']
						},
						{
						flex: 1,
						field: 'periode_awal',
						wrapText:true,
						headerName: 'Periode Awal',
						sortable: false, resizable: true, filter: 'ColFilter',
						cellClass: ['border-r', '!border-gray-200', 'justify-start']
						},
						{
						flex: 1,
						wrapText:true,
						field: 'periode_akhir',
						headerName: 'Periode Akhir',
						sortable: false, resizable: true, filter: 'ColFilter',
						cellClass: ['border-r', '!border-gray-200', 'justify-start']
						},
						]"
						/>
					</div>
					<div>
						<label class="font-semibold">Pilih Unit</label>
						<FieldSelect
						class="w-full py-2 !mt-0"
						:bind="{ clearable:true }"
						:value="values.m_dir_id"
						:errorText="formErrors.m_dir_id ? 'failed' : ''"
						:hints="formErrors.m_dir_id"
						displayField="nama"
						valueField="id"
						@input="(v)=>{
						values.m_dir_id=v
						values.m_kary_id=null
						fetchKaryawanList()
						}"
						:api="{
						url: `${store.server.url_backend}/operation/m_dir`,
						headers: {
						Authorization: `${store.user.token_type} ${store.user.token}`
						},
						params: {
						where: values.f_id ? `this.id in (select det.m_kary_dir_id from t_final_gaji_det det where det.t_final_gaji_id = ${values.f_id})` : '',
						simplest:true,
						single:true,
						transform:false,
						}
						}"
						placeholder="Pilih Unit / Direktorat"
						label=""
						:check="false"
						/>
					</div>
					<div>
						<label class="font-semibold">Nama Karyawan</label>
						<FieldPopup
						:value="values.m_kary_id"
						:errorText="formErrors.m_kary_id ? 'failed' : ''"
						@input="v => values.m_kary_id = v"
						:hints="formErrors.m_kary_id"
						class="w-full py-2 !mt-0"
						valueField="m_kary_id"
						displayField="m_kary.nama_lengkap"
						:api="{
						url: `${store.server.url_backend}/operation/t_final_gaji_det`,
						headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
						params: {
						where: `this.t_final_gaji_id = ${values.f_id}` + (values.m_dir_id ? ` and this.m_kary_dir_id = ${values.m_dir_id}` : ''),
						simplest:true,
						searchfield: 'this.nik, this.nama_lengkap, this.nama_depan, this.nama_belakang, m_zona.nama, m_dir.nama, m_divisi.nama, m_dept.nama'
						}
						}"
						placeholder="Cari Nomor Induk Karyawan" label="" :check="false"
						:columns="[{
						headerName: 'No',
						valueGetter:(p)=>p.node.rowIndex + 1,
						width: 60,
						sortable: false, resizable: false, filter: false,
						cellClass: ['justify-center', 'bg-gray-50']
						},
						{
						flex: 1,
						field: 'm_kary.kode',
						headerName: 'Kode',
						wrapText:true,
						sortable: false, resizable: true, filter: 'ColFilter',
						cellClass: ['border-r', '!border-gray-200', 'justify-start']
						},
						{
						flex: 1,
						field: 'm_kary.nama_lengkap',
						wrapText:true,
						headerName: 'Nama Karyawan',
						sortable: false, resizable: true, filter: 'ColFilter',
						cellClass: ['border-r', '!border-gray-200', 'justify-start']
						},
						{
						flex: 1,
						field: 'm_kary_dir.nama',
						wrapText:true,
						headerName: 'Unit',
						sortable: false, resizable: true, filter: 'ColFilter',
						cellClass: ['border-r', '!border-gray-200', 'justify-start']
						}
						]"
						/>
					</div>
				</div>
				<!-- TABLE DAFTAR KARYAWAN -->
				<div class="mt-6 mb-4 px-4 bg-gray-50 p-4 border rounded shadow-sm" v-if="karyawanList && karyawanList.length > 0">
					<h3 class="text-lg font-semibold mb-3 text-gray-800">Daftar Karyawan di Unit Terpilih</h3>
					<div class="overflow-x-auto">
						<table class="w-full table-auto border-collapse border border-[#CACACA] bg-white rounded-lg">
							<thead>
								<tr class="border bg-[#f8f8f8]">
									<td class="text-[#8F8F8F] font-semibold text-[14px] px-4 py-3 text-center w-[5%] border border-[#CACACA]">No.</td>
									<td class="text-[#8F8F8F] font-semibold text-[14px] px-4 py-3 text-left w-[20%] border border-[#CACACA]">ID Karyawan</td>
									<td class="text-[#8F8F8F] font-semibold text-[14px] px-4 py-3 text-left w-[35%] border border-[#CACACA]">Nama Karyawan</td>
									<td class="text-[#8F8F8F] font-semibold text-[14px] px-4 py-3 text-left w-[25%] border border-[#CACACA]">Jabatan</td>
									<td class="text-[#8F8F8F] font-semibold text-[14px] px-4 py-3 text-right w-[15%] border border-[#CACACA]">Gaji Bersih</td>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(item, i) in karyawanList" :key="item.id" class="border hover:bg-yellow-50 transition duration-150">
									<td class="text-center border border-[#CACACA] px-4 py-2">{{ i + 1 }}.</td>
									<td class="text-left border border-[#CACACA] px-4 py-2">{{ item['m_kary.kode'] }}</td>
									<td class="text-left border border-[#CACACA] px-4 py-2 font-medium">{{ item['m_kary.nama_lengkap'] }}</td>
									<td class="text-left border border-[#CACACA] px-4 py-2">{{ item['m_kary_divisi.nama'] || '-' }}</td>
									<td class="text-right border border-[#CACACA] px-4 py-2 font-semibold">{{ formatRupiah(item.netto) }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<!-- END COLUMN -->
				<div class="flex flex-row justify-end space-x-[20px] mt-[1em]">
					<button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] ">
						{{ values.tipe?.toLowerCase() === 'html' ? 'View' : 'Export' }}
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