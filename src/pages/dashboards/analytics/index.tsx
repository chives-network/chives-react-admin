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
import AnalyticsTrophy from 'src/views/dashboards/analytics/AnalyticsTrophy'
import AnalyticsPerformance from 'src/views/dashboards/analytics/AnalyticsPerformance'
import AnalyticsDepositWithdraw from 'src/views/dashboards/analytics/AnalyticsDepositWithdraw'
import AnalyticsTransactionsCard from 'src/views/dashboards/analytics/AnalyticsTransactionsCard'

import axios from 'axios'

// ** Config
import authConfig from 'src/configs/auth'


const AnalyticsDashboard = () => {

  const [isLoading, setIsLoading] = useState<boolean>(true)
  const dataDefault:{[key:string]:any} = {}
  const [dashboardData, setDashboardData] = useState(dataDefault)

  const backEndApi = "dashboard_jifen.php"
  const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!

  useEffect(() => {
    axios.get(authConfig.backEndApiHost + backEndApi, { headers: { Authorization: storedToken }, params: {} })
        .then(res => {
            setDashboardData(res.data);
            setIsLoading(false)
        })
        .catch(() => {
            console.log("axios.get editUrl return")
        })
    }, [])
  
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
                      <AnalyticsTrophy data={dashboardData['AnalyticsTrophy']}/>
                    </Grid>
                    <Grid item xs={12} md={8}>
                      <AnalyticsTransactionsCard data={dashboardData['AnalyticsTransactionsCard']}/>
                    </Grid>
                    <Grid item xs={12} md={6} lg={4}>
                      <AnalyticsPerformance />
                    </Grid>
                    <Grid item xs={12} md={8}>
                      <AnalyticsDepositWithdraw data={dashboardData['AnalyticsDepositWithdraw']}/>
                    </Grid>
                  </Grid>
                )}
      
    </ApexChartWrapper>
  )
}

export default AnalyticsDashboard
