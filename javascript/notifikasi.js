import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Create' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const currentUser = store.user.data
// ------------------------------ PERSIAPAN

const endpointApi = '/generate_approval/outstanding'
onBeforeMount(()=>{
  document.title = 'Notification'
})

let initialValues = {}
const changedValues = []

let dataLanding = reactive({items:[]})
const dataKontrak = ref([])
const isKontrakOpen = ref(true)
const is_superadmin = ref(false)
const values = reactive({})
let params = { join: false, transform: false, single:true }

onMounted(async () => {
    try {
        const resMe = await fetch(`${store.server.url_backend}/me`, {
            headers: {
                'Content-Type': 'Application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
            }
        });
        const dataMe = await resMe.json();
        is_superadmin.value = dataMe?.is_superadmin ?? false;

        if (is_superadmin.value) {
            const resKontrak = await fetch(`${store.server.url_backend}/operation/m_kary_det_kontrak/notifEndKontrak`, {
                headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                }
            });
            const dataK = await resKontrak.json();
            dataKontrak.value = dataK?.data || [];
        }
    } catch (e) {
        console.error("Error fetching notif kontrak", e);
    }
    
    loadTable();
})

async function loadTable() {
  try {
      // reset landing
      dataLanding.items = []

      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}`
      isRequesting.value = true

      const fixedParams = new URLSearchParams(params)
      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Failed when trying to read data")
      const resultJson = await res.json()
      let data = resultJson.data

      const groupedData = data.reduce((result, item) => {
        const key = item.trx_name;
        if (!result[key]) {
            result[key] = [];
        }
        result[key].push(item);
        return result;
      }, {});

      data = groupedData
      let counter = 0
      for(const item in data){
        let active = false
        if(counter === 0) active = true
        dataLanding.items.push({
          'active' : active,
          'name' : item,
          'data' : data[item]
        })
        counter++
      }
      
    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      }).then(() => {
        
      })
    }
    isRequesting.value = false
    // console.log(dataLanding.items)
}


function openClose(i) {
  const checkIsOpen = dataLanding.items[i].active ? true : false
  dataLanding.items[i].active = !checkIsOpen
}

function search() {
  params = {
    ...params,
    search: values.search
  }
  console.log(params)
  loadTable()
}

function paginate() {
  params = {
    ...params,
    paginate: values.paginate
  }
  loadTable()
}

function detail(i, idx) {
  const dt =  dataLanding.items[i]['data'][idx]
  console.log(dt)
  const url = (dt.form_name ?? dt.trx_table)+`/${dt.id}?is_approval=true`
  return router.replace('/' + url)
}