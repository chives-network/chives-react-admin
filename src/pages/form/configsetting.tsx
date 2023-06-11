import { useRouter } from 'next/router'


// ** Hooks
import UserList from '../Enginee/index'

const AppChat = () => {
  // ** States
  const backEndApi = "form_configsetting.php"
  const router = useRouter()
  const _GET = router.query
  const FlowId = String(_GET['FlowId'])
  if (FlowId != undefined) {
    return (
      <UserList backEndApi={backEndApi} externalId={FlowId}/>
    )
  }
  else {
    return (
      <UserList backEndApi={backEndApi} externalId='0'/>
    )
  }

}


export default AppChat
