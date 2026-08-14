<div v-if="beforeLoad" class="h-87vh w-full  items-center">
 <p class="italic font-semibold text-center text-gray-500">Harap tunggu sistem sedang menyusun konten..</p>
</div>
<div class="h-87vh w-full  items-center rounded text-sm" v-if="is_superadmin == true">
  <div class="grid grid-cols-4 gap-6 px-2 w-full">
    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
      <p>Total Unit Aktif</p>
      <div class="text-green-500 font-bold text-2xl"> @{{totalDepartemen}} </div>
    </div>

    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
      <p>Total Jabatan Aktif</p>
      <div class="text-green-500 font-bold text-2xl"> @{{ totalDivisi }} </div>
    </div>

    <div @click="openPegawaiAbsenModal" class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2 cursor-pointer">
      <p> Pegawai Tidak Hadir Hari Ini </p>
      <div class="text-red-500 font-bold text-2xl"> @{{pegawaiAbsen}} </div>
    </div>

    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
      <p> Pegawai Masuk Hari Ini </p>
      <div class="text-green-500 font-bold text-2xl"> @{{pegawaiMasuk}}</div>
    </div>
  </div>

  <div class="grid <md:grid-cols-1 grid-cols-2 gap-6 p-2" v-if="is_superadmin == true">
    <div
      class="col-span-2 p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full w-full">
      <h2 class="font-semibold text-md justify-start mb-4">Pengeluaran Gaji Karyawan Bulan Ini Per Unit</h2>
      <column-chart :stacked="true" :library="{
          accessibility: {
            enabled: false
          },
          yAxis: {
              min: 0,
              title: {
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              },
              gridLineWidth: 0,
          },
          chart: {
              backgroundColor: 'rgba(0,0,0,0)',
          }
        }" :data="chartData" adapter="highcharts">
      </column-chart>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full p-2" v-if="is_superadmin == true">
    <div
      class="p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full w-full">
      <h2 class="font-semibold text-md justify-start mb-4">Total Terlambat (menit) Dalam 1 Bulan</h2>
      <column-chart :stacked="true" :library="{
          accessibility: {
            enabled: false
          },
          yAxis: {
              min: 0,
              title: {
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              },
              gridLineWidth: 0,
          },
          chart: {
              backgroundColor: 'rgba(0,0,0,0)',
          }
        }" :data="chartDataLate" adapter="highcharts">
      </column-chart>
    </div>
    <div
      class="p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full w-full">
      <h2 class="font-semibold text-md justify-start mb-4">Terbanyak Tidak Hadir (Presensi Online) Dalam 1 Bulan</h2>
      <column-chart :stacked="true" :library="{
          accessibility: {
            enabled: false
          },
          yAxis: {
              min: 0,
              title: {
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              },
              gridLineWidth: 0,
          },
          chart: {
              backgroundColor: 'rgba(0,0,0,0)',
          }
        }" :data="chartDataAbsent" adapter="highcharts">
      </column-chart>
    </div>
    <div
      class="p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full w-full">
      <h2 class="font-semibold text-md justify-start mb-4">Terbanyak Absen Sempurna Dalam 1 Bulan</h2>
      <column-chart :stacked="true" :library="{
          accessibility: {
            enabled: false
          },
          yAxis: {
              min: 0,
              title: {
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              },
              gridLineWidth: 0,
          },
          chart: {
              backgroundColor: 'rgba(0,0,0,0)',
          }
        }" :data="chartDataPerfect" adapter="highcharts">
      </column-chart>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full p-2" v-if="is_superadmin == true">
    <!-- Ulang Tahun Bulan Ini -->
    <div class="p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full flex flex-col max-h-80">
      <h2 class="font-semibold text-md justify-start mb-4 sticky top-0 bg-white z-10"><i class="fa fa-birthday-cake mr-2 text-pink-500"></i>Ulang Tahun Bulan Ini</h2>
      <div class="overflow-y-auto flex-1">
        <div v-if="birthdaysThisMonth.length" class="divide-y divide-gray-200">
          <div v-for="(kary, idx) in birthdaysThisMonth" :key="idx" class="py-2 flex justify-between items-center">
            <div>
              <p class="font-medium text-gray-800">@{{ kary.nama_lengkap }}</p>
            </div>
            <div class="text-sm font-bold text-gray-500">
              @{{ new Date(kary.tgl_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'long' }) }}
            </div>
          </div>
        </div>
        <p v-else class="text-center text-gray-500 py-4">Tidak ada yang berulang tahun bulan ini.</p>
      </div>
    </div>

    <!-- Event / Libur Nasional Bulan Ini -->
    <div class="p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full flex flex-col max-h-80">
      <h2 class="font-semibold text-md justify-start mb-4 sticky top-0 bg-white z-10"><i class="fa fa-calendar-day mr-2 text-red-500"></i>Event / Libur Nasional Bulan Ini</h2>
      <div class="overflow-y-auto flex-1">
        <div v-if="holidaysThisMonth.length" class="divide-y divide-gray-200">
          <div v-for="(libur, idx) in holidaysThisMonth" :key="idx" class="py-2 flex flex-col">
            <div class="flex justify-between items-start">
              <p class="font-medium text-gray-800">@{{ libur.desc || libur.kode }}</p>
              <span class="text-sm font-bold text-red-500 bg-red-100 px-2 py-0.5 rounded ml-2 whitespace-nowrap">
                @{{ new Date(libur.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long' }) }}
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-gray-500 py-4">Tidak ada event atau libur nasional bulan ini.</p>
      </div>
    </div>
  </div>

  <div v-show="showPegawaiAbsenModal" class="fixed inset-0 flex items-center justify-center z-50">
    <div @click="closePegawaiAbsenModal" class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-[90%] md:w-[520px] mx-auto rounded shadow-lg z-50 overflow-hidden">
      <div class="modal-content py-4 text-left px-6">
        <div class="modal-header flex items-center justify-between flex-wrap border-b pb-3">
          <div>
            <h2 class="font-semibold text-md">Pegawai Tidak Hadir Hari Ini</h2>
            <p class="text-xs text-gray-500">Total: @{{ pegawaiTidakHadir.length }} pegawai</p>
          </div>
          <button @click="closePegawaiAbsenModal" class="text-gray-500 hover:text-gray-800 text-xl leading-none">&times;</button>
        </div>

        <div class="modal-body max-h-[60vh] overflow-y-auto py-3">
          <div v-if="pegawaiTidakHadir.length" class="divide-y divide-gray-200">
            <div v-for="(pegawai, index) in pegawaiTidakHadir" :key="pegawai.id || index" class="py-2 flex items-start gap-3">
              <span class="text-xs text-gray-500 w-7 pt-1">@{{ index + 1 }}.</span>
              <div>
                <p class="font-medium text-gray-800">@{{ pegawai.nama_lengkap }}</p>
                <p class="text-xs text-gray-500">@{{ pegawai.tanggal }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-center text-gray-500 py-8">Tidak ada pegawai yang tidak hadir hari ini.</p>
        </div>

        <div class="modal-footer flex justify-end border-t pt-3">
          <button @click="closePegawaiAbsenModal" class="modal-button bg-yellow-500 hover:bg-yellow-600 text-white font-semibold ml-2 px-3 py-1 rounded-sm">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
