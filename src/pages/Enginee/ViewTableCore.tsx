// ** React Imports
import { useState, useEffect, Fragment } from 'react'

// ** MUI Imports
import Typography from '@mui/material/Typography'
import Box from '@mui/material/Box'
import Table from '@mui/material/Table'
import TableRow from '@mui/material/TableRow'
import TableBody from '@mui/material/TableBody'
import { styled } from '@mui/material/styles'
import TableCell, { TableCellBaseProps } from '@mui/material/TableCell'
import Grid from '@mui/material/Grid'
import Card from '@mui/material/Card'
import CardHeader from '@mui/material/CardHeader'
import CardContent from '@mui/material/CardContent'

// ** Config
import authConfig from 'src/configs/auth'
import axios from 'axios'
import Mousetrap from 'mousetrap';

// ** Store Imports
import { useSelector } from 'react-redux'

// ** Styles
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css'

import { RootState } from 'src/store/index'

const MUITableCell = styled(TableCell)<TableCellBaseProps>(({ theme }) => ({
  borderBottom: 0,
  paddingLeft: '0 !important',
  paddingRight: '0 !important',
  paddingTop: `${theme.spacing(1)} !important`,
  paddingBottom: `${theme.spacing(1)} !important`
}))

interface ViewTableType {
  id: string
  action: string
  open: boolean
  toggleViewTableDrawer: () => void
  backEndApi: string
  editViewCounter: number
  externalId: number
  pageJsonInfor: {}
  CSRF_TOKEN: string
}

const ViewTableCore = (props: ViewTableType) => {
  // ** Props
  const { externalId, id, action, toggleViewTableDrawer, backEndApi, editViewCounter, CSRF_TOKEN } = props
  console.log("externalId props", externalId)
  
  // ** Hooks
  //const dispatch = useDispatch<AppDispatch>()
  const store = useSelector((state: RootState) => state.user)
  const allFields = store.view_default.allFields;
  const allFieldsMode = store.view_default.allFieldsMode;
  const titletext: string = store.view_default.titletext;
  const [defaultValuesView, setDefaultValuesView] = useState<{[key:string]:any}>({})

  useEffect(() => {
    Mousetrap.bind(['alt+c', 'command+c'], handleClose);
    
    return () => {
      Mousetrap.unbind(['alt+c', 'command+c']);
    }
  });

  //console.log("view_default--------------------------------", id, action)
  const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!

  useEffect(() => {
    if (action == "view_default" && editViewCounter > 0) {
      axios
        .get(authConfig.backEndApiHost + backEndApi, { headers: { Authorization: storedToken+"::::"+CSRF_TOKEN }, params: { action, id, editViewCounter } })
        .then(res => {
          if (res.data.status == "OK") {
            setDefaultValuesView(res.data.data)
          }
        })
        .catch(() => {
          console.log("axios.get editUrl return")
        })
    }
  }, [id, editViewCounter]) 
  
  //Need refresh data every time.

  const handleClose = () => {
    toggleViewTableDrawer()
  }

  return (
    <Fragment>
        <Box sx={{ mb: 8, textAlign: 'center' }}>
          <Typography variant='h5' sx={{ mb: 3 }}>
            {titletext}
          </Typography>
          <Typography variant='body2'>{store.view_default.titlememo ? store.view_default.titlememo : ''}</Typography>
        </Box>
        {allFieldsMode && allFieldsMode.map((allFieldsModeItem: any, allFieldsModeIndex: number) => {
          
          return (
            <Card key={"AllFieldsMode_" + allFieldsModeIndex}>
              <CardHeader title={allFieldsModeItem.label} />
              <CardContent sx={{ px: { xs: 8, sm: 12 } }}>
                <Grid container spacing={6} >
                  <Table>
                    <TableBody>
                      {allFields && allFields[allFieldsModeItem.value] && allFields[allFieldsModeItem.value].map((FieldArray: any, FieldArray_index: number) => {

                        //开始根据表单中每个字段的类型,进行不同的渲染,此部分比较复杂,注意代码改动.
                        if (FieldArray.type == "input"
                          || FieldArray.type == "email"
                          || FieldArray.type == "date"
                          || FieldArray.type == "time"
                          || FieldArray.type == "datetime"
                          || FieldArray.type == "slider"
                          || FieldArray.type == "autoincrement"
                          || FieldArray.type == "autoincrementdate"
                        ) {
                          if (defaultValuesView[FieldArray.name] == "1971-01-01" || defaultValuesView[FieldArray.name] == "1971-01-01 00:00:00" || defaultValuesView[FieldArray.name] == "1971-01") {
                            defaultValuesView[FieldArray.name] = "";
                          }
                          
                          return (
                            <TableRow key={FieldArray_index}>
                              <MUITableCell>{FieldArray.label}:</MUITableCell>
                              <MUITableCell>{defaultValuesView[FieldArray.name]}</MUITableCell>
                            </TableRow>
                          )
                        }//end if
                        else if (FieldArray.type == "password" || FieldArray.type == "comfirmpassword") {
                          // Nothing to do                
                        }
                        else if (FieldArray.type == "select" || FieldArray.type == "autocomplete" || FieldArray.type == "radiogroup") {
                          
                          return (
                            <TableRow key={FieldArray_index}>
                              <MUITableCell>{FieldArray.label}:</MUITableCell>
                              <MUITableCell>{defaultValuesView[FieldArray.name]}</MUITableCell>
                            </TableRow>
                          )
                        }
                        else if (FieldArray.type == "autocompletemulti") {
                          
                          return (
                            <TableRow key={FieldArray_index}>
                              <MUITableCell>{FieldArray.label}:</MUITableCell>
                              <MUITableCell>{defaultValuesView[FieldArray.name]}</MUITableCell>
                            </TableRow>
                          )
                        }
                        else if (FieldArray.type == "checkbox") {
                          
                          return (
                            <TableRow key={FieldArray_index}>
                              <MUITableCell>{FieldArray.label}:</MUITableCell>
                              <MUITableCell>{defaultValuesView[FieldArray.name]}</MUITableCell>
                            </TableRow>
                          )
                        }
                        else if (FieldArray.type == "textarea") {
                          
                          return (
                            <TableRow key={FieldArray_index}>
                              <MUITableCell>{FieldArray.label}:</MUITableCell>
                              <MUITableCell>{defaultValuesView[FieldArray.name]}</MUITableCell>
                            </TableRow>
                          )
                        }
                        else if (FieldArray.type == "avator") {
                          
                          return (
                            <TableRow key={FieldArray_index}>
                              <MUITableCell>{FieldArray.label}:</MUITableCell>
                              <MUITableCell>{defaultValuesView[FieldArray.name]}</MUITableCell>
                            </TableRow>
                          )
                        }
                        else if (FieldArray.type == "editor") {
                          
                          return (
                            <TableRow key={FieldArray_index}>
                              <MUITableCell>{FieldArray.label}:</MUITableCell>
                              <MUITableCell>{defaultValuesView[FieldArray.name]}</MUITableCell>
                            </TableRow>
                          )
                        }
                      })}
                    </TableBody>
                  </Table>
                </Grid>
              </CardContent>
            </Card>
          )
        })}
    </Fragment>
  )
}

export default ViewTableCore
