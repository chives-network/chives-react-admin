
// ** Hooks
import UserList from '../Enginee/index'

const AppChat = () => {
  // ** States
  const backEndApi = "form_datacollection.php"
  
  return (
    <UserList backEndApi={backEndApi} externalId=''/>
  )
}


export default AppChat
