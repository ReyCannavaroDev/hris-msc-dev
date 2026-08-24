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
  const tsId = `ts=`+(Date.parse(new Date()))

  // ------------------------------ PERSIAPAN
  onBeforeMount(()=>{
    document.title = 'Laporan Gaji'
  })

  //  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
  
  //  @else----------------------- LANDING
  let initialValues = {}
  const changedValues = []
  const checkedState = ref()

  const values = reactive({
    tipe: 'HTML'
  })

  const onGenerate = async () => {
    if(values.tipe === null){
      swal.fire({
        icon: 'error',
        text: 'Harap Memilih Tipe Eksport Dahulu!',
      })
      return
    }
    if(!values.periode_from || !values.periode_to){
      swal.fire({
        icon: 'error',
        text: 'Harap Memilih Periode Dahulu!',
      })
      return
    }
    const tempGet = []
    isRequesting.value = true
    if(values.tipe){
      if(values.tipe?.toLowerCase() === 'excel'){
        tempGet.push(`export=xls`)
      }else if(values.tipe?.toLowerCase() === 'pdf'){
        tempGet.push(`export=pdf`)
      }
    }
    const parseDateStr = (str) => {
      if (!str) return '';
      const parts = str.split(/[-\/]/);
      if (parts.length === 3) {
        if (parts[0].length === 4) {
          // format: YYYY-MM-DD
          return `${parts[0]}-${parts[1]}-${parts[2]}`;
        }
        // format: DD/MM/YYYY
        return `${parts[2]}-${parts[1]}-${parts[0]}`;
      }
      return str;
    }

    if(values.periode_from){
      tempGet.push(`periode_from=${parseDateStr(values.periode_from)}`)
    }
    if(values.periode_to){
      tempGet.push(`periode_to=${parseDateStr(values.periode_to)}`)
    }
    if(values.m_dir_id){
      tempGet.push(`m_dir_id=${values.m_dir_id}`)
    }
    if(values.m_divisi_id){
      tempGet.push(`m_divisi_id=${values.m_divisi_id}`)
    }
    if(values.m_kary_id){
      tempGet.push(`m_kary_id=${values.m_kary_id}`)
    }
    if(values.tgl_masuk_from){
      tempGet.push(`tgl_masuk_from=${parseDateStr(values.tgl_masuk_from)}`)
    }
    if(values.tgl_masuk_to){
      tempGet.push(`tgl_masuk_to=${parseDateStr(values.tgl_masuk_to)}`)
    }
    if(values.tunjangan_filter){
      tempGet.push(`tunjangan_filter=${encodeURIComponent(values.tunjangan_filter)}`)
    }
    const paramsGet = tempGet.join("&")
    if(values.tipe?.toLowerCase() !== 'html'){
      exportHtml.value = false
      window.open(`${store.server.url_backend}/web/report_rekap_gaji` + '?' + paramsGet)
    }else{
      await fetch(`${store.server.url_backend}/web/report_rekap_gaji` + '?' + paramsGet, {
        headers: {
            'Content-Type': 'html',
          },
      })
      .then(response => response.text())
      .then(html => {
        exportHtml.value = true
        const tempDiv = document.createElement('div')
        tempDiv.innerHTML = html
        const targetDiv = document.getElementById('exportTable')
        targetDiv.innerHTML = ''
        targetDiv.appendChild(tempDiv)
      })
      .catch(error => {
        swal.fire({
          icon: 'error',
          text: error,
        })
      })
    }
    
    isRequesting.value = false
  }

  //  @endif -------------------------------------------------END
  watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))