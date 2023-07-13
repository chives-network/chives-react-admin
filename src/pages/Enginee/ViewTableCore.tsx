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
import ListItem from '@mui/material/ListItem'
import Link from "@mui/material/Link"

// ** Icon Imports
import Icon from 'src/@core/components/icon'

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
  toggleImagesPreviewListDrawer: (imagesPreviewList: string[]) => void
}

const ImgStyled = styled('img')(({ theme }) => ({
  width: 120,
  height: 120,
  borderRadius: 4,
  marginRight: theme.spacing(5)
}))

const CustomLink = styled(Link)({
  textDecoration: "none",
  color: "inherit",
});

const ViewTableCore = (props: ViewTableType) => {
  // ** Props
  const { externalId, id, action, toggleViewTableDrawer, backEndApi, editViewCounter, CSRF_TOKEN,toggleImagesPreviewListDrawer } = props
  console.log("externalId props", externalId)
  
  // ** Hooks
  //const dispatch = useDispatch<AppDispatch>()
  const store = useSelector((state: RootState) => state.user)
  const allFields = store.view_default.allFields;
  const allFieldsMode = store.view_default.allFieldsMode;
  const titletext: string = store.view_default.titletext;
  const [defaultValuesView, setDefaultValuesView] = useState<{[key:string]:any}>({})
  const [childTable, setChildTable] = useState<{[key:string]:any}>({})

  const addFilesOrDatesDefault:{[key:string]:any}[][] = []
  const [newTableRowData, setNewTableRowData] = useState(addFilesOrDatesDefault)
  
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
            if(res.data.childtable) {
              setChildTable(res.data.childtable)
            }
            if(res.data.newTableRowData) {
              setNewTableRowData(res.data.newTableRowData)
            }
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
        <Card key={"AllFieldsMode"}>
          <CardContent sx={{ px: { xs: 8, sm: 12 } }}>
            <Grid container spacing={6} >
              <Table>
                <TableBody>
                  {newTableRowData && newTableRowData.length>0 && newTableRowData.map((RowData: any, RowData_index: number) => {

                    return (
                      <TableRow key={RowData_index}>
                        {RowData && RowData.map((CellData: any, FieldArray_index: number) => {
                          const FieldArray = CellData.FieldArray
                          //开始根据表单中每个字段的类型,进行不同的渲染,此部分比较复杂,注意代码改动.
                          if (FieldArray.type == "input"
                            || FieldArray.type == "email"
                            || FieldArray.type == "number"
                            || FieldArray.type == "date"
                            || FieldArray.type == "month"
                            || FieldArray.type == "time"
                            || FieldArray.type == "datetime"
                            || FieldArray.type == "slider"
                            || FieldArray.type == "readonly"
                            || FieldArray.type == "autoincrement"
                            || FieldArray.type == "autoincrementdate"
                          ) {
                            if (CellData.Value == "1971-01-01" || CellData.Value == "1971-01-01 00:00:00" || CellData.Value == "1971-01") {
                              CellData.Value = "";
                            }
                            console.log("CellData.Value", CellData)
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }//end if
                          else if (FieldArray.type == "password" || FieldArray.type == "comfirmpassword") {
                            // Nothing to do                
                          }
                          else if (FieldArray.type == "select" || FieldArray.type == "autocomplete" || FieldArray.type == "radiogroup") {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }
                          else if (FieldArray.type == "autocompletemulti") {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }
                          else if (FieldArray.type == "checkbox") {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }
                          else if (FieldArray.type == "textarea") {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }
                          else if (FieldArray.type == "avatar" && CellData.Value != undefined) {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>
                                <Box sx={{ display: 'flex', alignItems: 'center',cursor: 'pointer',':hover': {cursor: 'pointer',}, }} onClick={() => toggleImagesPreviewListDrawer([authConfig.backEndApiHost+CellData.Value])}>
                                  <ImgStyled src={authConfig.backEndApiHost+CellData.Value} alt={FieldArray.helptext} />
                                </Box>
                                </MUITableCell>
                              </Fragment>
                            )
                          }
                          else if (FieldArray.type == "files" && CellData.Value != undefined) {
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>
                                  {CellData.Value && CellData.Value.length>0 && CellData.Value.map((FileUrl: any)=>{
                                    return (
                                      <ListItem key={FileUrl['name']} style={{padding: "3px"}}>
                                      <div className='file-details' style={{display: "flex"}}>
                                          <div style={{padding: "0 3px 0 0"}}>
                                          {FileUrl.type.startsWith('image') ? <img width={45} height={45} alt={FileUrl['name']} src={authConfig.backEndApiHost+FileUrl['webkitRelativePath']} /> : <Icon icon='mdi:file-document-outline' fontSize={28}/> }
                                          </div>
                                          <div>
                                          {FileUrl['type']=="file" ? 
                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+FileUrl['webkitRelativePath']} download={FileUrl['name']}>{FileUrl['name']}</CustomLink></Typography>
                                          :
                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+FileUrl['webkitRelativePath']} download={FileUrl['name']} target="_blank">{FileUrl['name']}</CustomLink></Typography>
                                          }
                                          
                                          {FileUrl['size']>0 ? 
                                            <Typography className='file-size' variant='body2'>
                                                {Math.round(FileUrl['size'] / 100) / 10 > 1000
                                                ? `${(Math.round(FileUrl['size'] / 100) / 10000).toFixed(1)} mb`
                                                : `${(Math.round(FileUrl['size'] / 100) / 10).toFixed(1)} kb`}
                                            </Typography>
                                            : ''
                                          }                                        
                                          </div>
                                      </div>
                                      </ListItem>
                                      )
                                  })}                                
                                </MUITableCell>
                              </Fragment>
                            )
                          }
                          else if (FieldArray.type == "editor") {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }    
                          else if (FieldArray.type == "tablefiltercolor" ||
                                  FieldArray.type == "tablefilter" ||
                                  FieldArray.type == "radiogroup" ||
                                  FieldArray.type == "radiogroupcolor"
                                  ) {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell sx={{ minWidth: 140 }}>{FieldArray.label}:</MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }                      
                          else {
                            
                            return (
                              <Fragment key={FieldArray_index}>
                                <MUITableCell>{FieldArray.label}: {FieldArray.type} </MUITableCell>
                                <MUITableCell>{CellData.Value}</MUITableCell>
                              </Fragment>
                            )
                          }
                          
                        })}
                      </TableRow>
                    )

                  })}

                </TableBody>
              </Table>


              {childTable && childTable.allFields && childTable.data ?
                <Table>
                  <TableBody>
                    <TableRow key="ChildTableTableRow">
                      {childTable.allFields && childTable.allFields.Default.map((Item: any, Index: number) => {
                        return <MUITableCell>{Item.code? Item.code : Item.name}</MUITableCell>
                      })}
                    </TableRow>
                    {childTable.data && childTable.data.map((RowItem: any, RowIndex: number) => {
                      return (
                        <TableRow key={RowIndex}>
                          {childTable.allFields && childTable.allFields.Default.map((Item: any, Index: number) => {
                            return <MUITableCell>{Item.type=="autocomplete" ? RowItem[Item.code? Item.code : Item.name] : RowItem[Item.name]}</MUITableCell>
                          })}
                        </TableRow>
                      )
                    })}
                  </TableBody>
                </Table>
                : ''
              }
            </Grid>
          </CardContent>
        </Card>
    </Fragment>
  )
}

export default ViewTableCore
