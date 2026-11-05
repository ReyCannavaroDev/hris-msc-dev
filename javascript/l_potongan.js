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
const apiTable = ref(null)
const exportHtml = ref(false)
const formErrors = ref({})
const activeTabIndex = ref(0)
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Laporan Potongan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

//  @else----------------------- LANDING
let initialValues = {}
const changedValues = []
const checkedState = ref()

const values = reactive({
  f_id: null // pastikan f_id sudah terisi dari UI sebelumnya
})

const onGenerate = async () => {
  if (!values.f_id) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Data Dahulu!',
    })
    return
  }

  const tempGet = []
  isRequesting.value = true

  if (values.f_id) {
    tempGet.push(`f_id=${values.f_id}`)
  }

  if (values.is_active !== null && values.is_active !== undefined && values.is_active !== '') {
      tempGet.push(`is_active=${values.is_active}`)
  }

  const paramsGet = tempGet.join("&")
  const dataURL = `https://msc.qqltech.com:7169/public/t_final_gaji/exportPotongan?${paramsGet}`

  try {
    const res = await fetch(dataURL, {
      method: "GET",
      headers: {
        Authorization: `${store.user.token_type} ${store.user.token}`,
        Accept: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      }
    })

    if (!res.ok) {
      const errText = await res.text()
      throw new Error(errText || "Gagal Export!")
    }

    const blob = await res.blob()
    const fileURL = window.URL.createObjectURL(blob)

    const a = document.createElement('a')
    a.href = fileURL
    a.download = `export-potongan-${values.f_id}.xlsx`
    a.click()

    window.URL.revokeObjectURL(fileURL)

  } catch (err) {
    swal.fire({
      icon: 'error',
      text: err.message || 'Terjadi kesalahan!'
    })
  }

  isRequesting.value = false
}



//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))