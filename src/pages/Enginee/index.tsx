// ** React Imports
import { useState, useEffect, useCallback, Fragment } from 'react'

// ** Next Imports
import Link from 'next/link'

// ** MUI Imports
import Box from '@mui/material/Box'
import Card from '@mui/material/Card'
import Grid from '@mui/material/Grid'
import Button from '@mui/material/Button'
import Tooltip from '@mui/material/Tooltip'
import { DataGridPro, GridSortModel, GridCellEditCommitParams, GridFilterModel, GridRowId, GridPinnedColumns, LicenseInfo, zhCN, zhTW, enUS } from '@mui/x-data-grid-pro'
import { styled } from '@mui/material/styles'
import IconButton from '@mui/material/IconButton'
import Typography from '@mui/material/Typography'
import CardHeader from '@mui/material/CardHeader'
import ListItem from '@mui/material/ListItem'
import CircularProgress from '@mui/material/CircularProgress'
import CustomAvatar from 'src/@core/components/mui/avatar'

import Dialog from '@mui/material/Dialog'
import DialogTitle from '@mui/material/DialogTitle'
import DialogContent from '@mui/material/DialogContent'
import DialogActions from '@mui/material/DialogActions'
import DialogContentText from '@mui/material/DialogContentText'

LicenseInfo.setLicenseKey('e58f956ba402f45d4e8554db4fdd17a2Tz02MjA0MyxFPTE3MTA0NTk2ODI4NDcsUz1wcm8sTE09c3Vic2NyaXB0aW9uLEtWPTI=');

// ** Icon Imports
import Icon from 'src/@core/components/icon'

// ** Store Imports
import { useDispatch, useSelector } from 'react-redux'
import { createAsyncThunk } from '@reduxjs/toolkit'

// ** Config
import authConfig from 'src/configs/auth'

// ** Custom Components Imports
import CustomChip from 'src/@core/components/mui/chip'

// ** Third Party Components
import axios from 'axios'
import toast from 'react-hot-toast'

// ** Myself file
import IndexTableHeader from 'src/pages/Enginee/IndexTableHeader'
import AddOrEditTable from './AddOrEditTable'
import ViewTable from './ViewTable'
import ImagesPreview from './ImagesPreview'
import IndexBottomFlowNode from './IndexBottomFlowNode'
import { RootState, AppDispatch } from 'src/store/index'

export type InvoiceLayoutProps = {
  backEndApi: string
  externalId: string | undefined
}

const StyledLink = styled(Link)(({ theme }) => ({
  fontWeight: 600,
  fontSize: '1rem',
  cursor: 'pointer',
  textDecoration: 'none',
  color: theme.palette.text.secondary,
  '&:hover': {
    color: theme.palette.primary.main
  }
}))

interface AddTableType{
  backEndApi:string
  externalId:string
}

const ImgStyled = styled('img')(() => ({
  width: 32,
  height: 32,
  borderRadius: 4
}))

const UserList = ({ backEndApi, externalId }: AddTableType) => {
  // ** Props
  const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!
  
  // ** State
  const [isLoading, setIsLoading] = useState(false);
  const [isLoadingTip, setIsLoadingTip] = useState(false);
  const [isLoadingTipText, setIsLoadingTipText] = useState("");
  const [forceUpdate, setForceUpdate] = useState(0);
  const [viewActionOpen, setViewActionOpen] = useState<boolean>(false)
  const [addEditActionOpen, setAddEditActionOpen] = useState<boolean>(false)
  const [addEditActionName, setAddEditActionName] = useState<string>('')
  const [addEditActionId, setAddEditActionId] = useState<string>('')
  const [editViewCounter, setEditViewCounter] = useState<number>(1)
  const [addEditViewShowInWindow, setAddEditViewShowInWindow] = useState<boolean>(false)
  const [imagesPreviewOpen, setImagesPreviewOpen] = useState<boolean>(false)
  const [imagesPreviewList, setImagesPreviewList] = useState<string[]>([])
  const [imagesType, setImagesType] = useState<string[]>([])

  const [filterMultiColumns, setFilterMultiColumns] = useState<GridFilterModel>()
  const [searchFieldName, setsearchFieldName] = useState<string>('')
  const [searchFieldValue, setsearchFieldValue] = useState<string>('')
  const [selectedRows, setSelectedRows] = useState<GridRowId[]>([])
  const [sortMethod, setSortMethod] = useState<string>('desc')
  const [sortColumn, setSortColumn] = useState<string>('')

  const [allSubmitFields, setAllSubmitFields] = useState({ 'searchFieldName': '' });

  const [pageSize, setPageSize] = useState<number>(30)
  const [page, setPage] = useState<number>(0)

  //const [filter, setFilter] = useState<any[]>([])

  const handleIsLoadingTipChange = (status: boolean, showText: string) => {
    setIsLoadingTip(status)
    setIsLoadingTipText(showText)
  }

  const handleFilterChange = (field: any, value: string) => {
    const newAllFilters = JSON.parse(JSON.stringify(allSubmitFields))
    newAllFilters[field] = value
    setAllSubmitFields(newAllFilters)
    
    //const filterNew = JSON.parse(JSON.stringify(store.filter))    
    //filterNew[field] = value
    //setFilter(filterNew)
  }

  //console.log("process.env.NEXT_PUBLIC_JWT_REFRESH_TOKEN_SECRET", process.env.NEXT_PUBLIC_JWT_REFRESH_TOKEN_SECRET)

  // ** Hooks
  const dispatch = useDispatch<AppDispatch>()
  const store:{[key:string]:any} = useSelector((state: RootState) => state.user)

  const fetchData = createAsyncThunk('appUsers/fetchData', async (params: any) => {
    const newAllFilters = JSON.parse(JSON.stringify(params['allSubmitFields']))
    newAllFilters['externalId'] = params['externalId']
    newAllFilters['page'] = params['page']
    newAllFilters['pageSize'] = params['pageSize']
    newAllFilters['searchFieldName'] = params['searchFieldName']
    newAllFilters['searchFieldValue'] = params['searchFieldValue']
    newAllFilters['sortMethod'] = params['sortMethod']
    newAllFilters['sortColumn'] = params['sortColumn']
    if (params['filterMultiColumns'] != undefined) {
      newAllFilters['filterMultiColumns'] = JSON.parse(JSON.stringify(params['filterMultiColumns']))
    }

    if (storedToken) {
      setIsLoading(true)
      const response = await axios.get(authConfig.backEndApiHost + backEndApi, {
        headers: {
          Authorization: storedToken
        },
        params: newAllFilters
      })
      if(response.data && response.data.init_action.action.indexOf("view_default") != -1) {
        setAddEditActionName(response.data.init_action.action)
        setAddEditActionId(response.data.init_action.id)
        setViewActionOpen(!viewActionOpen)
        setEditViewCounter(0)
        setAddEditViewShowInWindow(true)
      }
      else if(response.data && response.data.init_action.action.indexOf("edit_default") != -1) {
        setAddEditActionName(response.data.init_action.action)
        setAddEditActionId(response.data.init_action.id)
        setAddEditActionOpen(!addEditActionOpen)
        setAddEditViewShowInWindow(true)
      }
      else if(response.data && response.data.init_action.action.indexOf("add_default") != -1) {
        setAddEditActionName(response.data.init_action.action)
        setAddEditActionId(response.data.init_action.id)
        setAddEditActionOpen(!addEditActionOpen)
        setAddEditViewShowInWindow(true)
      }

      setIsLoadingTipText(response.data.export_default.ExportLoading)
      
      //setFilter(response.data.init_default.filter)
      setIsLoading(false);
      setPageSize(response.data.init_default.pageNumber)
      
      return response.data
    }
    else {
      
      return []
    }
  })

  useEffect(() => {
    dispatch(
      fetchData({
        searchFieldName: searchFieldName,
        searchFieldValue: searchFieldValue,
        allSubmitFields: allSubmitFields,
        filterMultiColumns: filterMultiColumns,
        page: page,
        pageSize: pageSize,
        sortMethod: sortMethod,
        sortColumn: sortColumn,
        forceUpdate: forceUpdate,
        externalId: externalId
      })
    ).then();
  }, [dispatch, searchFieldName, searchFieldValue, allSubmitFields, page, pageSize, sortMethod, sortColumn, forceUpdate, filterMultiColumns, externalId])

  useEffect(() => {
    window.addEventListener('resize', () => {
      setInnerHeight(window.innerHeight)
    })
  }, [])
  const [innerHeight, setInnerHeight] = useState<number | string>(window.innerHeight)
  console.log("innerHeight",innerHeight)


  const onFilterColumnChangeMulti = useCallback((filterModel: GridFilterModel) => {
    setFilterMultiColumns(JSON.parse(JSON.stringify(filterModel)))
  }, [])

  //const FilterStateMap = {}
  //这个地方只能写一个常量,不能写变量,否则会出错.
  //for (let i = 0; i < 200; i++) {
  //  FilterStateMap['Filter_' + i] = useState('', "Filter_" + i);
  //}

  const tableHeaderHandleFilter = useCallback((val: any) => {
    setsearchFieldName(val.searchFieldName)
    setsearchFieldValue(val.searchFieldValue)
  }, [])

  const multiReviewHandleFilter = useCallback((action: string, multiReviewInputValue: string, selectedRows: GridRowId[], CSRF_TOKEN:string) => {
    const formData = new FormData();
    formData.append('multiReviewInputValue', multiReviewInputValue);
    formData.append('selectedRows', selectedRows.join(','));
    formData.append('externalId', externalId);
    fetch(
      authConfig.backEndApiHost + backEndApi + "?action=" + action,
      {
        headers: {
          Authorization: storedToken+"::::"+CSRF_TOKEN
        },
        method: 'POST',
        body: formData,
      }
    )
      .then((response) => response.json())
      .then((result) => {
        console.log('Success:', result);
        if (result.status == "OK") {
          toast.success(result.msg)
          setForceUpdate(Math.random())
          setSelectedRows([])
        }
        else {
          toast.error(result.msg)
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        toast.error("Network Error!");
      });

  }, [])

  const addUserHandleFilter = useCallback(() => {
    setsearchFieldValue("")
    setForceUpdate(Math.random())
    setAddEditActionId('')
    setEditViewCounter(0)
  }, [])

  const toggleImportTableDrawer = () => {
    setAddEditActionName('import_default')
    setAddEditActionOpen(!addEditActionOpen)
  }

  const toggleExportTableDrawer = () => {
    setIsLoadingTip(true)
    setIsLoadingTipText(store.export_default.ExportLoading)
    fetch(authConfig.backEndApiHost + store.export_default.exportUrl)
    .then(response => response.blob())
    .then(blob => {
      const downloadLink = document.createElement('a');
      downloadLink.href = URL.createObjectURL(blob);
      downloadLink.download = store.export_default.titletext;
      downloadLink.style.display = 'none';
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
      console.log('File download completed');
      setIsLoadingTip(false)
    })
    .catch(error => {
      console.error('File download failed:', error);
      setIsLoadingTip(false)
    });
  }


  const toggleAddTableDrawer = () => {
    setAddEditActionName('add_default')
    setAddEditActionOpen(!addEditActionOpen)
  }

  const toggleEditTableDrawer = () => {
    setAddEditActionName('edit_default')
    setAddEditActionOpen(!addEditActionOpen)
  }

  const toggleViewTableDrawer = () => {
    setAddEditActionName('view_default')
    setViewActionOpen(!viewActionOpen)
  }

  const toggleImagesPreviewDrawer = () => {
    setImagesPreviewOpen(!imagesPreviewOpen)
  }

  const toggleImagesPreviewListDrawer = (imagesPreviewList: string[], imagesType: string[]) => {
    setImagesPreviewOpen(!imagesPreviewOpen)
    setImagesPreviewList(imagesPreviewList)
    setImagesType(imagesType)
  }

  const togglePageActionDrawer = (action: string, id: string) => {
    setAddEditActionName(action)
    switch (action) {
      case 'edit_default':
        setAddEditActionId(id)
        setAddEditActionOpen(!addEditActionOpen)
        break;
      case 'view_default':
        setAddEditActionId(id)
        setViewActionOpen(!viewActionOpen)
        setEditViewCounter(0)
        break;
      case 'delete_array':
        setSelectedRows([id])
        handleMultiOpenDialog("delete_array")
        break;
    }
    if (action != "edit_default" && action.indexOf("edit_default") != -1) {
      setAddEditActionId(id)
      setAddEditActionOpen(!addEditActionOpen)
    }

  }

  const handleSortModel = (newModel: GridSortModel) => {
    if (newModel.length) {
      const newModelItem = newModel[0]
      setSortMethod(String(newModelItem.sort))
      setSortColumn(String(newModelItem.field))
    } else {
      setSortMethod('asc')
      setSortColumn(store.columns[0].field)
    }
  }

  if (store.init_default.searchtitle != undefined) {
    document.title = store.init_default.searchtitle;
  }

  const addDefault:{[key:string]:any} = {}

  const [pinnedColumns, setPinnedColumns] = useState<GridPinnedColumns>({'left':[],'right':[]});
  useEffect(() => {
    setPinnedColumns(store.init_default.pinnedColumns)
  }, [])
  const handlePinnedColumnsChange = useCallback(
    (updatedPinnedColumns: GridPinnedColumns) => {
      setPinnedColumns(updatedPinnedColumns)
    },
    [],
  );

  const [multiReviewInputValue, setMultiReviewInputValue] = useState<string>('')
  const handleMultiReviewAction = (action: string, selectedRows: GridRowId[], CSRF_TOKEN: string) => {
    multiReviewHandleFilter(action, multiReviewInputValue, selectedRows, CSRF_TOKEN)
    setMultiReviewInputValue('')
  }
  const [multiReviewOpenDialog, setMultiReviewOpenDialog] = useState(addDefault)
  const handleMultiOpenDialog = (action: string) => {
    const multiReviewOpenDialogNew:{[key:string]:any} = {}
    store.init_default.rowdelete.map((Item: any) => {
      multiReviewOpenDialogNew[Item.action] = false
    })
    multiReviewOpenDialogNew[action] = true
    setMultiReviewOpenDialog(multiReviewOpenDialogNew)
  }
  const handleMultiCloseDialog = () => {
    const multiReviewOpenDialogNew:{[key:string]:any} = {}
    store.init_default.rowdelete.map((Item: any) => {
      multiReviewOpenDialogNew[Item.action] = false
    })
    setMultiReviewOpenDialog(multiReviewOpenDialogNew)
  }
  const handleMultiCloseDialogAndSubmit = (action: string, selectedRows: GridRowId[], CSRF_TOKEN: string) => {
    handleMultiCloseDialog()
    handleMultiReviewAction(action, selectedRows, CSRF_TOKEN)
  }

  // set datagrid language
  const dataGridLanguageCode = store.init_default.dataGridLanguageCode
  const dataGridLanguageText:{[key:string]:any} = {}
  switch (dataGridLanguageCode) {
    case 'zhCN':
      dataGridLanguageText['localeText'] = zhCN.components.MuiDataGrid.defaultProps.localeText
      break;
    case 'zhTW':
      dataGridLanguageText['localeText'] = zhTW.components.MuiDataGrid.defaultProps.localeText
      break;
    case 'enUS':
    default:
      dataGridLanguageText['localeText'] = enUS.components.MuiDataGrid.defaultProps.localeText
      break;
  }

  const columns_for_datagrid:any[] = []

  type rowType = {
    [key:string]:string
  }
  interface CellType {
    row: rowType
  }
  const CustomLink = styled(Link)({
    textDecoration: "none",
    color: "inherit",
  });
  
  // set table every row actions, [edit, delete, or others] href={`?action=${action.action}&id=${row.id}`}
  store.columns.map((column: any, column_index: number) => {
    if (column && column.type == "actions" && column.actions) {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: CellType) => (
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          {column.actions.map((action: any, action_index: number) => {
            switch (action.action) {
              case 'view_default':
                if (!store.init_default.ForbiddenViewRow.includes(row.id)) {
                  
                  return (
                    <Tooltip title={action.text} key={"ColumnRenderCell" + action_index}>
                      <IconButton size='small' onClick={() => togglePageActionDrawer(action.action, row.id)}>
                        <Icon icon={action.mdi} fontSize={20} />
                      </IconButton>
                    </Tooltip>
                  )
                }
                break;
              case 'edit_default':
                if (!store.init_default.ForbiddenEditRow.includes(row.id)) {
                  
                  return (
                    <Tooltip title={action.text} key={"ColumnRenderCell" + action_index}>
                      <IconButton size='small' onClick={() => togglePageActionDrawer(action.action, row.id)}>
                        <Icon icon={action.mdi} fontSize={20} />
                      </IconButton>
                    </Tooltip>
                  )
                }
                break;
              case 'delete_array':
                if (!store.init_default.ForbiddenDeleteRow.includes(row.id)) {
                  
                  return (
                    <Tooltip title={action.text} key={"ColumnRenderCell" + action_index}>
                      <IconButton size='small' onClick={() => togglePageActionDrawer(action.action, row.id)}>
                        <Icon icon={action.mdi} fontSize={20} />
                      </IconButton>
                    </Tooltip>
                  )
                }
                break;
              default:
                  if (!store.init_default.ForbiddenEditRow.includes(row.id)) {
                    
                    return (
                      <Tooltip title={action.text} key={"ColumnRenderCell" + action_index}>
                        <IconButton size='small' onClick={() => togglePageActionDrawer(action.action, row.id)}>
                          <Icon icon={action.mdi} fontSize={20} />
                        </IconButton>
                      </Tooltip>
                    )
                  }
                  break;
            }

          })}
        </Box>
      )
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && column.type == "actionInRow") {
      const columnRenderCell = { ...column }
      const columnTempArray = [column.action]
      columnRenderCell['renderCell'] = ({ row }: any) => (
          <Box sx={{ display: 'flex', alignItems: 'center', '& svg': { mr: 3, color: column.urlcolor } }}>
            <Icon icon={column.urlmdi} fontSize={20} />
            <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
              {columnTempArray.map((action: any, action_index: number) => {
                switch (action) {
                  case 'view_default':
                    if (!store.init_default.ForbiddenViewRow.includes(row.id)) {
                      
                      return (
                        <IconButton size='small' onClick={() => togglePageActionDrawer(action, row.id)} key={action_index}>
                          <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
                            {row[column.field]}
                          </Typography>
                        </IconButton>
                      )
                    }
                    break;
                  case 'edit_default':
                    if (!store.init_default.ForbiddenEditRow.includes(row.id)) {
                      
                      return (
                          <IconButton size='small' onClick={() => togglePageActionDrawer(action, row.id)} key={action_index}>
                            <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
                              {row[column.field]}
                            </Typography>
                          </IconButton>
                      )
                    }
                    break;
                  case 'delete_array':
                    if (!store.init_default.ForbiddenDeleteRow.includes(row.id)) {
                      
                      return (
                        <IconButton size='small' onClick={() => togglePageActionDrawer(action, row.id)} key={action_index}>
                          <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
                            {row[column.field]}
                          </Typography>
                        </IconButton>
                      )
                    }
                    break;
                  default:
                      if (!store.init_default.ForbiddenEditRow.includes(row.id)) {
                        
                        return (
                          <IconButton size='small' onClick={() => togglePageActionDrawer(action, row.id)} key={action_index}>
                            <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
                              {row[column.field]}
                            </Typography>
                          </IconButton>
                        )
                      }
                      break;
                }

              })}
            </Typography>
          </Box>
      )
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && column.type == "url") {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => (
        <StyledLink href={`${column.href}${row.id}`} target={column.target}>
          <Box sx={{ display: 'flex', alignItems: 'center', '& svg': { mr: 3, color: column.urlcolor } }}>
            <Icon icon={column.urlmdi} fontSize={20} />
            <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
              {row[column.field]}
            </Typography>
          </Box>
        </StyledLink>
      )
      columns_for_datagrid[column_index] = columnRenderCell
    }    
    else if (column && column.type == "ExternalUrl") {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => (
        <StyledLink href={`${authConfig.backEndApiHost}${row[column.field].replace("[id]",row.id)}`} target={column.target}>
          <Box sx={{ display: 'flex', alignItems: 'center', '& svg': { mr: 3, color: column.urlcolor } }}>
            <Icon icon={column.urlmdi} fontSize={20} />
            <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
              {row[column.field]}
            </Typography>
          </Box>
        </StyledLink>
      )
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && column.type == "api") {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => (
        <Box sx={{ display: 'flex', alignItems: 'center', '& svg': { mr: 3, color: column.apicolor } }}>
          <Tooltip title={column.headerName}>
            <IconButton size='small' onClick={() => togglePageActionDrawer(column.apiaction, row.id)}>
              <Icon icon={column.apimdi} fontSize={20} />
              <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
                {column.headerName}
              </Typography>
            </IconButton>
          </Tooltip>
        </Box>
      )
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && column.type == "avatar") {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => {
        return (
          row[column.field] != "" ?
            (
              <Box sx={{ display: 'flex', alignItems: 'center',cursor: 'pointer',':hover': {cursor: 'pointer',}, }}  onClick={() => toggleImagesPreviewListDrawer([authConfig.backEndApiHost+row[column.field]], ['image'])}>
                <CustomAvatar src={authConfig.backEndApiHost+row[column.field]} sx={{ mr: 3, width: 30, height: 30 }} />
              </Box>
            )
            :
            null
        )
      }
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && column.type == "approvalnode") {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => {
        
        return (
          row[column.field] != "" && row[column.field.replace("审核状态", "审核时间")] != "" ?
            (
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <CustomAvatar src={row.avatar || '/images/avatars/1.png'} sx={{ mr: 3, width: 30, height: 30 }} />
                <Box sx={{ display: 'flex', alignItems: 'flex-start', flexDirection: 'column' }}>
                  {row[column.field]} ({row[column.field.replace("审核状态", "审核人")]})
                  <Typography noWrap variant='caption'>
                    {row[column.field.replace("审核状态", "审核意见")]} ({row[column.field.replace("审核状态", "审核时间")]})
                  </Typography>
                </Box>
              </Box>
            )
            :
            null
        )
      }
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && column.type == "files") {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => {

        return (
          <Fragment>
          {row[column.field] && row[column.field].length>0 && row[column.field].map((FileUrl: any)=>{
            
            return (
              <ListItem key={FileUrl['name']} style={{padding: "3px"}}>
              <div className='file-details' style={{display: "flex"}}>
                  <div style={{padding: "3px 3px 0 0"}}>
                    {FileUrl.type.startsWith('image') ? 
                    <Box sx={{ display: 'flex', alignItems: 'center',cursor: 'pointer',':hover': {cursor: 'pointer',}, }} onClick={() => toggleImagesPreviewListDrawer([authConfig.backEndApiHost+FileUrl['webkitRelativePath']], ['image'])}>
                      <ImgStyled src={authConfig.backEndApiHost+FileUrl['webkitRelativePath']} />
                    </Box>
                    : <Icon icon='mdi:file-document-outline' fontSize={28}/> }
                    </div>
                    <div>
                    {FileUrl['type']=="pdf" || FileUrl['type']=="Excel" ? 
                      <Box sx={{ display: 'flex', alignItems: 'center',cursor: 'pointer',':hover': {cursor: 'pointer',}, }} onClick={() => toggleImagesPreviewListDrawer([authConfig.backEndApiHost+FileUrl['webkitRelativePath']], [FileUrl['type']])}>
                        {FileUrl['name']}
                      </Box>
                    :
                      ''
                    }      
                    {FileUrl['type']=="file" ? 
                      <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+FileUrl['webkitRelativePath']} download={FileUrl['name']}>{FileUrl['name']}</CustomLink></Typography>
                    :
                      ''
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
          </Fragment>
        )
      }
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && (column.type == "radiogroupcolor")) {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => {
        
        return (
          row[column.field] != undefined &&row[column.field] != "" ?
            (
              <CustomChip
                skin='light'
                size='small'
                label={row[column.field]}
                color={column.color[row[column.field]]}
                sx={{ textTransform: 'capitalize' }}
              />
            )
            :
            null
        )
      }
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else if (column && (column.type == "tablefiltercolor") && column.color) {
      const columnRenderCell = { ...column }
      columnRenderCell['renderCell'] = ({ row }: any) => {
        
        return (
          row[column.field] != undefined && row[column.field] != "" ?
            (
              <Box sx={{ display: 'flex', alignItems: 'center', '& svg': { mr: 3, color: column.color[row[column.field]] && column.color[row[column.field]].color ? column.color[row[column.field]].color : "info.main" } }}>
                <Icon icon={column.color[row[column.field]] && column.color[row[column.field]].icon ? column.color[row[column.field]].icon : 'pencil-outline'} fontSize={20} />
                <Typography noWrap sx={{ color: 'text.secondary', textTransform: 'capitalize' }}>
                  {row[column.field]}
                </Typography>
              </Box>
            )
            :
            null
        )
      }
      columns_for_datagrid[column_index] = columnRenderCell
    }
    else {
      const columnRenderCell = { ...column }
      columns_for_datagrid[column_index] = columnRenderCell
    }
  })
  
  //console.log("store.init_default.ApprovalNodeFields",store.init_default.ApprovalNodeFields)
  //console.log("addEditActionId-addEditActionId-addEditActionId",store, addEditActionName)
  
  return (
    <Grid container spacing={6}>
      {store && store.init_action.action == 'init_default' ? 
      <Grid item xs={12}>
        <Card>
          {store.init_default.returnButton && store.init_default.returnButton.status ?
            <Grid sx={{ pr: 3, pb: 0, display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between' }}>
              <CardHeader title={store.init_default.searchtitle} />
              <Grid sx={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center' }}>
                <Button sx={{ mb: 2 }} variant='outlined' size='small' onClick={() => { window.history.back(); }}>{store.init_default.returnButton.text}</Button>
              </Grid>
            </Grid>
            :
            <CardHeader title={store.init_default.searchtitle} sx={{ pb: 2, pt: 3 }}/>
          }
          {store && store.init_default && store.init_default.rowdelete && store.init_default.rowdelete.map((Item: any, index: number) => {
            
            return (
              <Grid item key={"Grid_" + index}>
                <Fragment>
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
                    </DialogContent>
                    <DialogActions className='dialog-actions-dense'>
                      <Button onClick={() => handleMultiCloseDialog()}>{Item.cancel}</Button>
                      <Button onClick={() => { handleMultiCloseDialogAndSubmit(Item.action, selectedRows, store.init_default.CSRF_TOKEN) }} variant='contained'>{Item.submit}</Button>
                    </DialogActions>
                  </Dialog>
                </Fragment>
              </Grid>
            )
          })}

          {store && store.init_default && store.init_default.searchFieldText && store.init_default.searchFieldArray && store.init_default.searchFieldArray.length>0 && isLoadingTip==false ? <IndexTableHeader filter={store.init_default.filter} handleFilterChange={handleFilterChange} value={searchFieldName} handleFilter={tableHeaderHandleFilter} toggleAddTableDrawer={toggleAddTableDrawer} toggleImportTableDrawer={toggleImportTableDrawer} toggleExportTableDrawer={toggleExportTableDrawer} searchFieldText={store.init_default.searchFieldText} searchFieldArray={store.init_default.searchFieldArray} selectedRows={selectedRows} multireview={store.init_default.multireview} multiReviewHandleFilter={multiReviewHandleFilter} button_search={store.init_default.button_search} button_add={store.init_default.button_add} button_import={store.init_default.button_import} button_export={store.init_default.button_export} isAddButton={store && store.add_default && store.add_default.allFields ? true : false} isImportButton={store && store.import_default && store.import_default.allFields ? true : false} isExportButton={store && store.export_default && store.export_default.allFields && store.export_default.exportUrl ? true : false} CSRF_TOKEN={store.init_default.CSRF_TOKEN}/> : ''}

          {isLoadingTip ?
            <Grid item xs={12} sm={12} container justifyContent="space-around">
                <Box sx={{ mt: 6, mb: 6, display: 'flex', alignItems: 'center', flexDirection: 'column' }}>
                    <CircularProgress />
                    <Typography sx={{pt:5,pb:5}}>{isLoadingTipText}</Typography>
                </Box>
            </Grid>
          :
            <DataGridPro
              page={page}
              autoHeight
              pagination
              rows={store.data}
              rowCount={store.total}
              rowHeight={Number(store.init_default.rowHeight)}
              columns={columns_for_datagrid}
              checkboxSelection={store.init_default.checkboxSelection?true:false}
              disableSelectionOnClick
              pageSize={pageSize}
              sortingMode='server'
              paginationMode='server'
              onSortModelChange={handleSortModel}
              rowsPerPageOptions={store.pageNumberArray}
              onPageChange={newPage => setPage(newPage)}
              onPageSizeChange={(newPageSize: number) => setPageSize(newPageSize)}
              selectionModel={selectedRows}
              onSelectionModelChange={rows => setSelectedRows(rows)}
              loading={isLoading}
              filterMode="server"
              onFilterModelChange={onFilterColumnChangeMulti}
              isRowSelectable={(params) => !store.init_default.ForbiddenSelectRow.includes(params.id)}
              onCellEditCommit={(props:GridCellEditCommitParams) => {
                const { id, field, value } = props;
                const formData = new FormData();
                formData.append('id', String(id));
                formData.append('field', field);
                formData.append('value', value);
                formData.append('externalId', externalId);
                fetch(
                  authConfig.backEndApiHost + backEndApi + '?action=updateone',
                  {
                    headers: {
                      Authorization: storedToken+"::::"+store.init_default.CSRF_TOKEN
                    },
                    method: 'POST',
                    body: formData,
                  }
                )
                  .then((response) => response.json())
                  .then((result) => {
                    console.log('Success:', result);
                    if (result.status == "OK") {
                      toast.success(result.msg)
                    }
                    else {
                      toast.error(result.msg)
                    }
                  })
                  .catch((error) => {
                    console.error('Error:', error);
                    toast.error("Network Error!");
                  });
              }}
              pinnedColumns={pinnedColumns}
              onPinnedColumnsChange={handlePinnedColumnsChange}
              localeText={dataGridLanguageText['localeText']}
            />
          }
        </Card>
        { (store.init_default.ApprovalNodeFields && store.init_default.ApprovalNodeFields.AllNodes && store.init_default.ApprovalNodeFields.CurrentNode && store.init_default.ApprovalNodeFields.ApprovalNodeTitle) || (store.init_default.ApprovalNodeFields.DebugSql) ? 
          (
          <Grid item xs={12} sx={{mt: 2}}>
            <IndexBottomFlowNode ApprovalNodeFields={store.init_default.ApprovalNodeFields.AllNodes} ApprovalNodeCurrentField={store.init_default.ApprovalNodeFields.CurrentNode} ActiveStep={store.init_default.ApprovalNodeFields.ActiveStep} ApprovalNodeTitle={store.init_default.ApprovalNodeFields.ApprovalNodeTitle} DebugSql={store.init_default.ApprovalNodeFields.DebugSql} Memo={store.init_default.ApprovalNodeFields.Memo} />
          </Grid>
          )
          : '' 
        }
      </Grid>
      : '' } 
      {store && store.import_default && store.import_default.defaultValues && addEditActionName.indexOf("import_default") != -1 ? <AddOrEditTable externalId={Number(externalId)} id={addEditActionId} action={addEditActionName} addEditStructInfo={store.import_default} open={addEditActionOpen} toggleAddTableDrawer={toggleImportTableDrawer} addUserHandleFilter={addUserHandleFilter} backEndApi={backEndApi} editViewCounter={editViewCounter + 1} IsGetStructureFromEditDefault={0} addEditViewShowInWindow={addEditViewShowInWindow}  CSRF_TOKEN={store.init_default.CSRF_TOKEN} dataGridLanguageCode={store.init_default.dataGridLanguageCode} dialogMaxWidth={store.init_default.dialogMaxWidth} toggleImagesPreviewListDrawer={toggleImagesPreviewListDrawer} handleIsLoadingTipChange={handleIsLoadingTipChange} /> : ''}
      {store && store.add_default && store.add_default.defaultValues && addEditActionName.indexOf("add_default") != -1 ? <AddOrEditTable externalId={Number(externalId)} id={addEditActionId} action={addEditActionName} addEditStructInfo={store.add_default} open={addEditActionOpen} toggleAddTableDrawer={toggleAddTableDrawer} addUserHandleFilter={addUserHandleFilter} backEndApi={backEndApi} editViewCounter={editViewCounter + 1} IsGetStructureFromEditDefault={0} addEditViewShowInWindow={addEditViewShowInWindow}  CSRF_TOKEN={store.init_default.CSRF_TOKEN} dataGridLanguageCode={store.init_default.dataGridLanguageCode} dialogMaxWidth={store.init_default.dialogMaxWidth} toggleImagesPreviewListDrawer={toggleImagesPreviewListDrawer} handleIsLoadingTipChange={handleIsLoadingTipChange} /> : ''}
      {store && store[addEditActionName] && store[addEditActionName]['defaultValues'] && addEditActionName.indexOf("edit_default") != -1 && addEditActionId!='' ? <AddOrEditTable externalId={Number(externalId)} id={addEditActionId} action={addEditActionName} addEditStructInfo={store[addEditActionName]} open={addEditActionOpen} toggleAddTableDrawer={toggleEditTableDrawer} addUserHandleFilter={addUserHandleFilter} backEndApi={backEndApi} editViewCounter={editViewCounter + 1} IsGetStructureFromEditDefault={0} addEditViewShowInWindow={addEditViewShowInWindow}  CSRF_TOKEN={store.init_default.CSRF_TOKEN} dataGridLanguageCode={store.init_default.dataGridLanguageCode} dialogMaxWidth={store.init_default.dialogMaxWidth} toggleImagesPreviewListDrawer={toggleImagesPreviewListDrawer} handleIsLoadingTipChange={handleIsLoadingTipChange} /> : ''}
      {store && store.view_default && store.view_default.defaultValues && addEditActionName.indexOf("view_default") != -1 && addEditActionId!='' ? <ViewTable externalId={Number(externalId)} id={addEditActionId} action={addEditActionName} pageJsonInfor={store[addEditActionName]} open={viewActionOpen} toggleViewTableDrawer={toggleViewTableDrawer} backEndApi={backEndApi} editViewCounter={editViewCounter + 1} addEditViewShowInWindow={addEditViewShowInWindow} CSRF_TOKEN={store.init_default.CSRF_TOKEN} toggleImagesPreviewListDrawer={toggleImagesPreviewListDrawer} dialogMaxWidth={store.init_default.dialogMaxWidth} /> : ''}
      <ImagesPreview open={imagesPreviewOpen} toggleImagesPreviewDrawer={toggleImagesPreviewDrawer} imagesList={imagesPreviewList} imagesType={imagesType} />
    </Grid >
  )
}


export default UserList
