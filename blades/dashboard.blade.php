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

    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
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