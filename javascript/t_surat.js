import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isSigning = ref(route.query.action === 'Sign')
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=`+(Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/t_surat'
onBeforeMount(()=>{
  document.title = 'Surat Karyawan (SP, Penghargaan, dll)'
})

let initialValues = {}

const values = reactive({
  is_signed: false,
  jenis_surat: 'PERINGATAN'
})

onBeforeMount(async () => {
  if (isRead) {
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

      const res = await fetch(dataURL, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Failed when trying to read data")
      const resultJson = await res.json()
      initialValues = resultJson.data
      
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err
      }).then(() => {
        router.back()
      })
    }
    isRequesting.value = false
  }
})

// SIGNATURE CANVAS LOGIC
let canvas = null
let ctx = null
let isDrawing = false

onMounted(() => {
  if (isSigning.value && !values.is_signed) {
    setTimeout(() => {
      canvas = document.getElementById('signatureCanvas')
      if(canvas) {
        ctx = canvas.getContext('2d')
        ctx.lineWidth = 2
        ctx.lineCap = 'round'
        ctx.strokeStyle = '#000'

        const startPosition = (e) => {
          isDrawing = true
          draw(e)
        }

        const endPosition = () => {
          isDrawing = false
          ctx.beginPath()
        }

        const draw = (e) => {
          if (!isDrawing) return
          const rect = canvas.getBoundingClientRect()
          let clientX, clientY;

          if (e.type.includes('mouse')) {
             clientX = e.clientX;
             clientY = e.clientY;
          } else if (e.type.includes('touch')) {
             clientX = e.touches[0].clientX;
             clientY = e.touches[0].clientY;
          }

          ctx.lineTo(clientX - rect.left, clientY - rect.top)
          ctx.stroke()
          ctx.beginPath()
          ctx.moveTo(clientX - rect.left, clientY - rect.top)
        }

        canvas.addEventListener('mousedown', startPosition)
        canvas.addEventListener('mouseup', endPosition)
        canvas.addEventListener('mousemove', draw)
        canvas.addEventListener('touchstart', startPosition)
        canvas.addEventListener('touchend', endPosition)
        canvas.addEventListener('touchmove', draw)
      }
    }, 500)
  }
})

function clearSignature() {
  if (canvas && ctx) {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
  }
}

async function submitSignature() {
  if (!canvas) return;
  
  const blankCanvas = document.createElement('canvas');
  blankCanvas.width = canvas.width;
  blankCanvas.height = canvas.height;
  if(canvas.toDataURL() === blankCanvas.toDataURL()) {
      swal.fire({ icon: 'warning', text: 'Tanda tangan masih kosong!' })
      return;
  }

  const dataURL = canvas.toDataURL('image/png')
  
  try {
    const apiURL = `${store.server.url_backend}/operation${endpointApi}/custom_signLetter/${route.params.id}`
    isRequesting.value = true
    const res = await fetch(apiURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({ signature_img: dataURL })
    })
    if (!res.ok) throw ("Gagal menyimpan tanda tangan")
    
    swal.fire({ icon: 'success', text: 'Tanda tangan berhasil disimpan' })
    router.replace('/' + modulPath + '?reload='+(Date.parse(new Date())))
  } catch (err) {
    swal.fire({ icon: 'error', text: err })
  }
  isRequesting.value = false
}

function onBack() {
  router.replace('/' + modulPath)
}

async function onSave() {
  try {
    const isCreating = ['Create','Copy','Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    })
    if (!res.ok) {
        throw ("Gagal menyimpan data")
    }
    router.replace('/' + modulPath + '?reload='+(Date.parse(new Date())))
  } catch (err) {
    isBadForm.value = true
    swal.fire({ icon: 'error', text: err })
  }
  isRequesting.value = false
}

// ----------------------- LANDING
const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => !row.is_signed,
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus Data Terpilih?',
          confirmButtonText: 'Yes',
          showDenyButton: true,
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/${row.id}`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                }
              })
              if (!res.ok) throw new Error("Gagal menghapus")
              apiTable.value.reload()
            } catch (err) {
              swal.fire({ icon: 'error', text: err })
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
        router.push(`${route.path}/${row.id}?`+tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => !row.is_signed,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&`+tsId)
      }
    },
    {
      icon: 'pen',
      title: "Tanda Tangan Karyawan",
      class: 'bg-yellow-500 text-light-100',
      show: (row) => !row.is_signed && row.jenis_surat === 'PERINGATAN',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Sign&`+tsId)
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation${endpointApi}`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      join: true,
      searchfield: 'm_karyawan.nik, m_karyawan.nama_lengkap, this.jenis_surat, this.level_surat, this.tanggal_terbit'
    },
    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  },
  columns: [
    {
      headerName: 'No', valueGetter: (p) => p.node.rowIndex + 1, width: 60,
      cellClass: ['justify-center', 'bg-gray-50']
    },
    { field: 'm_karyawan.nik', headerName: 'NIK', flex: 1 },
    { field: 'm_karyawan.nama_lengkap', headerName: 'Nama Karyawan', flex: 1 },
    { field: 'jenis_surat', headerName: 'Jenis', flex: 1 },
    { field: 'level_surat', headerName: 'Level', flex: 1 },
    { field: 'tanggal_terbit', headerName: 'Tanggal Terbit', flex: 1 },
    { 
      field: 'is_signed', 
      headerName: 'Status TTD', 
      flex: 1,
      cellRenderer: (p) => p.value ? '<span class="text-green-600 font-bold">Sudah</span>' : '<span class="text-red-500">Belum</span>'
    }
  ]
})