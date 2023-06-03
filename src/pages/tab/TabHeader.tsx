// ** React Imports
import { useState, useEffect, SyntheticEvent } from 'react'

// ** Next Import
import { useRouter } from 'next/router'

// ** MUI Imports
import Tab from '@mui/material/Tab'
import Box from '@mui/material/Box'
import Grid from '@mui/material/Grid'
import TabPanel from '@mui/lab/TabPanel'
import TabContext from '@mui/lab/TabContext'
import Typography from '@mui/material/Typography'
import { styled, Theme } from '@mui/material/styles'
import useMediaQuery from '@mui/material/useMediaQuery'
import MuiTabList, { TabListProps } from '@mui/lab/TabList'
import CircularProgress from '@mui/material/CircularProgress'

// ** Icon Imports
import Icon from 'src/@core/components/icon'

// ** Demo Tabs Imports
import TabContent from './TabContent'

const TabList = styled(MuiTabList)<TabListProps>(({ theme }) => ({
  '& .MuiTabs-indicator': {
    display: 'none'
  },
  '& .Mui-selected': {
    backgroundColor: theme.palette.primary.main,
    color: `${theme.palette.common.white} !important`
  },
  '& .MuiTab-root': {
    minWidth: 65,
    minHeight: 40,
    paddingTop: theme.spacing(2),
    paddingBottom: theme.spacing(2),
    borderRadius: theme.shape.borderRadius,
    [theme.breakpoints.up('md')]: {
      minWidth: 130
    }
  }
}))

const TabHeader = ({ tab, allTabs }: { tab: string; allTabs: {[key:string]:any} }) => {
  // ** State
  const [activeTab, setActiveTab] = useState<string>(tab)
  const [isLoading, setIsLoading] = useState<boolean>(false)

  // ** Hooks
  const router = useRouter()
  const hideText = useMediaQuery((theme: Theme) => theme.breakpoints.down('md'))

  const handleChange = (event: SyntheticEvent, value: string) => {
    setIsLoading(true)
    router.push(`./${value.toLowerCase()}`).then(() => setIsLoading(false))
  }

  useEffect(() => {
    if (tab && tab !== activeTab) {
      setActiveTab(tab)
    }
  }, [tab])

  const TabGroupValue = allTabs[activeTab]['group']

  return (
    <Grid container spacing={6}>
      <Grid item xs={12}>
        <TabContext value={activeTab}>
          <Grid container spacing={6}>
            <Grid item xs={12}>
              <TabList
                variant='scrollable'
                scrollButtons='auto'
                onChange={handleChange}
                aria-label='customized tabs example'
              >
                {Object.entries(allTabs).map(([TabValue, Item]) => {
                  if (TabGroupValue == Item['group']) {
                    return (
                      <Tab
                        key={TabValue}
                        value={Item.value}
                        label={
                          < Box sx={{ display: 'flex', alignItems: 'center', ...(!hideText && { '& svg': { mr: 2 } }) }}>
                            <Icon icon={`mdi-${Item.icon}`} />
                            {!hideText && Item.label}
                          </Box>
                        }
                      />
                    )
                  }
                })
                }
              </TabList>
            </Grid>
            <Grid item xs={12} sx={{ pt: theme => `${theme.spacing(4)} !important` }}>
              {isLoading || (activeTab!=tab) ? (
                <Box sx={{ mt: 6, display: 'flex', alignItems: 'center', flexDirection: 'column' }}>
                  <CircularProgress sx={{ mb: 4 }} />
                  <Typography>Loading...</Typography>
                </Box>
              ) : (
                <TabPanel sx={{ p: 0 }} value={activeTab}>
                  <TabContent backEndApi={allTabs[activeTab]['backEndApi']} action={allTabs[activeTab]['action']} id={allTabs[activeTab]['id']} />
                </TabPanel>
              )}
            </Grid>
          </Grid>
        </TabContext>
      </Grid >
    </Grid >
  )
}

export default TabHeader
