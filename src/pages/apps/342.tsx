
/*
* Infrastructure: Chives React Admin
* Author: Chives Network
* Email: reactchives@gmail.com
* Copyright (c) 2023
* License: GPL V3 or Commercial license
*/
// ** React Imports
import { ReactNode } from 'react'

// ** Layout Import
import BlankLayout from 'src/@core/layouts/BlankLayout'

import UserList from "../Enginee/index"

const AppChat = () => {
    // ** States
    const backEndApi = "apps/apps_342.php"
    
  return (
    <UserList backEndApi={backEndApi} externalId=''/>
    )
}

AppChat.getLayout = (page: ReactNode) => <BlankLayout>{page}</BlankLayout>

export default AppChat
