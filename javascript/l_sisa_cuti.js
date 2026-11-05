import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const exportHtml = ref(false)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Laporan Sisa Cuti Karyawan'
})

// Current month (YYYY-MM)
const now = new Date()
const currentMonthStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`

const values = reactive({
  tipe: 'HTML',
  tipe_report: 'Laporan Sisa Cuti Karyawan',
  periode: currentMonthStr,
  m_dir_id: null,
  m_divisi_id: null,
  m_dept_id: null,
  m_kary_id: null,
})

const onGenerate = async () => {
  if (!values.tipe) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Tipe Export Terlebih Dahulu!',
    })
    return
  }
  if (!values.periode) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Periode Terlebih Dahulu!',
    })
    return
  }

  const tempGet = []
  isRequesting.value = true

  if (values.tipe) {
    if (values.tipe?.toLowerCase() === 'excel') {
      tempGet.push(`export=xls`)
    } else if (values.tipe?.toLowerCase() === 'pdf') {
      tempGet.push(`export=pdf`)
    }
  }

  tempGet.push(`tipe_report=${encodeURIComponent(values.tipe_report)}`)
  tempGet.push(`periode=${values.periode}`)

  if (values.m_divisi_id) {
    tempGet.push(`m_divisi_id=${values.m_divisi_id}`)
  }
  if (values.m_dept_id) {
    tempGet.push(`m_dept_id=${values.m_dept_id}`)
  }
  if (values.m_kary_id) {
    tempGet.push(`kary_id=${values.m_kary_id}`)
  }

  const paramsGet = tempGet.join("&")
  const reportUrl = `${store.server.url_backend}/web/report_sisa_cuti_karyawan?${paramsGet}`

  if (values.tipe?.toLowerCase() !== 'html') {
    exportHtml.value = false
    window.open(reportUrl)
  } else {
    try {
      const res = await fetch(reportUrl, {
        headers: {
          'Content-Type': 'html',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      const html = await res.text()
      exportHtml.value = true
      const targetDiv = document.getElementById('exportTable')
      if (targetDiv) {
        targetDiv.innerHTML = html
      }
    } catch (error) {
      swal.fire({
        icon: 'error',
        text: error.message || error,
      })
    }
  }

  isRequesting.value = false
}

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))
