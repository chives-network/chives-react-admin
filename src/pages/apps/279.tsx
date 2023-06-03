
/*
* Infrastructure: Chives React Admin
* Author: Chives Network
* Email: reactchives@gmail.com
* Copyright (c) 2023
* License: GPL V3 or Commercial license
*/
import UserList from "../Enginee/index"

const AppChat = () => {
    // ** States
    const backEndApi = "apps/apps_279.php"
    
  return (
    <UserList backEndApi={backEndApi} externalId=''/>
    )
}

export default AppChat
