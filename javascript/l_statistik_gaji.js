import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRequesting = ref(false)
const exportHtml = ref(false)
const formErrors = ref({})

const now = new Date()
const currentYear = now.getFullYear()
const currentMonth = String(now.getMonth() + 1).padStart(2, '0')
const lastDay = new Date(currentYear, now.getMonth() + 1, 0).getDate()

const defaultStart = `${currentYear}-${currentMonth}-01`
const defaultEnd = `${currentYear}-${currentMonth}-${String(lastDay).padStart(2, '0')}`

const values = reactive({
  tipe: 'HTML',
  group_by: 'unit',
  periode_from: defaultStart,
  periode_to: defaultEnd,
  m_dir_id: null,
  m_divisi_id: null,
  m_dept_id: null,
  is_active: 'true',
})

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Laporan Statistik Penggajian'
})

// Helper format date string
const parseDateStr = (str) => {
  if (!str) return ''
  const parts = String(str).split(/[-\/]/)
  if (parts.length === 3) {
    if (parts[0].length === 4) {
      // YYYY-MM-DD
      return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`
    }
    // DD/MM/YYYY
    return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`
  }
  return str
}

// ------------------------------ GENERATE STATISTIK
const onGenerate = async () => {
  if (!values.tipe) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Tipe Output terlebih dahulu!',
    })
    return
  }

  if (!values.periode_from || !values.periode_to) {
    swal.fire({
      icon: 'error',
      text: 'Harap mengisi Periode Dari dan Sampai terlebih dahulu!',
    })
    return
  }

  const pFrom = parseDateStr(values.periode_from)
  const pTo = parseDateStr(values.periode_to)

  const params = {
    periode_from: pFrom,
    periode_to: pTo,
    group_by: values.group_by || 'unit',
    m_dir_id: values.m_dir_id ?? '',
    m_divisi_id: values.m_divisi_id ?? '',
    m_dept_id: values.m_dept_id ?? '',
    is_active: values.is_active ?? '',
    export: values.tipe?.toLowerCase(),
  }

  const queryStr = new URLSearchParams(params).toString()

  try {
    isRequesting.value = true

    const url = `${store.server.url_backend}/public/t_perhitungan_gaji/exportStatistikGaji?${queryStr}`

    if (values.tipe?.toLowerCase() === 'html') {
      const res = await fetch(url)
      if (!res.ok) {
        const errJson = await res.json().catch(() => null)
        throw new Error(errJson?.error || 'Gagal memuat Laporan Statistik Penggajian')
      }
      const htmlText = await res.text()
      exportHtml.value = true
      const targetDiv = document.getElementById('exportTable')
      if (targetDiv) {
        targetDiv.innerHTML = htmlText
      }
    } else {
      exportHtml.value = false
      const res = await fetch(url)
      if (!res.ok) {
        const errJson = await res.json().catch(() => null)
        throw new Error(errJson?.error || 'Gagal mengunduh file Excel Statistik Penggajian')
      }
      const blob = await res.blob()
      const downloadUrl = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = downloadUrl
      a.download = `Laporan_Statistik_Penggajian_${pFrom}_sd_${pTo}.xlsx`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      window.URL.revokeObjectURL(downloadUrl)
    }
  } catch (err) {
    console.error('Generate Error:', err)
    swal.fire({
      icon: 'error',
      text: err.message || err,
    })
  } finally {
    isRequesting.value = false
  }
}

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))