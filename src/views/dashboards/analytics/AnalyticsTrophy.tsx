// ** MUI Imports
import Card from '@mui/material/Card'
import Button from '@mui/material/Button'
import Typography from '@mui/material/Typography'
import CardContent from '@mui/material/CardContent'
import { styled, useTheme } from '@mui/material/styles'
import { useRouter } from 'next/router'

import ButtonGroupSplit from 'src/views/dashboards/analytics/ButtonGroupSplit'

// Styled component for the triangle shaped background image
const TriangleImg = styled('img')(({ theme }) => ({
  right: 0,
  bottom: 0,
  height: 170,
  position: 'absolute',
  ...(theme.direction === 'rtl' ? { transform: 'scaleX(-1)' } : {})
}))

// Styled component for the trophy image
const TrophyImg = styled('img')({
  right: 36,
  bottom: 20,
  height: 98,
  position: 'absolute'
})

interface DataType {
  data: {[key:string]:any}
  toggleSetClassName: (arg0: string) => void
}

const AnalyticsTrophy = (props: DataType) => {

  const { data, toggleSetClassName } = props

  const router = useRouter();

  // ** Hook
  const theme = useTheme()

  const imageSrc = theme.palette.mode === 'light' ? 'triangle-light.png' : 'triangle-dark.png'

  return (
    <Card sx={{ position: 'relative' }}>
      <CardContent>
        <Typography variant='h6'>{data.Welcome}</Typography>
        <Typography variant='body2' sx={{ letterSpacing: '0.25px' }}>
        {data.SubTitle}
        </Typography>
        <Typography variant='h5' sx={{ my: 4, color: 'primary.main' }}>
          {data.TotalScore}
        </Typography>
        {data.TopRightOptions ? 
          <ButtonGroupSplit data={data.TopRightOptions} toggleSetClassName={toggleSetClassName} />
        :
          <Button size='small' variant='contained' onClick={() => router.push(data.ViewButton.url)}>
            {data.ViewButton.name}
          </Button>
        }
        
        <TriangleImg alt='triangle background' src={`/images/misc/${imageSrc}`} />
        <TrophyImg alt='trophy' src='/images/misc/trophy.png' />
      </CardContent>
    </Card>
  )
}

export default AnalyticsTrophy
