import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, watch, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated, } from 'vue'


const router = useRouter()
const route = useRoute()
const store = inject('store')
console.log('cek admin',store.user.data.is_superadmin)
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
const is_approval = route.query.is_approval ? true : false
const is_to_upload = route.query.is_to_upload ? true : false
let isApproved = ref(false)
let modalOpen = ref(false)
let isFinish = ref(false)
const is_superadmin = ref(false);
// ------------------------------ PERSIAPAN
const endpointApi = '/t_cuti'
onBeforeMount(() => {
  document.title = is_approval ? 'Approval Cuti' : 'Transaksi Cuti'
  is_superadmin.value = store.user.data?.is_superadmin ?? false
  console.log(store.user.data?.is_superadmin)
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
})
const totalDays = ref();


watchEffect(() => {
  if (values.date_from && values.date_to && values.date_from !== '' && values.date_to !== '') {
    const dateFrom = new Date(values.date_from);
    const dateTo = new Date(values.date_to);

    if (!isNaN(dateFrom.getTime()) && !isNaN(dateTo.getTime())) {
      const timeDifference = dateTo.getTime() - dateFrom.getTime();
      const daysDifference = Math.floor(timeDifference / (1000 * 3600 * 24));

      totalDays.value = daysDifference;
    } else {
      totalDays.value = 'error';
    }
  } else {
    totalDays.value = 0;
  }
});

function toApiFormat(dateString) {
  if (!dateString) return ''
  const [day, month, year] = dateString.split(/[\/-]/)
  return `${year}-${month}-${day}`
}

function toUiFormat(dateString) {
  if (!dateString) return ''
  const [year, month, day] = dateString.split(/[-\/]/)
  return `${day}/${month}/${year}`
}


const onDateChange = async (v) => {
  values.date_from = v

  const params = new URLSearchParams({
    m_kary_id: values.m_kary_id,
    alasan_id: values.alasan_id,
    total_bulan: values.total_bulan,
    tgl_awal: toApiFormat(v)
  })

  const dataURL = `${store.server.url_backend}/operation/t_cuti/duration?${params}`
  isRequesting.value = true

  try {
    const apiRes = await fetch(dataURL, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })

    const textRes = await apiRes.text()
    console.log('Raw Res: ', textRes)

    values.date_to = toUiFormat(textRes.trim())
  } catch (err) {
    console.error(err)
  } finally {
    isRequesting.value = false
  }
}



function formatDate1(date) {
  const formattedDate = date.split('/')
  if (formattedDate.length > 1) {
    const year = formattedDate[2]
    const month = formattedDate[1]
    const day = formattedDate[0]
    return `${day}-${month}-${year}`
  } else {
    return date
  }
}


onBeforeMount(async () => {
  // tampilkan default direktorat dengan store user comp.nama
  values.direktorat = store.user.data?.direktorat


  if (isRead) {
    //  READ DATA
    try {
      let dataURL = ''
      let dataURLAprv = ''
      let resAprv = ''
      if (route.query.is_approval) {
        dataURLAprv = `${store.server.url_backend}/operation/t_cuti/detail?id=${route.params.id}`
        isRequesting.value = true
        const apiApp = await fetch(dataURLAprv, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        const resultJson = await apiApp.json()
        console.log(resultJson.data)
        const apiTrx = await fetch(`${store.server.url_backend}/operation${endpointApi}/${resultJson.data.approval.trx_id}`, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        if (!apiTrx.ok || !apiApp.ok) throw new Error("Failed when trying to read data")
        const resultTrxJson = await apiTrx.json()
        values.interval = resultJson?.data.approval
        values.approval = resultJson?.data.approval
        values.trx = resultJson?.data.trx
        values.datalog = resultJson?.data.approval_log
        initialValues = resultTrxJson.data

        // console.log('bang',resultTrxJson)

        // logic finish & Approved data
        isApproved.value = resultTrxJson?.data?.cuti_status == 'APPROVED' ? true : false
        isFinish.value = resultJson?.data?.approval?.tahap_saat_ini == resultJson?.data?.approval?.tahap_total ? true : false
      } else {
        const editedId = route.params.id
        dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
        isRequesting.value = true
        const params = { join: true, transform: false }
        const fixedParams = new URLSearchParams(params)
        const res = await fetch(dataURL + '?' + fixedParams, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        if (!res.ok) throw new Error("Failed when trying to read data")
        const resultJson = await res.json()
        initialValues = resultJson.data

        initialValues.date_from = formatDate1(initialValues.date_from)
        initialValues.date_to = formatDate1(initialValues.date_to)
        // logic Approved data
        isApproved.value = resultJson?.data?.cuti_status == 'APPROVED' ? true : false
      }
    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      }).then(() => {
        router.back()
      })
    }
    isRequesting.value = false
  }

  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
})

onMounted(() => {
  if (!is_superadmin.value) {
    values.m_kary_id = store.user.data?.m_kary_id
  }
})

async function downloadDoc() {
  window.open(`${store.server.url_backend}/operation/t_cuti/cuti?id=${values.t_cuti_id}`)
}

function onBack() {
  if (!is_approval) {
    router.replace('/' + modulPath)
  } else {
    router.replace('/notifikasi')
  }
  return
}

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Reset this form data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    }
  })
}

function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
}

function closeModal(i) {
  dataLog.items = []
  modalOpen.value = false
}

let dataLog = reactive({ items: [] })
async function loadLog(id) {
  const url = `${store.server.url_backend}/operation/t_cuti/log?id=${id}`
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

function formatDate(date) {
  const formattedDate = date.split('/')
  if (formattedDate.length > 1) {
    const year = formattedDate[2]
    const month = formattedDate[1]
    const day = formattedDate[0]
    return `${day}-${month}-${year}`
  } else {
    return date
  }
}

// function onSave() {
//   try {
//     // Format Tanggal Awal
//     // values.date_to = formatDate(values.date_to)
//     // Format Tanggal Akhir
//     // if (values.tipe_string?.toLowerCase() === 'p24') {
//     //   values.date_to = values.date_from
//     // } else {
//     //   values.date_to = formatDate(values.date_to)
//     // }
//     if (values.date_from && values.date_to && values.date_to < values.date_from) {
//       isBadForm.value = true;
//       swal.fire({
//         icon: 'error',
//         text: 'Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal',
//       });
//       return;
//     }

//      if (!values.attachment) {
//       swal.fire({
//         icon: 'error',
//         text: 'Wajib Melampirkan Surat'
//       })
//       return
//     }

//     const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);

//     if (values.status === 'REVISED') {
//       values.status = 'DRAFT'; // Change status to DRAFT if it was REVISED
//     }

//     const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;

//     isRequesting.value = true;

//     fetch(dataURL, {
//       method: isCreating ? 'POST' : 'PUT',
//       headers: {
//         'Content-Type': 'Application/json',
//         Authorization: `${store.user.token_type} ${store.user.token}`,
//       },
//       body: JSON.stringify(values),
//     })
//       .then(async (res) => {
//         if (!res.ok) {
//           if ([400, 422].includes(res.status)) {
//             const responseJson = await res.json();
//             formErrors.value = responseJson.errors || {};
//             throw new Error(responseJson.message || 'Failed when trying to post data');
//           } else {
//             throw new Error('Failed when trying to post data');
//           }
//         }
//         router.replace('/' + modulPath + '?reload=' + Date.parse(new Date()));
//       })
//       .catch((err) => {
//         isBadForm.value = true;
//         swal.fire({
//           icon: 'error',
//           text: 'Harap Lengkapi Data',
//         });
//       })
//       .finally(() => {
//         isRequesting.value = false;
//       });
//   } catch (err) {
//     isBadForm.value = true;
//     swal.fire({
//       icon: 'error',
//       text: 'Harap Lengkapi Data',
//     });
//     isRequesting.value = false;
//   }
// }

async function onSave() {
  try {
    // 1. Validasi Client-side: Tanggal
    if (values.date_from && values.date_to && values.date_to < values.date_from) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: 'Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal'
      })
      return
    }

    // 2. Validasi Client-side: Lampiran
    if (!values.attachment) {
      swal.fire({
        icon: 'error',
        text: 'Wajib Melampirkan Surat'
      })
      return
    }

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)

    const payload = {
      ...values,
      date_from: toApiFormat(values.date_from),
      date_to: toApiFormat(values.date_to)
    }

    if (payload.status === 'REVISED') {
      payload.status = 'DRAFT'
    }

    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`

    isRequesting.value = true
    formErrors.value = {} // Reset error per field

    // 3. Eksekusi Request
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    })

    const responseJson = await res.json()

    // 4. Cek Jika Response Tidak OK (Error 400, 422, 500, dll)
    if (!res.ok) {
      let errorMessage = responseJson.message || 'Gagal memproses data'

      if (responseJson.errors) {
        if (typeof responseJson.errors === 'string') {
          // Kasus: "Batas pengajuan dispensasi..." (String)
          errorMessage = responseJson.errors
        } else {
          // Kasus: Validation error dari Laravel (Object)
          formErrors.value = responseJson.errors
          errorMessage = Object.values(responseJson.errors).flat().join(', ')
        }
      }
      
      // Lempar error ke blok catch di bawah
      throw new Error(errorMessage)
    }

    // 5. Berhasil: Tampilkan Sukses dan Redirect
    swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Data telah disimpan',
      timer: 1500,
      showConfirmButton: false
    }).then(() => {
      router.replace('/' + modulPath + '?reload=' + Date.now())
    })

  } catch (err) {
    // 6. Tangani Segala Error (dari fetch maupun throw manual)
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: err.message || 'Terjadi kesalahan sistem' // Menampilkan pesan error asli dari backend
    })
  } finally {
    isRequesting.value = false
  }
}


function onProcess(typePar) {
  const payload = {
    id: route.params.id,
    type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'APPROVED'),
    note: values.catatan,
  };
  // if(!payload.note) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: "Catatan wajib diisi",
  //   });
  //   return  
  // }


  swal.fire({
    icon: 'warning',
    text: typePar === 'revise' ? 'Revised data?' : (typePar === 'reject' ? 'Rejected data?' : 'Approved data?'),
    showDenyButton: true,
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation/t_cuti/progress`;
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


//  @else----------------------- LANDING
// LANDING LAMA 

const downloadExcel = async () => {
  try {
    const res = await fetch('https://msc.qqltech.com:7169/public/t_cuti/exportCuti')
    if (!res.ok) throw new Error('Gagal mengunduh file')
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'data cuti.xlsx'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Gagal download Excel:', err)
  }
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      show: (row) => row.status?.toUpperCase() === 'DRAFT',
      title: "Hapus",
      // show: () => store.user.data.username==='developer',
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
              if (!res.ok) throw new Error("Failed when trying to remove data")
              apiTable.value.reload()
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
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => row.status?.toUpperCase() === 'DRAFT' || row.status?.toUpperCase() === 'REVISED' || (row.status?.toUpperCase() === 'IN APPROVAL' && store.user.data?.is_superadmin),
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId);
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      show: (row) => row.status?.toUpperCase() === 'DRAFT',
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
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
              const dataURL = `${store.server.url_backend}/operation/t_cuti/posted`
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

            apiTable.value.reload()
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
              const dataURL = `${store.server.url_backend}/operation/t_cuti/send_approval`
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

            apiTable.value.reload()
          }
        })
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
      searchfield: 'm_kary.nama_lengkap, alasan.value, tipe_cuti.value, date_from, date_to, status',
      where: `m_kary.m_dir_id=${store.user.data.m_dir_id}`
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
    cellClass: ['justify-left', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'nomor',
    headerName: 'Nomor',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_kary.nama_lengkap',
    headerName: 'Nama Karyawan',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'alasan.value',
    headerName: 'Alasan',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  // {
  //   field: 'tipe_cuti.value',
  //   headerName: 'Tipe Cuti',
  //   filter: true,
  //   sortable: true,
  //   flex: 1,
  //   filter: 'ColFilter',
  //   resizable: true,
  //   cellClass: ['border-r', '!border-gray-200', 'justify-left']
  // },
  {
    field: 'date_from',
    headerName: 'Tanggal Awal',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'date_to',
    headerName: 'Tanggal Akhir',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    headerName: 'Status',
    field: 'status',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      let color = 'gray'
      if (value == 'APPROVED')
        color = 'green'
      else if (value == 'IN APPROVAL')
        color = 'blue'
      else if (value == 'REVISED')
        color = 'yellow'
      else if (value == 'REJECTED')
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
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))