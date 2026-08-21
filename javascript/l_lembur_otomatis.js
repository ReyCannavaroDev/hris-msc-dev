import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const exportHtml = ref(false)
const dataLembur = ref([])
const isRequesting = ref(false)
const formErrors = ref({})

const thisMonth = new Date().toISOString().split('T')[0]
const tempYear = thisMonth.split('-')[0]
const tempMonth = thisMonth.split('-')[1]

const values = reactive({
  tipe: 'HTML',
  periode: tempYear + '-' + tempMonth,
  dir_id: null,
  divisi_id: null,
  m_kary_id: null
})

// ------------------------------ GENERATE REPORT
const onGenerate = async () => {
  if (values.tipe === null) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Tipe Export terlebih dahulu!',
    })
    return
  }

  if (values.periode === null) {
    swal.fire({
      icon: 'error',
      text: 'Harap memilih Periode terlebih dahulu!',
    })
    return
  }

  const params = {
    month: values.periode,
    m_dir_id: values.dir_id ?? '',
    m_divisi_id: values.divisi_id ?? '',
    m_kary_id: values.m_kary_id ?? ''
  }
  const queryStr = new URLSearchParams(params).toString()

  if (values.tipe.toLowerCase() === 'excel') {
    exportHtml.value = false
    try {
      const url = `${store.server.url_backend}/public/presensi_absensi/exportLemburOtomatis?${queryStr}`
      const res = await fetch(url)
      if (!res.ok) throw new Error('Gagal mengunduh file Excel')
      const blob = await res.blob()
      const downloadUrl = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = downloadUrl
      a.download = `Laporan_Lembur_Otomatis_${values.periode}.xlsx`
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
      const url = `${store.server.url_backend}/operation/presensi_absensi/lembur_otomatis?${queryStr}`
      const res = await fetch(url, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      if (!res.ok) throw new Error('Gagal menarik data pratinjau')
      const responseJson = await res.json()
      dataLembur.value = responseJson.data || []
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

onBeforeMount(() => {
  document.title = 'Laporan Lembur Otomatis'
})

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))
