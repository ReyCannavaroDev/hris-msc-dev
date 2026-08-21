import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, watch } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const loaderData = ref(false)
const isRequesting = ref(false)
const apiTable = ref(null)

const thisMonth = new Date().toISOString().split('T')[0]
const tempYear = thisMonth.split('-')[0]
const tempMonth = thisMonth.split('-')[1]

const headerValues = reactive({
  month: tempYear + '-' + tempMonth,
  dir_id: null,
  divisi_id: null,
  m_kary_id: null
})

const landing = reactive({
  actions: [],
  api: {
    url: `${store.server.url_backend}/operation/presensi_absensi/lembur_otomatis`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      month: tempYear + '-' + tempMonth,
      m_dir_id: '',
      m_divisi_id: '',
      m_kary_id: ''
    },
    onsuccess(response) {
      const rows = response.data || []
      return {
        current_page: 1,
        has_next: false,
        data: rows
      }
    }
  },
  columns: [
    {
      headerName: 'No',
      valueGetter: (params) => params.node.rowIndex + 1,
      width: 60,
      sortable: false,
      resizable: true,
      cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
    },
    {
      field: 'nik',
      headerName: 'NIK',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'nama',
      headerName: 'Nama Karyawan',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'unit',
      headerName: 'Unit',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'jabatan',
      headerName: 'Jabatan',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'tanggal',
      headerName: 'Tanggal',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      field: 'hari',
      headerName: 'Hari',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      field: 'tipe_hari',
      headerName: 'Tipe Hari',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: ({ value }) => {
        const color = value === 'KERJA' ? 'green' : 'red'
        return `<span class="bg-${color}-100 text-${color}-800 px-2 py-0.5 rounded text-xs font-semibold">${value}</span>`
      }
    },
    {
      field: 'jam_selesai_jadwal',
      headerName: 'Jadwal Pulang',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      field: 'checkout_aktual',
      headerName: 'Checkout',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      field: 'jam_lembur',
      headerName: 'Durasi (Jam)',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-end'],
      valueFormatter: (params) => params.value ? `${params.value} Jam` : ''
    },
    {
      field: 'nominal_lembur',
      headerName: 'Nominal',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-end', 'font-bold', 'text-green-600'],
      valueFormatter: (params) => params.value ? `Rp ${Number(params.value).toLocaleString('id-ID')}` : ''
    }
  ]
})

watch(headerValues, () => {
  if (apiTable.value) {
    landing.api.params.month = headerValues.month
    landing.api.params.m_dir_id = headerValues.dir_id ?? ''
    landing.api.params.m_divisi_id = headerValues.divisi_id ?? ''
    landing.api.params.m_kary_id = headerValues.m_kary_id ?? ''
    apiTable.value.reload()
  }
}, { deep: true })

onBeforeMount(() => {
  document.title = 'Lembur Otomatis Absensi'
})

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))
