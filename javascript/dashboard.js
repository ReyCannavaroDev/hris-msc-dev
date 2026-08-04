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
const formErrors = ref({})
const tsId = `ts=`+(Date.parse(new Date()))

const dirSalary = ref([])
const chartData = ref({})
const chartDataLate = ref({})
const chartDataAbsent = ref({})
const chartDataPerfect = ref({})
const totalDivisi = ref(0)
const totalDepartemen = ref(0)
const pegawaiAbsen = ref(0)
const pegawaiMasuk = ref(0)
const topLate = ref([])
const topAbsent = ref([])
const topPerfect = ref([])


// ------------------------------ PERSIAPAN
const endpointApi = '/dashboard'
onBeforeMount(()=>{
  document.title = 'Dashboard'
})
const is_superadmin = ref(false)
const beforeLoad = ref(false)
onMounted(async ()=>{
  beforeLoad.value = true
   try {
      const dataURL = `${store.server.url_backend}/me`
      isRequesting.value = true
      const res = await fetch(dataURL, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      const data = await res.json()
      is_superadmin.value = data?.is_superadmin ?? false
      if(data?.is_superadmin == false){
        router.replace('/presensi_absen_online')
      }

      // is_superadmin.value = data?.user_type ?? 'User'

      const resDash = await fetch(`${store.server.url_backend}/operation/m_dir/dashboard`, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      const dataDash = await resDash.json()
      console.log('FULL dataDash:', dataDash)
      // console.log('datadash div', dataDash?.div_count)

      totalDivisi.value = dataDash?.div_count ?? 0
      // console.log('totalDivisi', totalDivisi);

      totalDepartemen.value = dataDash?.dir_count ?? 0
      pegawaiAbsen.value = dataDash?.total_absen ?? 0
      pegawaiMasuk.value = dataDash?.total_hadir ?? 0



      dirSalary.value = dataDash?.dir_salary || []
      topLate.value = dataDash?.late || []
      topAbsent.value = dataDash?.absent || []
      topPerfect.value = dataDash?.perfect || []

      // subSalary.value = dataDash.subcomp_salary
      // console.log('branchSalary dari API:', subSalary.value)

      if (Array.isArray(dirSalary.value)) {
        chartData.value = Object.fromEntries(
          dirSalary.value.map(item => [item.dir_name, item.total_gaji])
        )
        console.log('chartData hasil:', chartData.value)
      } 

      if (Array.isArray(topLate.value)) {
        chartDataLate.value = Object.fromEntries(
          topLate.value.map(item => [item.nama_lengkap, item.total_late])
        )
        console.log('Cek isi topLate:', topLate.value);
        console.log('Cek isi chartDataLate:', chartDataLate.value);
      } 

       if (Array.isArray(topAbsent.value)) {
        chartDataAbsent.value = Object.fromEntries(
          topAbsent.value.map(item => [item.nama_lengkap, item.total_absent])
        )
        // console.log('Cek isi topLate:', topLate.value);
        // console.log('Cek isi chartDataLate:', chartDataLate.value);
      } 

       if (Array.isArray(topPerfect.value)) {
        chartDataPerfect.value = Object.fromEntries(
          topPerfect.value.map(item => [item.nama_lengkap, item.total_perfect])
        )
        // console.log('Cek isi topLate:', topLate.value);
        // console.log('Cek isi chartDataLate:', chartDataLate.value);
      } 


    } catch (err) {
      beforeLoad.value = false
    }
    beforeLoad.value = false
})
