import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const exportHtml = ref(false)
const dataThr = ref([])
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
  date_cut_off: todayFormatted,
  dir_id: null,
  divisi_id: null,
  m_kary_id: null,
})

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Laporan THR'
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

const onDateCutOffChange= (val) => {
  values.date_cut_off  = val
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
  if (!values.date_cut_off ) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Tanggal Cut Off THR terlebih dahulu!',
    })
    return
  }

  const params = {
    m_dir_id: values.m_dir_id ?? '',
    m_divisi_id: values.m_divisi_id ?? '',
    m_kary_id: values.m_kary_id ?? '',
    is_active: values.is_active ?? '',
    agama: values.agama ?? '',
    date_cut_off: formatDateToYmd(values.date_cut_off)
  }


  const queryStr = new URLSearchParams(params).toString()

  if (values.tipe.toLowerCase() === 'excel') {
    exportHtml.value = false
    try {
      //Kunci tombol dan munculkan loading overlay
      isRequesting.value = true

      const url = `${store.server.url_backend}/public/m_kary/exportThr?${queryStr}`
      const res = await fetch(url)
      if (!res.ok) throw new Error('Gagal mengunduh file Excel THR')
      const blob = await res.blob()
      const downloadUrl = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = downloadUrl
      const labelPeriod = `${formatDateToYmd(values.date_cut_off)}`
      a.download = `Laporan_THR_${labelPeriod}.xlsx`
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
    }finally {
      // Kembalikan tombol ke keadaan semula saat sukses maupun error
      isRequesting.value = false 
    }
  } else {
    // HTML Preview
    isRequesting.value = true
    try {
      const htmlParams = { ...params, tipe: 'html' }
      const htmlQueryStr = new URLSearchParams(htmlParams).toString()

      const url = `${store.server.url_backend}/public/m_kary/exportThr?${htmlQueryStr}`
      const res = await fetch(url, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })

      if (!res.ok) throw new Error('Gagal menarik data THR')
      const responseJson = await res.json()
      dataThr.value = responseJson.data || []
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