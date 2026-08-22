import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const exportHtml = ref(false)
const dataTerlambat = ref([])
const isRequesting = ref(false)
const formErrors = ref({})

const thisMonth = new Date().toISOString().split('T')[0]
const tempYear = thisMonth.split('-')[0]
const tempMonth = thisMonth.split('-')[1]

// Format helper date to DD/MM/YYYY
const now = new Date()
const currentDay = String(now.getDate()).padStart(2, '0')
const currentMonth = String(now.getMonth() + 1).padStart(2, '0')
const currentYear = now.getFullYear()
const todayFormatted = `${currentDay}/${currentMonth}/${currentYear}`

const values = reactive({
  tipe: 'HTML',
  tipe_periode: 'Bulan', // Pilihan flexible: 'Bulan' atau 'Rentang Tanggal'
  periode: tempYear + '-' + tempMonth,
  date_start: todayFormatted,
  date_end: todayFormatted,
  dir_id: null,
  divisi_id: null,
  m_kary_id: null,
  is_active: 'true'
})

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Laporan Keterlambatan'
})

// Helper format date dari DD/MM/YYYY ke YYYY-MM-DD
const formatDateToYmd = (val) => {
  if (!val) return ''
  if (val.includes('/')) {
    const parts = val.split('/')
    if (parts.length === 3) {
      return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`
    }
  }
  return val
}

const onDateStartChange = (val) => {
  if (!val) {
    values.date_end = null
    return
  }
  values.date_start = val
}

const onDateEndChange = (val) => {
  values.date_end = val
}

const resetPeriode = () => {
  values.periode = tempYear + '-' + tempMonth
  values.date_start = todayFormatted
  values.date_end = todayFormatted
}

// ------------------------------ GENERATE REPORT
const onGenerate = async () => {
  if (!values.tipe) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Tipe Export terlebih dahulu!',
    })
    return
  }

  // Validasi periode sesuai tipe periode fleksibel yang dipilih
  if (values.tipe_periode === 'Bulan' && !values.periode) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Periode Bulan terlebih dahulu!',
    })
    return
  }

  if (values.tipe_periode === 'Rentang Tanggal') {
    if (!values.date_start || !values.date_end) {
      swal.fire({
        icon: 'error',
        text: 'Harap mengisi Tanggal Awal dan Tanggal Akhir periode!',
      })
      return
    }
  }

  const params = {
    tipe_periode: values.tipe_periode,
    m_dir_id: values.dir_id ?? '',
    m_divisi_id: values.divisi_id ?? '',
    m_kary_id: values.m_kary_id ?? '',
    is_active: values.is_active ?? ''
  }

  if (values.tipe_periode === 'Bulan') {
    params.month = values.periode
  } else {
    params.date_start = formatDateToYmd(values.date_start)
    params.date_end = formatDateToYmd(values.date_end)
  }

  const queryStr = new URLSearchParams(params).toString()

  if (values.tipe.toLowerCase() === 'excel') {
    exportHtml.value = false
    try {
      const url = `${store.server.url_backend}/public/presensi_absensi/exportTerlambat?${queryStr}`
      const res = await fetch(url)
      if (!res.ok) throw new Error('Gagal mengunduh file Excel Laporan Terlambat')
      const blob = await res.blob()
      const downloadUrl = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = downloadUrl
      const labelPeriod = values.tipe_periode === 'Bulan' 
        ? values.periode 
        : `${formatDateToYmd(values.date_start)}_sd_${formatDateToYmd(values.date_end)}`
      a.download = `Laporan_Keterlambatan_${labelPeriod}.xlsx`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      window.URL.revokeObjectURL(downloadUrl)
    } catch (err) {
      console.error('Download Error:', err)
      swal.fire({
        icon: 'error',
        text: err.message || err
      })
    }
  } else {
    // HTML Preview
    isRequesting.value = true
    try {
      const url = `${store.server.url_backend}/operation/presensi_absensi/laporan_terlambat?${queryStr}`
      const res = await fetch(url, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      if (!res.ok) throw new Error('Gagal menarik data pratinjau keterlambatan')
      const responseJson = await res.json()
      dataTerlambat.value = responseJson.data || []
      exportHtml.value = true
    } catch (err) {
      console.error('Preview Error:', err)
      swal.fire({
        icon: 'error',
        text: err.message || err
      })
    } finally {
      isRequesting.value = false
    }
  }
}

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))