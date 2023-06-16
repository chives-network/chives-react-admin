// ** React Imports
import { forwardRef, ReactElement, Ref, Fragment, useState } from 'react'

// ** MUI Imports
import Box from '@mui/material/Box'
import Badge from '@mui/material/Badge'
import IconButton from '@mui/material/IconButton'
import Dialog from '@mui/material/Dialog'
import DialogContent from '@mui/material/DialogContent'
import Fade, { FadeProps } from '@mui/material/Fade'

// ** Icon Imports
import Icon from 'src/@core/components/icon'

const Transition = forwardRef(function Transition(
    props: FadeProps & { children?: ReactElement<any, any> },
    ref: Ref<unknown>
  ) {
    return <Fade ref={ref} {...props} />
  })

// ** Third Party Components
import clsx from 'clsx'
import { useKeenSlider } from 'keen-slider/react'

interface ImagesPreviewType {
    open: boolean
    imagesList: string[]
    toggleImagesPreviewDrawer: () => void
  }

const ImagesPreview = (props: ImagesPreviewType) => {
  // ** Props
  const { open, imagesList, toggleImagesPreviewDrawer } = props
  
  const handleClose = () => {
    toggleImagesPreviewDrawer()
  }

  console.log("imagesList",imagesList)

  // ** States
  const [loaded, setLoaded] = useState<boolean>(false)
  const [currentSlide, setCurrentSlide] = useState<number>(0)

  // ** Hook
  const [sliderRef, instanceRef] = useKeenSlider<HTMLDivElement>({
    rtl: true,
    slideChanged(slider) {
      setCurrentSlide(slider.track.details.rel)
    },
    created() {
      setLoaded(true)
    }
  })

  return (
    <Dialog
        fullWidth
        open={open}
        maxWidth='md'
        scroll='body'
        onClose={handleClose}
        TransitionComponent={Transition}
      >
        <DialogContent sx={{ pb: 8, px: { xs: 8, sm: 8 }, pt: { xs: 8, sm: 12.5 }, position: 'relative' }}>
          <IconButton
            size='small'
            onClick={handleClose}
            sx={{ position: 'absolute', right: '1rem', top: '1rem' }}
          >
            <Icon icon='mdi:close' />
          </IconButton>
          <Fragment>
            <Box className='navigation-wrapper'>
                <Box ref={sliderRef} className='keen-slider'>
                {imagesList && imagesList.length>0 && imagesList.map((Url: string, UrlIndex: number)=>{
                    return (
                        <Box className='keen-slider__slide' key={UrlIndex}>
                            <img src={Url} alt='swiper 1' />
                        </Box>
                    )
                })}
                </Box>
                {loaded && instanceRef.current && (
                <Fragment>
                    <Icon
                    icon='mdi:chevron-left'
                    className={clsx('arrow arrow-left', {
                        'arrow-disabled': currentSlide === 0
                    })}
                    onClick={(e: any) => e.stopPropagation() || instanceRef.current?.prev()}
                    />
                    <Icon
                    icon='mdi:chevron-right'
                    className={clsx('arrow arrow-right', {
                        'arrow-disabled': currentSlide === instanceRef.current.track.details.slides.length - 1
                    })}
                    onClick={(e: any) => e.stopPropagation() || instanceRef.current?.next()}
                    />
                </Fragment>
                )}
            </Box>
            {loaded && instanceRef.current && (
                <Box className='swiper-dots'>
                {[...Array(instanceRef.current.track.details.slides.length).keys()].map(idx => {
                    return (
                    <Badge
                        key={idx}
                        variant='dot'
                        component='div'
                        className={clsx({
                        active: currentSlide === idx
                        })}
                        onClick={() => {
                        instanceRef.current?.moveToIdx(idx)
                        }}
                    ></Badge>
                    )
                })}
                </Box>
            )}
            </Fragment>
        </DialogContent>
      </Dialog >
    
  )
}

export default ImagesPreview
