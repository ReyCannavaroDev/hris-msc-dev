import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watch, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')
console.log('ini console store', store.user.data)

// const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const formErrorsDetail = ref({})
const formErrorsDetailPengembalian = ref({})
const tableKey = ref(0)
const tableKeyPengembalian = ref(0)
var activeTabIndex = ref(0)
let modalOpen = ref(false)
let modalOpenCreate = ref(false)
const tsId = `ts=` + (Date.parse(new Date()))
const is_approval = route.query.is_approval ? true : false
const is_customer = route.query.is_customer ? true : false
let isApproved = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })
// ------------------------------ PERSIAPAN
const endpointApi = 'm_kary'
onBeforeMount(() => {
  document.title = is_approval ? 'Approval Supplier' : 'Master Supplier'
})

const previewSrc = ref(null)

const previewImage = (filePath) => {
  previewSrc.value = filePath
}

const openIjazahPreview = (filePath) => {
  previewSrc.value = filePath; // Set the previewSrc to the filePath
};

const closePreview = () => {
  previewSrc.value = null; // Reset preview source
};
// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
})


const handleKeyDown = (event) => {
  console.log(event)
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}

let initialValues = {}
const changedValues = []

const now = new Date()
const year = now.getFullYear()
const month = String(now.getMonth() + 1).padStart(2, '0')

let values = reactive({
  is_active: 1,
  periode: `${year}-${month}`
})

// DEFAULT VALUE BEFORE MOUNT --UBAH DISINI
const defaultValues = () => {
  values.tanggal = new Date().toLocaleDateString('en-GB')
}


// Table Detail
const detailArr = ref([])
const detailArrKendaraan = ref([])
const karyawan = ref([])
// const onRetotal = (dArr) => {
//   if (dArr) {
//     detailArr.value = dArr

//   }

// }

function onDetailAdd(rows) {
  detailArr.value.push({ seq: 1 })
  console.log(detailArr, 'DETAILLL')
}

function selectKaryawan(item) {
  console.log('ID karyawan:', item.id)
  values.m_karyawan_id = item.id
  values.nama_karyawan = item.nama || item.nama_lengkap || '-'
  
  getDetailKontrak(item.id)
}

async function getDetailKontrak(karyId) {
  try {
    const params = new URLSearchParams({
      where: `m_karyawan_id = ${karyId}`
    });

    const url = `${store.server.url_backend}/operation/m_kary_det_kontrak?${params.toString()}`;
    const res = await fetch(url, {
      headers: {
        Authorization: `${store.user.token_type} ${store.user.token}`,
        'Content-Type': 'application/json'
      }
    });

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const data = await res.json();
    detailArr.value = data.data || [];
  } catch (err) {
    console.error('Gagal mengambil detail kontrak:', err);
    detailArr.value = [];
  }
}

function onDetailAddKendaraan(rows) {
  const nextSeq = detailArrKendaraan.value.length + 1
  detailArrKendaraan.value.push({ seq: nextSeq, is_active: true })
  console.log(detailArrKendaraan.value, 'DETAILLL')
}


const removeDetail = (index) => {
  detailArr.value.splice(index, 1)
  onRetotal()
}

const removeDetailKendaraan = (index) => {
  detailArrKendaraan.value.splice(index, 1)
  onRetotal()
}

const validEmail = (v) => (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v))

const isValidEmail = ref(true)
function validateEmail(v) {
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v)) {
    formErrors.value["email"] = null
    isValidEmail.value = true
  } else {
    formErrors.value["email"] = ['Please enter a valid email address']
    isValidEmail.value = false
  }
  values.email = v
}

onBeforeMount(async () => {
  try {
    const params = new URLSearchParams({
      periode: values.periode || '',
      scope: 'endKontrak'
    })

    const dataURL = `${store.server.url_backend}/operation/m_kary_det_kontrak?${params.toString()}`
    const res = await fetch(dataURL, {
      headers: {
        Authorization: `${store.user.token_type} ${store.user.token}`,
        'Content-Type': 'application/json'
      }
    })

    const data = await res.json()
    karyawan.value = (data.data || []).map(item => ({
      ...item,
      nama: item.nama_lengkap || item.nama || '-',
      tgl_start: item.tgl_awal_kontrak || item.tgl_start || '-',
      tgl_end: item.tgl_akhir_kontrak || item.tgl_end || '-'
    }))
  } catch (err) {
    console.error('Gagal mengambil data karyawan:', err)
    karyawan.value = []
  }
})

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else if (route.query.view_gaji_final) {
    router.replace('/t_info_gaji')
  } else {
    router.replace('/' + modulPath)
  }
  return
}


async function onSave() {
  try {
    let next = true;

    if (!values.tanggal) {
      swal.fire({
        icon: 'warning',
        text: `Tanggal harus diisi`
      });
      next = false;
      return;
    }

    if (!next) return;

    values.m_supp_d_rek = detailArr.value;
    values.m_supp_d_kend = detailArrKendaraan.value;

    const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${store.user.data.m_supp_id}`;
    const res = await fetch(dataURL, {
      method: 'PUT',
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

    await swal.fire({
      icon: 'success',
      text: 'Data berhasil diupdate!',
      confirmButtonText: 'OK'
    }).then(() => {
      router.replace('/' + modulPath + '?reload=' + Date.now());
    });

  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'warning',
      text: err
    });
  }

  isRequesting.value = false;
}



async function onPost() {
  try {
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}/post`;
    isRequesting.value = true;

    const res = await fetch(dataURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({ id: values.id })
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


function onProcess(typePar) {
  let next = true
  if (typePar?.toLowerCase() === 'revise' || typePar?.toLowerCase() === 'reject') {
    if (!values.note_approval) {
      swal.fire({
        icon: 'warning',
        text: `Catatan approval diperlukan!`
      })
      next = false
      return
    }
  }



  if (!next) return

  const payload = {
    id: route.params.id,
    type: typePar,
    note: values.note_approval,
  };

  swal.fire({
    icon: 'warning',
    text: typePar === 'REVISE' ? 'Revised data?' : (typePar === 'REJECT' ? 'Rejected data?' : 'Approved data?'),
    showDenyButton: true,
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation/${endpointApi}/approval`;
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
          router.replace('/notifikasi?reload=true');
        }
      } catch (err) {
        isBadForm.value = true;
        swal.fire({
          icon: 'warning',
          text: err || 'Failed when trying to post data',
        });
      } finally {
        isRequesting.value = false;
      }
    }
  });
}
function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
  console.log(modalOpen.value)
}

function closeModal(i) {
  dataLog.items = []
  modalOpen.value = false
}

async function loadLog(id) {
  const url = `${store.server.url_backend}/operation/t_delivery_plan/log?id=${initialValues.id}`
  const res = await fetch(url, {
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
  })
  if (!res.ok) throw new Error("Failed when trying to read data")
  const result = await res.json()
  dataLog.items = result
}

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))