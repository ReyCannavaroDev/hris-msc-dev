import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, watch, onActivated, computed } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const disableGroup = ref(route.params.id === 'create' ? false : true)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const apiTable2 = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
const activeTabIndex = ref(0)

const is_approval = route.query.is_approval ? true : false
const is_to_upload = route.query.is_to_upload ? true : false
let isApproved = ref(false)
let isFinish = ref(false)

const detailKont = ref([])

let trx_dtl = reactive({ items: [] })
let detailKey = ref(0)
let modalOpen = ref(false)
let detailIdxSelected = ref(0)
let trx_dtl_sub = reactive({ items: [] })
let _id = ref(0)
const startMonth = ref(null)
const endMonth = ref(null)
// ------------------------------ PERSIAPAN
const endpointApi = 't_extend_kontrak'
const month = ref('')


onBeforeMount(() => {
  document.title = is_approval ? 'Approval Perpanjangan Kontrak' : 'Transaksi Perpanjangan Kontrak'
})

//  @if( $id )------------------- JS CONTENT ! PENTING JANGAN DIHAPUS

// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
})

const handleKeyDown = (event) => {
  if (event?.ctrlKey && event?.key === 's' && actionText.value) {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}

let initialValues = {}
const changedValues = []

let values = reactive({
  m_karyawan_id: route.query.IsKary ? Number(route.query.IsKary) : null,
  status: 'DRAFT'
})

// DEFAULT VALUE BEFORE MOUNT --UBAH DISINI
const defaultValues = () => {
  values.is_active = 1
  values.m_karyawan_id = 22
}

watch(
  () => [values.tgl_awal, values.duration],
  () => {
    hitungTanggalAkhir()
  }
)

async function hitungTanggalAkhir() {
  const { tgl_awal, duration } = values

  if (!tgl_awal || !duration) return

  const formatDate = (date) => {
    if (!date) return ''
    const [day, month, year] = date.split('/')
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
  }

  const formattedTglAwal = formatDate(tgl_awal)

  try {
    const res = await fetch(`${store.server.url_backend}/operation/m_kary/countEnd`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({
        tgl_awal: formattedTglAwal,
        duration
      })
    })

    if (!res.ok) throw new Error('Gagal hit API')

    const data = await res.json()
    console.log('ini data', data)
    values.tgl_akhir = data?.tgl_akhir || ''
  } catch (err) {
    console.error('Gagal ambil tanggal akhir:', err)
  }
}

function onProcess(typePar) {
  const payload = {
    id: route.params.id,
    type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'APPROVED'),
    note: values.catatan_approval,
    contract_signed: values.contract_signed
  };
  if (!payload.contract_signed) {
    swal.fire({
      icon: 'warning',
      text: "Kontrak lengkap wajib diunggah",
    });
    return
  }


  swal.fire({
    icon: 'warning',
    text: typePar === 'revise' ? 'Revised data?' : (typePar === 'reject' ? 'Rejected data?' : 'Approved data?'),
    showDenyButton: true,
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation/t_extend_kontrak/progress`;
        isRequesting.value = true;
        const res = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
          body: JSON.stringify(payload),
        });




        if (!res.ok) {
          const responseJson = await res.json();
          if ([400, 422, 500].includes(res.status)) {
            formErrors.value = responseJson.errors || {};
            if (res.status === 422) {
              throw new Error(responseJson.message + " Pastikan anda sudah mengisi semua kolom dengan tanda bintang merah");
            }
            throw new Error(responseJson.message || "Failed when trying to post data");
          } else {
            throw new Error("Failed when trying to post data");
          }
        } else {
          // Success case
          swal.fire({
            icon: 'success',
            text: 'Proses berhasil',
          });
          router.replace('/notifikasi');
        }
      } catch (err) {
        isBadForm.value = true;
        swal.fire({
          icon: 'error',
          text: err || 'Failed when trying to post data',
        });
      } finally {
        isRequesting.value = false;
      }
    }
  });

  if (route.params.id === 'create') {
    activeTabIndex = 0;
  }
}

const addKontrak = async () => {
  var tempObj = {}
  // values.id = ++_idKel
  // for (const key in values) {
  //   if (key !== 'desc') {
  //     if (values[key] == null) {
  //       tempObj[key] = ['Bidang ini wajib diisi']
  //     }
  //   }
  // }
  // console.log('ini detailKont',detailKont.value)
  if (Object.keys(tempObj).length >= 1) {
    formErrors.value = tempObj
    swal.fire({
      icon: 'error',
      text: 'Masih ada field yang belum terisi'
    })
    return
  }

  detailKont.value = detailKont.value.map(item => ({
    ...item,
    status: item.status === false || item.status === 0
  }))

  // const detKont = detKont.value.filter(item => item.status)

  // console.log('ini detail kont',detailKont)

  detailKont.value = [...detailKont.value, { ...values }]
  if (values.status === true || values.status === 1) {
    values.kontrak = [...(values.kontrak || []), values.tgl_akhir]
  }
  Object.keys(values).forEach(key => values[key] = null)
  formErrors.value = {}
}


onBeforeMount(async () => {
  const IsKary = route.query.IsKary
  try {
    if (IsKary) {
      const params = new URLSearchParams({
        simplest: true,
        where: `this.m_karyawan_id = ${IsKary}`
      })
      const dataURL = `${store.server.url_backend}/operation/m_kary_det_kontrak?${params.toString()}`
      const res = await fetch(dataURL, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      if (!res.ok) throw new Error('Gagal mengambil detail kontrak')
      const result = await res.json()
      values.m_kary_det_kontrak_id = result.data[0]?.id ?? null
      detailKont.value = result.data.map((v, i) => ({ ...v, _id: i + 1 }))
    }

    if (isRead) {
      try {
        const editedId = route.params.id
        const baseURL = store.server.url_backend
        const params = { join: false, transform: false }
        const fixedParams = new URLSearchParams(params)
        let dataURL = ''
        let dataURLAprv = ''
        let resAprv = ''
        if (route.query.is_approval) {
          dataURLAprv = `${store.server.url_backend}/operation/t_extend_kontrak/detail?id=${route.params.id}`
          isRequesting.value = true
          const apiApp = await fetch(dataURLAprv, {
            headers: {
              'Content-Type': 'Application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            },
          })
          const resultJson = await apiApp.json()
          console.log(resultJson.data.trx)
          const apiTrx = await fetch(`${store.server.url_backend}/operation/${endpointApi}/${resultJson.data.approval.trx_id}`, {
            headers: {
              'Content-Type': 'Application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            },
          })
          if (!apiTrx.ok || !apiApp.ok) throw new Error("Failed when trying to read data")
          const resultTrxJson = await apiTrx.json()
          // console.log('kontol')
          values.interval = resultJson?.data.approval
          values.approval = resultJson?.data.approval
          values.trx = resultJson?.data.trx
          values.datalog = resultJson?.data.approval_log
          initialValues = resultTrxJson.data
          // console.log('bang', initialValues)

          Object.assign(values, resultTrxJson.data)


          const karyId = initialValues.m_karyawan_id
          if (karyId) {
            const dataDet = `${baseURL}/operation/m_kary_det_kontrak?where=this.m_karyawan_id=${karyId}`
            const resp = await fetch(dataDet, {
              headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              }
            })
            if (!resp.ok) throw new Error('Failed when trying to read kontrak detail')
            const resultData = await resp.json()
            detailKont.value = resultData.data || []
            // console.log(detailKont.value)
          }
          // initialValues.m_kary_id = 1

          // console.log('bang', resultTrxJson)
          // initialValues.m_karyawan_id = resultTrxJson.data.trx

          // logic finish & Approved data
          isApproved.value = resultTrxJson?.data?.cuti_status == 'APPROVED' ? true : false
          isFinish.value = resultJson?.data?.approval?.tahap_saat_ini == resultJson?.data?.approval?.tahap_total ? true : false
        } else {
          const editedId = route.params.id
          const baseURL = store.server.url_backend
          const params = { join: false, transform: false }
          const fixedParams = new URLSearchParams(params)

          isRequesting.value = true

          const dataURL = `${baseURL}/operation/${endpointApi}/${editedId}`
          const res = await fetch(`${dataURL}?${fixedParams}`, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            }
          })
          if (!res.ok) throw new Error('Failed when trying to read data')
          const resultJson = await res.json()

          initialValues = resultJson.data
          console.log('bang', resultJson)
          initialValues.is_active = initialValues.is_active ? 1 : 0

          const sortedData = (resultJson.data?.generate_num_det ?? []).sort((a, b) => a.seq - b.seq)

          const karyId = initialValues.m_karyawan_id
          if (karyId) {
            const dataDet = `${baseURL}/operation/m_kary_det_kontrak?where=this.m_karyawan_id=${karyId}`
            const resp = await fetch(dataDet, {
              headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              }
            })
            if (!resp.ok) throw new Error('Failed when trying to read kontrak detail')
            const resultData = await resp.json()
            detailKont.value = resultData.data || []
            console.log(detailKont.value)
          }

          // Jika format, isi data detail transaksi
          if (route.query.type?.toLowerCase() === 'format') {
            trx_dtl.items = sortedData
            trx_dtl.items?.forEach((v, i) => {
              if (actionText.value?.toLowerCase() === 'copy' && v.uid) delete v.uid
              v._id = ++_id.value
            })
          }

          if (actionText.value?.toLowerCase() === 'copy') delete initialValues.uid

          for (const key in initialValues) values[key] = initialValues[key]
        }
      } catch (err) {
        console.error(err)
        swal.fire({
          icon: 'error',
          text: err.message || err,
          allowOutsideClick: false,
          confirmButtonText: 'Kembali'
        }).then(() => router.back())
      } finally {
        isRequesting.value = false
      }
    }

  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err.message || err,
      allowOutsideClick: false,
      confirmButtonText: 'Kembali'
    }).then(() => {
      router.back()
    })
  } finally {
    isRequesting.value = false
  }
})


function onBack() {
  router.replace('/' + modulPath)
}

async function onSave() {
  const result = await swal.fire({
    icon: 'warning',
    text: 'Simpan data?',
    showDenyButton: true,
  });

  if (!values.m_dir_id) {
    await swal.fire({
      icon: 'warning',
      text: 'Unit belum dipilih!'
    });
    return;
  }

  if (!values.m_divisi_id) {
    await swal.fire({
      icon: 'warning',
      text: 'Jabatan belum dipilih!'
    });
    return;
  }

  if (!values.tipe_karyawan_id) {
    await swal.fire({
      icon: 'warning',
      text: 'Tipe Karyawan belum dipilih!'
    });
    return;
  }

  if (!result.isConfirmed) return;
  try {
    // Inti onSave
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;
    isRequesting.value = true;
    values.is_active = values.is_active ? 1 : 0
    values.generate_num_det = trx_dtl.items.map((dt, key) => ({ ...dt, seq: key + 1 }))
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    });
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json();
        formErrors.value = responseJson.errors || {};
        throw (responseJson.errors.length ? responseJson.errors[0] : responseJson.message || "Oops, sesuatu yang salah terjadi. Coba kembali nanti.");
      } else {
        throw ("Oops, sesuatu yang salah terjadi. Coba kembali nanti.");
      }
    }
    router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())));
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'warning',
      text: err
    });
  }
  isRequesting.value = false;
}

//  @else----------------------- LANDING
const activeBtn = ref()
const endpointApiLanding = computed(() => activeTabIndex.value === 0 ? '' : 'generate_num')
const now = new Date()
const periode = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`

var optGroup = []
var optStatus = []

onBeforeMount(async () => {
  try {
    const dataUrl = `${store.server.url_backend}/operation/${endpointApi}`
    const params = {
      simplest: true,
    }
    const fixedParams = new URLSearchParams(params)
    const res = await fetch(dataUrl + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })

    const resultData = await res.json();
    console.log('test status', resultData.data)
    if (resultData.data.length > 0) {
      resultData.data?.forEach((item) => {
        optStatus.push(item.status)
        console.log('opt status', item.status)
      })
    }
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'warning',
      text: err,
      allowOutsideClick: false
    })
    isRequesting.value = false
  }
})

onBeforeMount(async () => {
  try {
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}`
    const params = {
      simplest: true,
    }
    const fixedParams = new URLSearchParams(params)
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    const resultJson = await res.json();
    console.log('res', resultJson.data)
    if (resultJson.data.length > 0) {
      resultJson.data?.forEach((item) => {
        optGroup.push(item['tipe_karyawan.value'])
        console.log(optGroup)
      })
    }
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'warning',
      text: err,
      allowOutsideClick: false,
    })
  }
  isRequesting.value = false
})

const downloadExcel = async () => {
  try {
    const res = await fetch(`${store.server.url_backend}/public/t_extend_kontrak/exportExtendKontrak`)
    if (!res.ok) throw new Error('Gagal mengunduh file')
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'data perpanjangan kontrak.xlsx'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Gagal download Excel:', err)
  }
}




window.handleCreate = (id) => {
  if (!id) return
  const ts = Date.now()
  const newRoute = `${route.path}/create?IsKary=${id}&ts=${ts}`
  router.push(newRoute)
}

function changeTab(index) {
  activeTabIndex.value = index
  activeBtn.value = null
  apiTable.value.reload()
  apiTable2.value.reload()
}

function filterShowData(status, noBtn) {
  if (activeBtn.value === noBtn) {
    activeBtn.value = null
    landing.api.params.where = null
  } else {
    activeBtn.value = noBtn
    landing.api.params.where = `this.status='${status.toUpperCase()}'`
  }
  apiTable.value.reload()
}

// function updateTanggal(tipe, value) {
//   if (tipe === 'month') {
//     // month.value = value
//     landingKaryEx.api.params.month = value
//   }

//   apiTable.value.reload()
// }
function updateTanggal(tipe, value) {
  if (tipe === 'start_month') {
    landingKaryEx.api.params.start_month = value
  }

  if (tipe === 'end_month') {
    landingKaryEx.api.params.end_month = value
  }

  // ambil dua value sekarang
  const start = landingKaryEx.api.params.start_month
  const end = landingKaryEx.api.params.end_month

  // Jika keduanya sudah ada → reload
  if (start && end) {
    apiTable.value.reload()
  }
}


const downloadExcelKont = async () => {
  try {
    const payload = {
      start_month: landingKaryEx.api.params.start_month ?? null,
      end_month: landingKaryEx.api.params.end_month ?? null
    }

    const res = await fetch(
      `${store.server.url_backend}/public/m_kary_det_kontrak/exportEndKontrak`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      }
    )

    if (!res.ok) throw new Error('Gagal mengunduh file')

    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'data perpanjangan kontrak.xlsx'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Gagal download Excel:', err)
  }
}



const landingKaryEx = reactive({
  api: {
    url: `${store.server.url_backend}/operation/m_kary_det_kontrak`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      periode,
      month: '',
      scopes: 'EndKontrak',
      simplest: true,
      searchfield: 'm_kary.nama_lengkap',
    },
    // params: () => ({
    //   periode,
    //   month: month.value,
    //   scopes:'EndKontrak',
    //   simplest: true,
    //   searchfield: 'this.id, this.name, this.ref_type, this.value, this.is_active',
    // }),
    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  },
  columns: [{
    headerName: 'No',
    valueGetter: (params) => params.node.rowIndex + 1,
    width: 60,
    sortable: true,
    resizable: true,
    filter: true,
    cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'm_kary.nama_lengkap',
    headerName: 'Nama Karyawan',
    filter: true,
    sortable: true,
    flex: 3,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Unit',
    field: 'm_dir.nama',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 2,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Tipe',
    field: 'tipe_karyawan',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    field: 'tgl_awal',
    headerName: 'Tanggal Awal',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    valueFormatter: (params) => {
      const v = params.value
      if (v === null || v === undefined || v === '') return ''
      const s = String(v).replace(/\\\//g, '/').trim()
      let d = null
      if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
        d = new Date(s)
      } else if (/^\d{2}\/\d{2}\/\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('/')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d{2}-\d{2}-\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('-')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d+$/.test(s)) {
        d = new Date(Number(s))
      } else {
        d = new Date(s)
      }
      if (!d || isNaN(d.getTime())) return ''
      const day = String(d.getDate()).padStart(2, '0')
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const year = d.getFullYear()
      return `${day}-${month}-${year}`
    }
  },
  {
    field: 'tgl_akhir',
    headerName: 'Tanggal Akhir',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    valueFormatter: (params) => {
      const v = params.value
      if (v === null || v === undefined || v === '') return ''
      const s = String(v).replace(/\\\//g, '/').trim()
      let d = null
      if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
        d = new Date(s)
      } else if (/^\d{2}\/\d{2}\/\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('/')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d{2}-\d{2}-\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('-')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d+$/.test(s)) {
        d = new Date(Number(s))
      } else {
        d = new Date(s)
      }
      if (!d || isNaN(d.getTime())) return ''
      const day = String(d.getDate()).padStart(2, '0')
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const year = d.getFullYear()
      return `${day}-${month}-${year}`
    }
  },
  {
    headerName: 'Aksi',
    field: 'id',
    flex: 1,
    cellClass: ['justify-center'],
    cellRenderer: (params) => {
      let id = params.data?.m_karyawan_id ?? ''
      id = String(id)
      const safeId = id.replace(/'/g, "\\'")
      return `<button
      class="bg-blue-500 text-white rounded px-3 py-1 text-xs font-medium hover:bg-blue-600"
      onclick="window.handleCreate('${safeId}')"
    >Update</button>`
    }
  }
  ]
})


const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      show: (row) => row.status?.toUpperCase() !== 'APPROVED' && row.status?.toUpperCase() !== 'POSTED' && row.status?.toUpperCase() !== 'COMPLETED' && row.status?.toUpperCase() !== 'IN APPROVAL',
      title: "Hapus",
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus Data Terpilih?',
          confirmButtonText: 'Yes',
          showDenyButton: true,
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation/t_extend_kontrak/${row.id}`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                }
              })
              if (!res.ok) {
                if ([400, 422].includes(res.status)) {
                  const responseJson = await res.json()
                  formErrors.value = responseJson.errors || {}
                  throw new Error(responseJson.message || "Failed when trying to post data")
                } else {
                  throw new Error("Failed when trying to post data")
                }
              }
              apiTable2.value.reload()
              // const resultJson = await res.json()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                text: err
              })
            }
            isRequesting.value = false
          }
        })
      }
    },
    {
      icon: 'eye',
      title: "Read",
      class: 'bg-green-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?type=format&` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => row.status?.toUpperCase() !== 'APPROVED' && row.status?.toUpperCase() !== 'POSTED' && row.status?.toUpperCase() !== 'COMPLETED' && row.status?.toUpperCase() !== 'IN APPROVAL',
      click(row) {
        router.push(`${route.path}/${row.id}?type=format&action=Edit&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Post Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row.status?.toUpperCase() === 'DRAFT',
      async click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Post Data?',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',

          showDenyButton: true
        }).then(async (res) => {
          if (res.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation/t_extend_kontrak/posted`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'POST',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: JSON.stringify({ id: row.id })
              })
              if (!res.ok) {
                if ([400, 422, 500].includes(res.status)) {
                  const responseJson = await res.json()
                  formErrors.value = responseJson.errors || {}
                  throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
                } else {
                  throw ("Failed when trying to post data")
                }
              }
              const responseJson = await res.json()
              swal.fire({
                icon: 'success',
                text: responseJson.message
              })
              // const resultJson = await res.json()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                iconColor: '#1469AE',
                confirmButtonColor: '#1469AE',
                text: err
              })
            }
            isRequesting.value = false

            apiTable2.value.reload()
          }
        })
      }
    },
    {
      icon: 'location-arrow',
      title: "Complete Process",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row.status?.toUpperCase() === 'APPROVED',
      async click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Complete Process?',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',

          showDenyButton: true
        }).then(async (res) => {
          if (res.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation/t_extend_kontrak/complete`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'POST',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: JSON.stringify({ id: row.id })
              })
              if (!res.ok) {
                if ([400, 422, 500].includes(res.status)) {
                  const responseJson = await res.json()
                  formErrors.value = responseJson.errors || {}
                  throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
                } else {
                  throw ("Failed when trying to post data")
                }
              }
              const responseJson = await res.json()
              swal.fire({
                icon: 'success',
                text: responseJson.message
              })
              // const resultJson = await res.json()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                iconColor: '#1469AE',
                confirmButtonColor: '#1469AE',
                text: err
              })
            }
            isRequesting.value = false

            apiTable2.value.reload()
          }
        })
      }
    },
    {
      icon: 'location-arrow',
      title: "Send Approval",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => {
        const status = row.status?.toUpperCase()
        return ['POSTED', 'REVISED'].includes(status)
      },

      async click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Send Approval?',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',
          showDenyButton: true
        }).then(async (res) => {
          if (res.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation/t_extend_kontrak/send_approval`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'POST',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: JSON.stringify({ id: row.id })
              })
              if (!res.ok) {
                if ([400, 422, 500].includes(res.status)) {
                  const responseJson = await res.json()
                  formErrors.value = responseJson.errors || {}
                  throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
                } else {
                  throw ("Failed when trying to post data")
                }
              }
              const responseJson = await res.json()
              swal.fire({
                icon: 'success',
                text: responseJson.message
              })
              // const resultJson = await res.json()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                iconColor: '#1469AE',
                confirmButtonColor: '#1469AE',
                text: err
              })
            }
            isRequesting.value = false

            apiTable2.value.reload()
          }
        })
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation/t_extend_kontrak`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      searchfield: 'this.id, this.nomor, m_dir.nama, m_karyawan.nama_depan, tipe_karyawan.value, this.tgl_awal, this.tgl_akhir',
    },
    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  },
  columns: [{
    headerName: 'No',
    valueGetter: (params) => params.node.rowIndex + 1,
    width: 60,
    sortable: true,
    resizable: true,
    filter: true,
    cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'nomor',
    headerName: 'Nomor',
    filter: true,
    sortable: true,
    flex: 2,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    field: 'm_karyawan.nama_depan',
    headerName: 'Nama Karyawan',
    filter: true,
    sortable: true,
    flex: 3,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    field: 'm_dir.nama',
    headerName: 'Unit',
    filter: true,
    sortable: true,
    flex: 2,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Tipe',
    field: 'tipe_karyawan.value',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    // filterParams: {
    //   options: optGroup
    // },
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    field: 'tgl_awal',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    valueFormatter: (params) => {
      const v = params.value
      if (v === null || v === undefined || v === '') return ''
      const s = String(v).replace(/\\\//g, '/').trim()
      let d = null
      if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
        d = new Date(s)
      } else if (/^\d{2}\/\d{2}\/\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('/')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d{2}-\d{2}-\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('-')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d+$/.test(s)) {
        d = new Date(Number(s))
      } else {
        d = new Date(s)
      }
      if (!d || isNaN(d.getTime())) return ''
      const day = String(d.getDate()).padStart(2, '0')
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const year = d.getFullYear()
      return `${day}-${month}-${year}`
    }
  },
  {
    field: 'tgl_akhir',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    valueFormatter: (params) => {
      const v = params.value
      if (v === null || v === undefined || v === '') return ''
      const s = String(v).replace(/\\\//g, '/').trim()
      let d = null
      if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
        d = new Date(s)
      } else if (/^\d{2}\/\d{2}\/\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('/')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d{2}-\d{2}-\d{4}$/.test(s)) {
        const [dd, mm, yyyy] = s.split('-')
        d = new Date(`${yyyy}-${mm}-${dd}`)
      } else if (/^\d+$/.test(s)) {
        d = new Date(Number(s))
      } else {
        d = new Date(s)
      }
      if (!d || isNaN(d.getTime())) return ''
      const day = String(d.getDate()).padStart(2, '0')
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const year = d.getFullYear()
      return `${day}-${month}-${year}`
    }
  },
  {
    headerName: 'Status',
    field: 'status',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    filterParams: {
      options: optStatus
    },
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      let color = 'gray'
      if (value == 'DRAFT')
        color = 'green'
      else if (value == 'POSTED')
        color = 'blue'
      else if (value == 'SIGNED')
        color = 'yellow'
      else if (value == 'COMPLETED')
        color = 'red'
      return `<span class="text-${color}-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
    }
  }
  ]
})

onActivated(() => {
  //  reload table api landing
  if (apiTable.value) {
    if (route.query.reload) {
      apiTable.value.reload()
    }
  }
})

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))//   javascript