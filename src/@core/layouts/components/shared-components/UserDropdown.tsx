// ** React Imports
import { useState, useEffect, SyntheticEvent, Fragment } from 'react'

// ** Next Import
import { useRouter } from 'next/router'

// ** MUI Imports
import Box from '@mui/material/Box'
import Menu from '@mui/material/Menu'
import Badge from '@mui/material/Badge'
import Avatar from '@mui/material/Avatar'
import Divider from '@mui/material/Divider'
import MenuItem from '@mui/material/MenuItem'
import { styled } from '@mui/material/styles'
import Typography from '@mui/material/Typography'

// ** Icon Imports
import Icon from 'src/@core/components/icon'

// ** Context
import { useAuth } from 'src/hooks/useAuth'

// ** Config
import authConfig from 'src/configs/auth'
import axios from 'axios'

// ** Type Imports
import { Settings } from 'src/@core/context/settingsContext'

interface Props {
  settings: Settings
}

// ** Styled Components
const BadgeContentSpan = styled('span')(({ theme }) => ({
  width: 8,
  height: 8,
  borderRadius: '50%',
  backgroundColor: theme.palette.success.main,
  boxShadow: `0 0 0 2px ${theme.palette.background.paper}`
}))

const UserDropdown = (props: Props) => {
  // ** Props
  const { settings } = props

  // ** States
  const [anchorEl, setAnchorEl] = useState<Element | null>(null)
  const [isLogout, setIsLogout] = useState<boolean>(false)

  // ** Hooks
  const router = useRouter()
  const auth = useAuth()
  const logout = auth.logout
  const user:any = auth.user

  // ** Vars
  const { direction } = settings

  const handleDropdownOpen = (event: SyntheticEvent) => {
    setAnchorEl(event.currentTarget)
  }

  const handleDropdownClose = (url?: string) => {
    if(url=='/apps/200' && user.type=="Student")  {
      url = '/apps/203'
    }
    if(url=='/apps/201' && user.type=="Student")  {
      url = '/apps/202'
    }    
    if (url) {
      router.push(url)
    }
    setAnchorEl(null)
  }

  const styles = {
    py: 2,
    px: 4,
    width: '100%',
    display: 'flex',
    alignItems: 'center',
    color: 'text.primary',
    textDecoration: 'none',
    '& svg': {
      mr: 2,
      fontSize: '1.375rem',
      color: 'text.primary'
    }
  }

  const handleLogout = () => {
    setIsLogout(true)
    logout()
    handleDropdownClose()
  }

  const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!
  useEffect(() => {
    if(isLogout) {
      axios
      .get(authConfig.logoutEndpoint, { headers: { Authorization: storedToken} })
      .then()
    }
  }, [isLogout])

  return (
    <Fragment>
      <Badge
        overlap='circular'
        onClick={handleDropdownOpen}
        sx={{ ml: 2, cursor: 'pointer' }}
        badgeContent={<BadgeContentSpan />}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'right'
        }}
      >
        <Avatar
          alt={user.USER_NAME}
          onClick={handleDropdownOpen}
          sx={{ width: 40, height: 40 }}
          src={authConfig.backEndApiHost + user.avatar}
        />
      </Badge>
      <Menu
        anchorEl={anchorEl}
        open={Boolean(anchorEl)}
        onClose={() => handleDropdownClose()}
        sx={{ '& .MuiMenu-paper': { width: 230, mt: 4 } }}
        anchorOrigin={{ vertical: 'bottom', horizontal: direction === 'ltr' ? 'right' : 'left' }}
        transformOrigin={{ vertical: 'top', horizontal: direction === 'ltr' ? 'right' : 'left' }}
      >
        <Box sx={{ pt: 2, pb: 3, px: 4 }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <Badge
              overlap='circular'
              badgeContent={<BadgeContentSpan />}
              anchorOrigin={{
                vertical: 'bottom',
                horizontal: 'right'
              }}
            >
              <Avatar alt={user.USER_NAME} src={authConfig.backEndApiHost + user.avatar} sx={{ width: '2.5rem', height: '2.5rem' }} />
            </Badge>
            <Box sx={{ display: 'flex', ml: 3, alignItems: 'flex-start', flexDirection: 'column' }}>
              <Typography sx={{ fontWeight: 600 }}>{user.USER_NAME}</Typography>
              <Typography variant='body2' sx={{ fontSize: '0.8rem', color: 'text.disabled' }}>
              {user.role}
              </Typography>
            </Box>
          </Box>
        </Box>
        <Divider sx={{ mt: '0 !important' }} />
        {user.type=="User" ?
          <MenuItem sx={{ p: 0 }} onClick={() => handleDropdownClose('/user/profile')}>
            <Box sx={styles}>
              <Icon icon='mdi:account-outline' />
              个人档案
            </Box>
          </MenuItem>
        :
        ''}
        {user.type=="User" ?
          <Divider />
        :
        ''}
          <MenuItem sx={{ p: 0 }} onClick={() => handleDropdownClose('/user/password')}>
            <Box sx={styles}>
              <Icon icon='mdi:security' />
              修改密码
            </Box>
          </MenuItem>
          <Divider />
          <MenuItem sx={{ p: 0 }} onClick={() => handleDropdownClose('/apps/200')}>
            <Box sx={styles}>
              <Icon icon='mdi:database-plus' />
              操作日志
            </Box>
          </MenuItem>
          <Divider />
          <MenuItem sx={{ p: 0 }} onClick={() => handleDropdownClose('/apps/201')}>
            <Box sx={styles}>
              <Icon icon='mdi:tumblr-reblog' />
              登录日志
            </Box>
          </MenuItem>

        <Divider />
        <MenuItem
          onClick={handleLogout}
          sx={{ py: 2, '& svg': { mr: 2, fontSize: '1.375rem', color: 'text.primary' } }}
        >
          <Icon icon='mdi:logout-variant' />
          退出
        </MenuItem>
      </Menu>
    </Fragment>
  )
}

export default UserDropdown
