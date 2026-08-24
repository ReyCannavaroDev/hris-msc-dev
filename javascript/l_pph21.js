import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRequesting = ref(false)
const exportHtml = ref(false)
const formErrors = ref({})

const thisMonth = new Date().toISOString().split('T')[0]
const tempYear = thisMonth.split('-')[0]
const tempMonth = thisMonth.split('-')[1]

const values = reactive({
  tipe: 'Excel',
  periode: tempYear + '-' + tempMonth,
  m_dir_id: null,
  m_divisi_id: null,
  m_kary_id: null,
  is_active: 'true',
})

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Laporan PPh 21'
})

// ------------------------------ GENERATE REPORT
const onGenerate = async () => {
  if (!values.tipe) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Tipe Export terlebih dahulu!',
    })
    return
  }

  if (!values.periode) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Periode Bulan terlebih dahulu!',
    })
    return
  }

  const params = {
    month: values.periode,
    m_dir_id: values.m_dir_id ?? '',
    m_divisi_id: values.m_divisi_id ?? '',
    m_kary_id: Array.isArray(values.m_kary_id) ? values.m_kary_id.join(',') : (values.m_kary_id ?? ''),
    is_active: values.is_active ?? '',
    export: values.tipe?.toLowerCase(),
  }

  const queryStr = new URLSearchParams(params).toString()

  try {
    isRequesting.value = true

    const url = `${store.server.url_backend}/public/t_perhitungan_gaji/exportPph21?${queryStr}`

    if (values.tipe?.toLowerCase() === 'html') {
      const res = await fetch(url)
      if (!res.ok) {
        const errJson = await res.json().catch(() => null)
        throw new Error(errJson?.error || 'Gagal memuat pratinjau tabel Laporan PPh 21')
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
        throw new Error(errJson?.error || 'Gagal mengunduh file Excel Laporan PPh 21')
      }
      const blob = await res.blob()
      const downloadUrl = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = downloadUrl
      a.download = `Laporan_PPh21_${values.periode}.xlsx`
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