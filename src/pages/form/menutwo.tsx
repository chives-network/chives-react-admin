
// ** Hooks
import UserList from '../Enginee/index'

const AppChat = () => {
    // ** States
    const backEndApi = "form_menutwo.php"
    
    return (
        <UserList backEndApi={backEndApi} externalId=''/>
    )
}


export default AppChat
