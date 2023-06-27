// ** React Imports
import { useState, useEffect } from 'react'

// ** MUI Imports
import Grid from '@mui/material/Grid'
import CircularProgress from '@mui/material/CircularProgress'
import Typography from '@mui/material/Typography'
import Box from '@mui/material/Box'

// ** Styled Component Import
import ApexChartWrapper from 'src/@core/styles/libs/react-apexcharts'

// ** Demo Components Imports
import ApexAreaChart from 'src/views/charts/apex-charts/ApexAreaChart'
import AnalyticsTrophy from 'src/views/dashboards/analytics/AnalyticsTrophy'
import AnalyticsSalesByCountries from 'src/views/dashboards/analytics/AnalyticsSalesByCountries'
import AnalyticsDepositWithdraw from 'src/views/dashboards/analytics/AnalyticsDepositWithdraw'
import AnalyticsTransactionsCard from 'src/views/dashboards/analytics/AnalyticsTransactionsCard'

import axios from 'axios'

// ** Config
import authConfig from 'src/configs/auth'
import { useAuth } from 'src/hooks/useAuth'


const AnalyticsDashboard = () => {

  const [isLoading, setIsLoading] = useState<boolean>(true)
  const dataDefault:{[key:string]:any} = {}
  const [dashboardData, setDashboardData] = useState(dataDefault)
  const [className, setClassName] = useState<string>("")
  const [optionsMenuItem, setOptionsMenuItem] = useState<string>("")
  const auth = useAuth()

  const toggleSetClassName = (classNameTemp: string) => {
    setClassName(classNameTemp)
  }
  
  const handleOptionsMenuItemClick = (Item: string) => {
    setOptionsMenuItem(Item)
  }

  console.log("auth",auth)

  useEffect(() => {
    if (auth.user && auth.user.type=="Student") {
      const backEndApi = "dashboard_jifen_student.php"
      axios.get(authConfig.backEndApiHost + backEndApi, { headers: { Authorization: storedToken }, params: { className, optionsMenuItem } })
      .then(res => {
          setDashboardData(res.data);
          setIsLoading(false)
          setClassName(res.data.defaultValue)
      })
    }
    else if (auth.user && auth.user.type=="User") {
      const backEndApi = "dashboard_jifen_banji.php"
      axios.get(authConfig.backEndApiHost + backEndApi, { headers: { Authorization: storedToken }, params: { className, optionsMenuItem } })
      .then(res => {
          setDashboardData(res.data);
          setIsLoading(false)
          setClassName(res.data.defaultValue)
      })
    }    
  }, [className, auth, optionsMenuItem])

  const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!

  console.log("dashboardData",dashboardData)

  return (
    <ApexChartWrapper>
      {isLoading ? (
                    <Grid item xs={12} sm={12} container justifyContent="space-around">
                        <Box sx={{ mt: 6, mb: 6, display: 'flex', alignItems: 'center', flexDirection: 'column' }}>
                            <CircularProgress />
                            <Typography>加载中...</Typography>
                        </Box>
                    </Grid>
                ) : (
                  <Grid container spacing={6}>
                    <Grid item xs={12} md={4}>
                      <AnalyticsTrophy data={dashboardData['AnalyticsTrophy']} toggleSetClassName={toggleSetClassName} />
                    </Grid>
                    <Grid item xs={12} md={8}>
                      <AnalyticsTransactionsCard data={dashboardData['AnalyticsTransactionsCard']} handleOptionsMenuItemClick={handleOptionsMenuItemClick} />
                    </Grid>
                    <Grid item xs={12} md={6} lg={4}>
                      <AnalyticsSalesByCountries data={dashboardData['AnalyticsSalesByCountries']} handleOptionsMenuItemClick={handleOptionsMenuItemClick}/>
                    </Grid>
                    <Grid item xs={12} md={8}>
                      <AnalyticsDepositWithdraw data={dashboardData['AnalyticsDepositWithdraw']} handleOptionsMenuItemClick={handleOptionsMenuItemClick}/>
                    </Grid>
                    <Grid item xs={12} md={12}>
                      <ApexAreaChart />
                    </Grid>
                  </Grid>
                )}
      
    </ApexChartWrapper>
  )
}

export default AnalyticsDashboard
