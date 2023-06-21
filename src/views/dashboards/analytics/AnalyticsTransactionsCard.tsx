
// ** MUI Imports
import Box from '@mui/material/Box'
import Grid from '@mui/material/Grid'
import Card from '@mui/material/Card'
import CardHeader from '@mui/material/CardHeader'
import Typography from '@mui/material/Typography'
import CardContent from '@mui/material/CardContent'

// ** Icon Imports
import Icon from 'src/@core/components/icon'

// ** Types
import { ThemeColor } from 'src/@core/layouts/types'

// ** Custom Components Imports
import OptionsMenu from 'src/@core/components/option-menu'
import CustomAvatar from 'src/@core/components/mui/avatar'

interface DataType2 {
  stats: string
  title: string
  color: ThemeColor
  icon: string
}

interface DataType {
  data: {[key:string]:any}
}

const AnalyticsTransactionsCard = (props: DataType) => {
  
  const { data } = props

  return (
    <Card>
      <CardHeader
        title={data.Title}
        action={
          <OptionsMenu
            options={['Last 28 Days', 'Last Month', 'Last Year']}
            iconButtonProps={{ size: 'small', sx: { color: 'text.primary' } }}
          />
        }
        subheader={
          <Typography variant='body2'>
            <Box component='span' sx={{ fontWeight: 600, color: 'text.primary' }}>
            {data.SubTitle}
            </Box>{' '}
          </Typography>
        }
        titleTypographyProps={{
          sx: {
            mb: 2.5,
            lineHeight: '2rem !important',
            letterSpacing: '0.15px !important'
          }
        }}
      />
      <CardContent sx={{ pt: theme => `${theme.spacing(3)} !important` }}>
        <Grid container spacing={[5, 0]}>
          {
            data.data.map((item: DataType2, index: number) => (
              <Grid item xs={12} sm={2.4} key={index}>
                <Box key={index} sx={{ display: 'flex', alignItems: 'center' }}>
                  <CustomAvatar
                    variant='rounded'
                    color={item.color}
                    sx={{ mr: 3, boxShadow: 3, width: 44, height: 44, '& svg': { fontSize: '1.75rem' } }}
                  >
                  <Icon icon={item.icon} />
                  </CustomAvatar>
                  <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                    <Typography>{item.title}</Typography>
                    <Typography variant='h6'>{item.stats}</Typography>
                  </Box>
                </Box>
              </Grid>
            ))
          }
        </Grid>
      </CardContent>
    </Card>
  )
}

export default AnalyticsTransactionsCard
