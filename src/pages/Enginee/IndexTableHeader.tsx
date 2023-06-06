// ** MUI Imports
import { useState, useEffect, Fragment, Ref, useRef } from 'react'
import Box from '@mui/material/Box'
import Button from '@mui/material/Button'
import TextField from '@mui/material/TextField'
import Mousetrap from 'mousetrap'

// ** Icon Imports
import Grid from '@mui/material/Grid'
import Divider from '@mui/material/Divider'
import MenuItem from '@mui/material/MenuItem'
import InputLabel from '@mui/material/InputLabel'
import FormControl from '@mui/material/FormControl'
import CardContent from '@mui/material/CardContent'
import Tooltip from "@mui/material/Tooltip"
import Select, { SelectChangeEvent } from '@mui/material/Select'

import Dialog from '@mui/material/Dialog'
import DialogTitle from '@mui/material/DialogTitle'
import DialogContent from '@mui/material/DialogContent'
import DialogActions from '@mui/material/DialogActions'
import DialogContentText from '@mui/material/DialogContentText'

import { GridRowId } from '@mui/x-data-grid-pro'
import toast from 'react-hot-toast'

import { useForm, Controller } from 'react-hook-form'

interface TableHeaderProps {
  filter: any[]
  handleFilterChange: (field: any, value: string) => void
  handleFilter: (val: string) => void
  toggle: () => void
  value: string
  searchFieldText: string
  searchFieldArray: { value: string; }[]
  selectedRows: GridRowId[]
  multireview: {multireview:{}[]}
  multiReviewHandleFilter: (action: string, multiReviewInputValue: string, selectedRows: GridRowId[], CSRF_TOKEN: string) => void
  button_search: string
  button_add: string
  isAddButton: boolean
  CSRF_TOKEN: string
}

const IndexTableHeader = (props: TableHeaderProps) => {
  
  // ** Props
  const { filter, handleFilterChange, handleFilter, toggle, value, searchFieldText, searchFieldArray, selectedRows, multireview, multiReviewHandleFilter, button_search, button_add, isAddButton, CSRF_TOKEN } = props
  const defaultValuesInitial = { "searchOneFieldName": searchFieldArray && searchFieldArray[0] && searchFieldArray[0].value ? searchFieldArray[0].value : undefined, "searchOneFieldValue": "", "multiReviewInputName": "" }
  console.log("value tableHeader",value)
  
  //const [defaultValues, setDefaultValues] = useState(JSON.parse(JSON.stringify(defaultValuesInitial)))
  const defaultValues = JSON.parse(JSON.stringify(defaultValuesInitial))
  const [filterSelectValue, setFilterSelectValue] = useState<any[]>([])

  useEffect(() => {
    
    //Mousetrap.bind(['alt+f', 'command+f'], handleSubmit(onSubmit));
    Mousetrap.bind(['alt+a', 'command+a'], toggle);
    
    return () => {
      Mousetrap.unbind(['alt+f', 'command+f']);
      Mousetrap.unbind(['alt+a', 'command+a']);
    }
  });

  //console.log("defaultValuesInitial", defaultValuesInitial)
  //console.log("defaultValues", defaultValues)
  //console.log("JSON.parse(JSON.stringify(filter))***********", JSON.parse(JSON.stringify(filter)))
  //console.log("filter", filter)
  console.log("filter*******************************", filter)
  console.log("searchFieldArray*******************************", searchFieldArray)
  const {
    control,
    handleSubmit,
    formState: { errors }
  } = useForm({
    defaultValues: defaultValues,
    mode: 'onChange'
  })

  const onSubmit = (data: any) => {
    handleFilter(data)
  }

  const [multiReviewInputValue, setMultiReviewInputValue] = useState('')
  const handleMultiReviewAction = (action: string, selectedRows: GridRowId[]) => {
    multiReviewHandleFilter(action, multiReviewInputValue, selectedRows, CSRF_TOKEN)
    setMultiReviewInputValue('')
  }
  const [multiReviewOpenDialog, setMultiReviewOpenDialog] = useState<{[key:string]:any}>({})
  const handleMultiOpenDialog = (action: string) => {
    const multiReviewOpenDialogNew:{[key:string]:any} = {}
    multireview.multireview.map((Item: any) => {
      multiReviewOpenDialogNew[Item.action] = false
    })
    multiReviewOpenDialogNew[action] = true
    setMultiReviewOpenDialog(multiReviewOpenDialogNew)
  }
  const handleMultiCloseDialog = () => {
    const multiReviewOpenDialogNew:{[key:string]:any} = {}
    multireview.multireview.map((Item: any) => {
      multiReviewOpenDialogNew[Item.action] = false
    })
    setMultiReviewOpenDialog(multiReviewOpenDialogNew)
  }
  const handleMultiCloseDialogAndSubmit = (action: string, selectedRows: GridRowId[], Item: any) => {
    if (Item.inputmust && Item.memoname != "" && multiReviewInputValue == '') {
      toast.error(Item.inputmusttip)
    }
    else {
      handleMultiCloseDialog()
      handleMultiReviewAction(action, selectedRows)
    }
  }

  const myRef:Ref<any> = useRef(null)

  return (
    <>
      <form onSubmit={handleSubmit(onSubmit)} id="searchOneField">
        {filter.length > 0 ?
          <CardContent sx={{ pl: 5, pb: 1, pt: 1 }}>
            <Grid container spacing={6}>
              {filter.length > 0 && filter.map((Filter: any, Filter_index: number) => {
                
                //const [valueFunction, setStatusFunction] = FilterStateMap['Filter_'+Filter_index];
                //console.log("valueFunction",valueFunction)
                //console.log("Filter-----",Filter)
                return (
                  <Grid item sm={3} xs={12} key={"Filter_" + Filter_index}>
                    <FormControl fullWidth size="small">
                      <InputLabel id={Filter.name}>{Filter.text}</InputLabel>
                      <Select
                        fullWidth
                        value={filterSelectValue[Filter_index] || ''}
                        id={Filter.text}
                        label={Filter.name}
                        labelId={Filter.text}
                        
                        //onChange={ (e: SelectChangeEvent) => {handleStatusChange(e)} }
                        //onChange={e: SelectChangeEvent => handleChange(Filter.name, e.target.value)}
                        onChange={(e: SelectChangeEvent) => {
                          
                          //console.log("filter", filter); 
                          handleFilterChange(Filter.name, e.target.value)
                          filterSelectValue[Filter_index] = e.target.value
                          setFilterSelectValue(filterSelectValue);
                        }
                        }
                        inputProps={{ placeholder: Filter.text }}
                      >
                        {Filter && Filter.list.map((item: any, item_index: number) => {
                          
                          //console.log("item.name",item.name)
                          return (
                            <MenuItem value={item.value} key={item.name + "_" + item_index}>{item.name}({item.num})</MenuItem>
                          )
                        })}
                      </Select>
                    </FormControl>
                  </Grid>
                )

              })}
            </Grid>
          </CardContent>
          : ''
        }
        {filter.length > 0 ? <Divider /> : ''}
        {!selectedRows || selectedRows.length == 0 ?
          <Box sx={{ pl: 5, pb: 2, display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between' }}>
            <Grid container spacing={2}>
              {searchFieldArray ?
                <Grid item sm={3} xs={12}>
                  <FormControl fullWidth size="small">
                    <InputLabel id={searchFieldText}>{searchFieldText}</InputLabel>
                    <Controller
                      name='searchOneFieldName'
                      control={control}
                      render={({ field: { value, onChange } }) => (
                        <Select
                          value={value}
                          label={searchFieldText}
                          onChange={onChange}
                          error={Boolean(errors['searchOneFieldName'])}
                          labelId='validation-basic-select'
                          aria-describedby='validation-basic-select'
                        >
                          {searchFieldArray && searchFieldArray.map((ItemArray: any, ItemArray_index: number) => {
                            return <MenuItem value={ItemArray.value} key={"SelectedRows_" + ItemArray_index}>{ItemArray.label}</MenuItem>
                          })}
                        </Select>
                      )}
                    />
                  </FormControl>
                </Grid>
                : ''}
              {searchFieldArray ?
                <Grid item sm={2} xs={12}>
                  <FormControl fullWidth size="small" sx={{}}>
                    <Controller
                      name="searchOneFieldValue"
                      control={control}
                      render={({ field: { value, onChange } }) => (
                        <TextField
                          size='small'
                          value={value}
                          sx={{ mb: 2 }}
                          label={searchFieldText}
                          onChange={onChange}
                          placeholder={searchFieldText}
                          error={Boolean(errors['searchOneFieldValue'])}
                        />
                      )}
                    />
                  </FormControl>
                </Grid>
                : ''}
              {searchFieldArray ?
                <Grid item sm={2} xs={12}>
                  <FormControl fullWidth size="small">
                    <Tooltip title="Alt+f">
                      <Button sx={{ mb: 2 }} variant='contained' type='submit'>{button_search}</Button>
                    </Tooltip>
                  </FormControl>
                </Grid>
                : ''}
              {isAddButton ?
                <Grid item sm={3} xs={12}>
                  <Tooltip title="Alt+a">
                    <Button sx={{ ml: 3, mb: 2 }} onClick={toggle} variant='contained'>{button_add}</Button>
                  </Tooltip>
                </Grid>
                : ''}
            </Grid>
          </Box>
          : ''
        }
      </form>
      {selectedRows && selectedRows.length > 0 ?
        <Box sx={{ pl: 5, pb: 2, display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between' }}>
          <Grid container spacing={2}>
            {multireview && multireview.multireview && multireview.multireview.map((Item: any, index: number) => {
              
              return (
                <Grid item key={"Grid_" + index}>
                  <Fragment>
                    <Button sx={{ mb: 2 }} variant='contained' type="button" onClick={() => handleMultiOpenDialog(Item.action)}>{Item.text}</Button>
                    <Dialog
                      open={multiReviewOpenDialog[Item.action] == undefined ? false : multiReviewOpenDialog[Item.action]}
                      onClose={() => handleMultiCloseDialog()}
                      aria-labelledby='form-dialog-title'
                    >
                      <DialogTitle id='form-dialog-title'>{Item.title}</DialogTitle>
                      <DialogContent>
                        <DialogContentText sx={{ mb: 3 }}>
                          {Item.content}
                        </DialogContentText>
                        {Item.memoname != "" ? <TextField required={Item.inputmust} inputRef={myRef} id={Item.memoname} value={multiReviewInputValue} onChange={(e) => { setMultiReviewInputValue(e.target.value) }} autoFocus fullWidth type='text' label={Item.memoname} /> : ''}
                      </DialogContent>
                      <DialogActions className='dialog-actions-dense'>
                        <Button onClick={() => handleMultiCloseDialog()}>{Item.cancel}</Button>
                        {Item.memoname != "" ? 
                          <Button onClick={() => { myRef.current.reportValidity(); handleMultiCloseDialogAndSubmit(Item.action, selectedRows, Item) }} variant='contained'>{Item.submit}</Button> 
                          : 
                          <Button onClick={() => { handleMultiCloseDialogAndSubmit(Item.action, selectedRows, Item) }} variant='contained'>{Item.submit}</Button> 
                        }
                      </DialogActions>
                    </Dialog>
                  </Fragment>
                </Grid>
              )
            })}
          </Grid>
        </Box>
        : ''
      }
    </>
  )
}

export default IndexTableHeader
