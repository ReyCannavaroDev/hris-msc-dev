@verbatim
	<div class="flex flex-col gap-y-3">
		<div class="flex gap-x-4 px-2">
			<div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
				<div class="mb-4">
					<h1 class="text-[24px] mb-4 font-bold">
						Laporan Sisa Cuti Karyawan
					</h1>
					<hr>
				</div>
				<div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
					<!-- Tipe Export -->
					<div>
						<label class="font-semibold">Tipe Export</label>
						<FieldSelect
						:bind="{ readonly: !actionText, clearable: false }"
						class="w-full py-2 !mt-0"
						:value="values.tipe"
						:errorText="formErrors.tipe ? 'failed' : ''"
						@input="v => values.tipe = v"
						:hints="formErrors.tipe"
						:check="false"
						label=""
						:options="['Excel','PDF','HTML']"
						placeholder="Pilih Tipe Export"
						valueField="key"
						displayField="key"
						/>
					</div>

					<!-- Periode Bulan & Tahun -->
					<div>
						<label class="font-semibold">Periode (Bulan/Tahun) <span class="text-red-500">*</span></label>
						<FieldX
						type="month"
						:bind="{ readonly: false }"
						class="w-full py-2 !mt-0"
						:value="values.periode"
						label=""
						placeholder="YYYY-MM"
						:errorText="formErrors.periode ? 'failed' : ''"
						@input="v => values.periode = v"
						:hints="formErrors.periode"
						:check="false"
						/>
					</div>

					<!-- Direktorat -->
					<div>
						<label class="font-semibold">Direktorat</label>
						<FieldSelect
						:bind="{ readonly: !actionText }"
						class="w-full py-2 !mt-0"
						:value="values.m_dir_id"
						:errorText="formErrors.m_dir_id ? 'failed' : ''"
						@input="v => values.m_dir_id = v"
						:hints="formErrors.m_dir_id"
						:check="false"
						label=""
						@update:valueFull="(objVal)=>{
						values.m_divisi_id = null
						values.m_dept_id = null
						values.m_kary_id = null
						}"
						placeholder="Pilih Direktorat"
						valueField="id"
						displayField="nama"
						:api="{
						url: `${store.server.url_backend}/operation/m_dir`,
						headers: {
						'Content-Type': 'Application/json',
						Authorization: `${store.user.token_type} ${store.user.token}`
						},
						params: {
						single: true,
						join: false,
						where: `this.is_active='true'`
						}
						}"
						/>
					</div>

					<!-- Divisi -->
					<div>
						<label class="font-semibold">Divisi</label>
						<FieldSelect
						:bind="{ readonly: !actionText }"
						class="w-full py-2 !mt-0"
						:value="values.m_divisi_id"
						:errorText="formErrors.m_divisi_id ? 'failed' : ''"
						@input="v => values.m_divisi_id = v"
						:hints="formErrors.m_divisi_id"
						:check="false"
						label=""
						@update:valueFull="(objVal)=>{
						values.m_dept_id = null
						values.m_kary_id = null
						}"
						placeholder="Pilih Divisi"
						valueField="id"
						displayField="nama"
						:api="{
						url: `${store.server.url_backend}/operation/m_divisi`,
						headers: {
						'Content-Type': 'Application/json',
						Authorization: `${store.user.token_type} ${store.user.token}`
						},
						params: {
						single: true,
						join: false,
						where: values.m_dir_id ? `this.m_dir_id=${values.m_dir_id} AND this.is_active='true'` : `this.is_active='true'`
						}
						}"
						/>
					</div>

					<!-- Departemen -->
					<div>
						<label class="font-semibold">Departemen</label>
						<FieldSelect
						:bind="{ readonly: !actionText }"
						class="w-full py-2 !mt-0"
						:value="values.m_dept_id"
						:errorText="formErrors.m_dept_id ? 'failed' : ''"
						@input="v => values.m_dept_id = v"
						:hints="formErrors.m_dept_id"
						:check="false"
						label=""
						@update:valueFull="(objVal)=>{
						values.m_kary_id = null
						}"
						placeholder="Pilih Departemen"
						valueField="id"
						displayField="nama"
						:api="{
						url: `${store.server.url_backend}/operation/m_dept`,
						headers: {
						'Content-Type': 'Application/json',
						Authorization: `${store.user.token_type} ${store.user.token}`
						},
						params: {
						single: true,
						join: false,
						where: values.m_divisi_id ? `this.m_divisi_id=${values.m_divisi_id} AND this.is_active='true'` : `this.is_active='true'`
						}
						}"
						/>
					</div>

					<!-- Karyawan -->
					<div>
						<label class="font-semibold">Karyawan</label>
						<FieldSelect
						class="w-full py-2 !mt-0"
						:bind="{ readonly: !actionText, clearable: true }"
						:value="values.m_kary_id"
						@input="v => values.m_kary_id = v"
						:errorText="formErrors.m_kary_id ? 'failed' : ''"
						:hints="formErrors.m_kary_id"
						valueField="id"
						displayField="nama_lengkap"
						:api="{
						url: `${store.server.url_backend}/operation/m_kary`,
						headers: {
						'Content-Type': 'Application/json',
						Authorization: `${store.user.token_type} ${store.user.token}`
						},
						params: {
						simplest: true,
						transform: false,
						join: false,
						where: values.m_dept_id ? `this.m_dept_id=${values.m_dept_id} AND this.is_active='true'` : (values.m_divisi_id ? `this.m_divisi_id=${values.m_divisi_id} AND this.is_active='true'` : `this.is_active='true'`)
						}
						}"
						placeholder="Semua Karyawan (Opsional)"
						label=""
						:check="false"
						/>
					</div>
				</div>

				<!-- Action Button -->
				<div class="flex flex-row justify-end space-x-[20px] mt-[1em] px-4">
					<button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] font-semibold">
						{{ values.tipe?.toLowerCase() === 'html' ? 'Tampilkan Laporan' : 'Export Laporan' }}
					</button>
				</div>

				<!-- Output Preview HTML -->
				<div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
					<hr class="mb-4">
					<div id="exportTable" class="bg-white p-4 border rounded shadow-inner">
					</div>
				</div>
			</div>
		</div>
	</div>
@endverbatim
