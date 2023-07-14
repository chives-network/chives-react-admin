// ** React Imports
import { useState, useEffect, MouseEvent, ChangeEvent, Fragment, SyntheticEvent, forwardRef, ReactElement, Ref, FocusEvent } from 'react'
import { ElementType,MouseEventHandler } from 'react'

// ** MUI Imports
import Select from '@mui/material/Select'
import Switch from '@mui/material/Switch'
import Button, { ButtonProps } from '@mui/material/Button'
import MenuItem from '@mui/material/MenuItem'
import { styled } from '@mui/material/styles'
import TextField from '@mui/material/TextField'
import IconButton from '@mui/material/IconButton'
import InputLabel from '@mui/material/InputLabel'
import Typography from '@mui/material/Typography'
import List from '@mui/material/List'
import ListItem from '@mui/material/ListItem'
import Box, { BoxProps } from '@mui/material/Box'
import Collapse from '@mui/material/Collapse'
import FormControl from '@mui/material/FormControl'
import FormLabel from '@mui/material/FormLabel'
import Radio from '@mui/material/Radio'
import RadioGroup from '@mui/material/RadioGroup'
import OutlinedInput from '@mui/material/OutlinedInput'
import FormHelperText from '@mui/material/FormHelperText'
import InputAdornment from '@mui/material/InputAdornment'
import CircularProgress from '@mui/material/CircularProgress'
import FormControlLabel from '@mui/material/FormControlLabel'
import Autocomplete from '@mui/material/Autocomplete'
import Checkbox from '@mui/material/Checkbox'
import FormGroup from '@mui/material/FormGroup'
import Slider from '@mui/material/Slider'
import Divider from '@mui/material/Divider';
import Card from '@mui/material/Card'
import CardHeader from '@mui/material/CardHeader'
import CardContent, { CardContentProps } from '@mui/material/CardContent'
import Tooltip from "@mui/material/Tooltip"
import TableContainer from '@mui/material/TableContainer'
import Link from "@mui/material/Link"
import Table from '@mui/material/Table'
import TableRow from '@mui/material/TableRow'
import TableBody from '@mui/material/TableBody'
import TableCell from '@mui/material/TableCell'
import TableHead from '@mui/material/TableHead'
import TabContext from '@mui/lab/TabContext'
import TabPanel from '@mui/lab/TabPanel'
import Dialog from '@mui/material/Dialog'
import DialogContent from '@mui/material/DialogContent'
import { useRouter } from 'next/router'
import Fade, { FadeProps } from '@mui/material/Fade'

//import Tab from '@mui/material/Tab'
//import TabList from '@mui/lab/TabList'

// ** Custom Component Imports
import Repeater from 'src/@core/components/repeater'

// ** Config
import authConfig from 'src/configs/auth'

// ** Third Party Imports
import * as yup from 'yup'
import { yupResolver } from '@hookform/resolvers/yup'
import { useForm, Controller } from 'react-hook-form'

//import { fr } from 'yup-locales';
import { setLocale } from 'yup';
import AddOrEditTableLanguage from 'src/types/forms/AddOrEditTableLanguage';

import axios from 'axios'

import Mousetrap from 'mousetrap'

// ** Icon Imports
import Icon from 'src/@core/components/icon'
import Grid, { GridProps } from '@mui/material/Grid'

// Date Locale
import DatePicker, { registerLocale } from 'react-datepicker'
import { useTranslation } from 'react-i18next'
import { Locale } from 'date-fns'
import en from 'date-fns/locale/en-US'
import zh from 'date-fns/locale/zh-CN'

const langObj: { [key: string]: Locale } = { en, zh }

// ** Date Style Imports
import DatePickerWrapper from 'src/@core/styles/libs/react-datepicker'

// ** Third Party Imports
import { convertFromHTML, ContentState, EditorState } from 'draft-js'
import { convertToHTML } from 'draft-convert';
import toast from 'react-hot-toast'

// ** Component Import
import ReactDraftWysiwyg from 'src/@core/components/react-draft-wysiwyg'
import { EditorWrapper } from 'src/@core/styles/libs/react-draft-wysiwyg'

import DropzoneWrapper from 'src/@core/styles/libs/react-dropzone'
import { useDropzone } from 'react-dropzone'

// ** Styles
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css'

import chinacity from 'src/types/forms/chinacity';
import mdi from 'src/types/forms/mdi';

// ** Tab Content Imports
import IndexJumpDialogWindow from 'src/pages/Enginee/IndexJumpDialogWindow'

const RepeatingContent = styled(Grid)<GridProps>(({ theme }) => ({
    paddingRight: 0,
    display: 'flex',
    position: 'relative',
    borderRadius: theme.shape.borderRadius,
    border: `1px solid ${theme.palette.divider}`,
    '& .col-title': {
      top: '-1.5rem',
      position: 'absolute'
    },
    [theme.breakpoints.down('lg')]: {
      '& .col-title': {
        top: '0',
        position: 'relative'
      }
    }
}))

const RepeaterWrapper = styled(CardContent)<CardContentProps>(({ theme }) => ({
    paddingTop: theme.spacing(4),
    paddingBottom: theme.spacing(2),
    '& .repeater-wrapper + .repeater-wrapper': {
        marginTop: theme.spacing(2)
    }
}))

const ChildTableRowAction = styled(Box)<BoxProps>(({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'flex-start',
    padding: theme.spacing(2, 1),
    borderLeft: `1px solid ${theme.palette.divider}`
}))

const Transition = forwardRef(function Transition(
    props: FadeProps & { children?: ReactElement<any, any> },
    ref: Ref<unknown>
  ) {
    return <Fade ref={ref} {...props} />
  })

interface AddOrEditTableType {
    externalId: number
    id: number | string
    action: string
    addEditStructInfo: any
    open: boolean
    toggleAddTableDrawer: () => void
    addUserHandleFilter: () => void
    backEndApi: string
    editViewCounter: number
    IsGetStructureFromEditDefault: number
    AddtionalParams: {[key:string]:any}
    CSRF_TOKEN: string
    dataGridLanguageCode: string
    toggleImagesPreviewListDrawer: (imagesPreviewList: string[]) => void
    handleIsLoadingTipChange: (status: boolean, showText: string) => void
}

const AddOrEditTableCore = (props: AddOrEditTableType) => {
    // ** Props
    const { externalId, id, action, addEditStructInfo, toggleAddTableDrawer, addUserHandleFilter, backEndApi, editViewCounter, IsGetStructureFromEditDefault, AddtionalParams, CSRF_TOKEN, dataGridLanguageCode, toggleImagesPreviewListDrawer, handleIsLoadingTipChange } = props

    //Yup Language
    if(dataGridLanguageCode=="zhCN") {
        setLocale(AddOrEditTableLanguage);
    }

    const router = useRouter();
    
    // ** Hooks
    const addFilesOrDatesDefault:{[key:string]:any} = {}
    const [defaultValuesNew, setDefaultValuesNew] = useState(addFilesOrDatesDefault)
    const [fieldArrayShow, setFieldArrayShow] = useState(addFilesOrDatesDefault)
    const [avatorShowArea, setAvatorShowArea] = useState(addFilesOrDatesDefault)
    const [allFiles, setAllFiles] = useState(addFilesOrDatesDefault);
    const [allDates, setAllDates] = useState(addFilesOrDatesDefault);
    const [isLoading, setIsLoading] = useState<boolean>(true)
    const [isSubmitLoading, setIsSubmitLoading] = useState<boolean>(false)
    const [autoCompleteMulti, setAutoCompleteMulti] = useState(addFilesOrDatesDefault)
    const addEditorDefault:{[key:string]:EditorState} = {}
    const [allEditorValues, setAllEditorValues] = useState(addEditorDefault)
    const [allFields, setAllFields] = useState(addEditStructInfo.allFields)
    const [addEditStructInfo2, setAaddEditStructInfo2] = useState(addEditStructInfo)
    const [uploadFiles, setUploadFiles] = useState<File[] | FileUrl[]>([])
    const [uploadFileFieldName, setUploadFileFieldName] = useState<string>("")
    const [childItemCounter, setChildItemCounter] = useState<number>(1)
    const [deleteChildTableItemArray, setDeleteChildTableItemArray] = useState<number[]>([])
    const [jumpWindowIsShow, setJumpWindowIsShow] = useState(addFilesOrDatesDefault)

    const [activeTab, setActiveTab] = useState<string>('detailsTab')

    const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!

    ///console.log("AddtionalParams======================================",action)

    useEffect(() => {
        if (action.indexOf("edit_default") != -1 && editViewCounter > 0) {
            
            //setIsLoading(true)
            const params = { action, id, editViewCounter, IsGetStructureFromEditDefault, externalId }
            
            //for (const Item in AddtionalParams) {
            //    params[Item] = AddtionalParams[Item]
            //}
            axios
                .get(authConfig.backEndApiHost + backEndApi, { headers: { Authorization: storedToken+"::::"+CSRF_TOKEN }, params: params })
                .then(res => {
                    if (res.data.status == "OK") {
                        setDefaultValuesNew(res.data.data)
                        
                        //console.log("defaultValuesNew--------------------------------", defaultValuesNew)
                        //Show the field when the value is match the condition
                        //This field will control other fields show or not
                        if (res.data.EnableFields) {
                            const fieldArrayShowTemp:{[key:string]:boolean} = {}
                            for (const fieldItem of res.data.EnableFields) {
                                fieldArrayShowTemp[fieldItem] = true
                            }
                            setFieldArrayShow(fieldArrayShowTemp)
                        }
                        if (res.data.data)              {
                            const allEditorValuesTemp = { ...allEditorValues }
                            const autoCompleteMultiTemp:{[key:string]:any} = {}
                            const allFieldsMode = addEditStructInfo2.allFieldsMode;
                            const allFieldsTemp:{[key:string]:any} = JSON.parse(JSON.stringify(addEditStructInfo2.allFields))
                            allFieldsMode && allFieldsMode.map((allFieldsModeItem: any) => {
                                allFields && allFields[allFieldsModeItem.value] && allFields[allFieldsModeItem.value].map((FieldArray: any, FieldArray_index: number) => {
                                    if (FieldArray.type == "autocompletemulti") {
                                        autoCompleteMultiTemp[FieldArray.name] = res.data.data[FieldArray.name]
                                    }
                                    if (FieldArray.type == "editor" && res.data.data[FieldArray.name]) {
                                        allEditorValuesTemp[FieldArray.name] = EditorState.createWithContent(ContentState.createFromBlockArray(convertFromHTML(res.data.data[FieldArray.name]).contentBlocks, convertFromHTML(res.data.data[FieldArray.name]).entityMap,))
                                    }
                                    if (FieldArray.type == "UserRoleMenuDetail") {
                                        const UserRoleSelect = res.data.data[FieldArray.name].split(',')
                                        setUserRoleMenuDetail(FieldArray.MenuTwoArray)
                                        setMenuTwoCount(FieldArray.MenuTwoCount)
                                        const TempSelectedCheckbox:{[key:string]:any[]} = {}
                                        Object.keys(FieldArray.MenuTwoCount).map((MenuTwoName:string)=>{
                                            TempSelectedCheckbox[MenuTwoName] = []
                                        })
                                        Object.keys(FieldArray.MenuTwoArray).map((MenuOneName: string) => {
                                            const MenuTwoArray = FieldArray.MenuTwoArray[MenuOneName]
                                            Object.keys(MenuTwoArray).map((MenuTwoName: string) => {
                                                MenuTwoArray[MenuTwoName] && MenuTwoArray[MenuTwoName].map((MenuThreeRecord: any) => {
                                                    if(UserRoleSelect.includes(MenuThreeRecord.id)) {
                                                        TempSelectedCheckbox[MenuThreeRecord.MenuOneName].push(MenuThreeRecord.id)
                                                    }
                                                })
                                            })
                                        })
                                        setSelectedCheckbox(TempSelectedCheckbox)
                                        setSelectedMenuOneNameForSubmit(FieldArray.name)                                        
                                    }                                    
                                    if (FieldArray.type == "files" || FieldArray.type == "readonlyfiles" || FieldArray.type == "file" || FieldArray.type == "readonlyfile" || FieldArray.type == "xlsx" || FieldArray.type == "readonlyxlsx") {
                                        setUploadFileFieldName(FieldArray.name)
                                        if(res.data.data[FieldArray.name] && res.data.data[FieldArray.name].length>0) {
                                            setUploadFiles(res.data.data[FieldArray.name])
                                        }
                                    } 
                                    
                                    //处理身份证件类型为非居民身份证时,需要自动修改身份证件号的类型为input                       
                                    if(action!="edit_default_1" && action!="edit_default_2" && FieldArray.name.includes("身份证件类型") && res.data.data[FieldArray.name]!="居民身份证") {
                                        allFieldsTemp[allFieldsModeItem.value][FieldArray_index+1]['type'] = "input"
                                        allFieldsTemp[allFieldsModeItem.value][FieldArray_index+1]['rules']['format'] = ""
                                    }
                                    
                                })
                            })
                            setAllFields(allFieldsTemp)
                            setAllEditorValues(allEditorValuesTemp)
                            setAutoCompleteMulti(autoCompleteMultiTemp)
                            if(res.data.forceuse) {
                                setAaddEditStructInfo2(res.data.edit_default)
                                setAllFields(res.data.edit_default.allFields)
                            }
                            console.log(res.data.childtable, res.data.childtable.ChildItemCounter)
                            if(res.data.childtable && res.data.childtable.ChildItemCounter) {
                                setChildItemCounter(res.data.childtable.ChildItemCounter)
                                
                                //setChildItemRecords(res.data.childtable.data)
                            }
                        }
                        
                        //end for condition
                    }
                    setIsLoading(false)
                })
                .catch(() => {
                    setIsLoading(false)
                    console.log("axios.get editUrl return ******************************************")
                })
        }
        else if (action == "add_default" || action == "import_default") {
            setDefaultValuesNew(addEditStructInfo2.defaultValues)
            setIsLoading(false)
            const allFieldsMode = addEditStructInfo2.allFieldsMode;
            allFieldsMode && allFieldsMode.map((allFieldsModeItem: any) => {
                allFields && allFields[allFieldsModeItem.value] && allFields[allFieldsModeItem.value].map((FieldArray: any) => {
                    if (FieldArray.type == "UserRoleMenuDetail") {
                        setUserRoleMenuDetail(FieldArray.MenuTwoArray)
                        setMenuTwoCount(FieldArray.MenuTwoCount)
                        const TempSelectedCheckbox:{[key:string]:any[]} = {}
                        Object.keys(FieldArray.MenuTwoCount).map((MenuTwoName:string)=>{
                            TempSelectedCheckbox[MenuTwoName] = []
                        })
                        setSelectedCheckbox(TempSelectedCheckbox)
                        setSelectedMenuOneNameForSubmit(FieldArray.name)
                    }
                    if (FieldArray.type == "files") {
                        setUploadFileFieldName(FieldArray.name)
                    }
                    if (FieldArray.type == "file") {
                        setUploadFileFieldName(FieldArray.name)
                    }
                    if (FieldArray.type == "xlsx") {
                        setUploadFileFieldName(FieldArray.name)
                    }
                })
            })
        }
    }, [id, editViewCounter, IsGetStructureFromEditDefault]) //Need refresh data every time.

    const allFieldsMode = addEditStructInfo2.allFieldsMode;
    const titletext: string = addEditStructInfo2.titletext;
    const defaultValues:{ [key:string]:any } = addEditStructInfo2.defaultValues;
    const componentsize = addEditStructInfo2.componentsize;

    //console.log("addEditStructInfo2",addEditStructInfo2)
    //console.log("allFieldsMode",allFieldsMode)
    //console.log("defaultValues",defaultValues)
    //console.log("defaultValuesNew",defaultValuesNew)
    //console.log("allFields",allFields)
    
    const chinaIdCardCheck = (value:string|undefined) => {
        if(value==undefined)  {

            return false;
        }
        const reg = /(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
        if (!reg.test(value)) {
            
            return false;
        }
        const wi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        const ai = value.substring(0, 17);
        let sum = 0;
        for (let i = 0; i < 17; i++) {
            sum += parseInt(ai.charAt(i)) * wi[i];
        }
        const y = sum % 11;
        const valCode = ["1", "0", "X", "9", "8", "7", "6", "5", "4", "3", "2"][y];
        if (valCode != value.charAt(17).toUpperCase()) {
            
            return false;
        }
        
        return true;
    }
    
    //Yup check
    const yupCheckMap:{[key:string]:any} = {}
    {
        allFieldsMode && allFieldsMode.map((allFieldsModeItem: any) => {
            allFields && allFields[allFieldsModeItem.value] && allFields[allFieldsModeItem.value].map((FieldArray: any) => {
                if (FieldArray.type == "input" && FieldArray.rules) {
                    let yupCheck = yup.string().trim().label(FieldArray.label)
                    FieldArray.rules.required ? yupCheck = yupCheck.required() : '';
                    FieldArray.rules.min > 0 ? yupCheck = yupCheck.min(FieldArray.rules.min) : '';
                    FieldArray.rules.max > 0 ? yupCheck = yupCheck.max(FieldArray.rules.max) : '';
                    FieldArray.rules.format == 'onlylowerletter' ? yupCheck = yupCheck.matches(/^[a-z_-]+$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'onlyupperletter' ? yupCheck = yupCheck.matches(/^[A-Z_-]+$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'onlyletterandnumber' ? yupCheck = yupCheck.matches(/^[a-zA-Z0-9_-]+$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'onlynumber' ? yupCheck = yupCheck.matches(/^[0-9]+$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'passwordstrong' ? yupCheck = yupCheck.matches(/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'passwordmiddle' ? yupCheck = yupCheck.matches(/^(?![a-zA-z]+$)(?!\d+$)(?![!@#$%^&*]+$)[a-zA-Z\d!@#$%^&*]+$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'passwordweak' ? yupCheck = yupCheck.matches(/^(?:\d+|[a-zA-Z]+|[!@#$%^&*]+)$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'chinaidcard' ? yupCheck = yupCheck.test('custom-test', FieldArray.rules.invalidtext, chinaIdCardCheck) : '';
                    FieldArray.rules.format == 'bankcard' ? yupCheck = yupCheck.matches(/^([1-9]{1})(\d{15}|\d{18})$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'chinamobile' ? yupCheck = yupCheck.matches(/^((\+|00)86)?1[3-9]\d{9}$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'chinapassport' ? yupCheck = yupCheck.matches(/(^[EeKkGgDdSsPpHh]\d{8}$)|(^(([Ee][a-fA-F])|([DdSsPp][Ee])|([Kk][Jj])|([Mm][Aa])|(1[45]))\d{7}$)/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'url' ? yupCheck = yupCheck.matches(/^((https?|http):\/\/)?([\da-z.-]+)\.([a-z.]{2,6})(\/\w\.-]*)*\/?/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'chinese' ? yupCheck = yupCheck.matches(/^(?:[\u3400-\u4DB5\u4E00-\u9FEA\uFA0E\uFA0F\uFA11\uFA13\uFA14\uFA1F\uFA21\uFA23\uFA24\uFA27-\uFA29]|[\uD840-\uD868\uD86A-\uD86C\uD86F-\uD872\uD874-\uD879][\uDC00-\uDFFF]|\uD869[\uDC00-\uDED6\uDF00-\uDFFF]|\uD86D[\uDC00-\uDF34\uDF40-\uDFFF]|\uD86E[\uDC00-\uDC1D\uDC20-\uDFFF]|\uD873[\uDC00-\uDEA1\uDEB0-\uDFFF]|\uD87A[\uDC00-\uDFE0])+$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'username' ? yupCheck = yupCheck.matches(/^[a-zA-Z0-9_-]{4,16}$/, FieldArray.rules.invalidtext) : '';
                    FieldArray.rules.format == 'chinatelphone' ? yupCheck = yupCheck.matches(/\d{3}-\d{8}|\d{4}-\d{7}/, FieldArray.rules.invalidtext) : '';
                    yupCheckMap[FieldArray.name] = yupCheck
                }
                else if (FieldArray.type == "email" && FieldArray.rules && FieldArray.rules.required) {
                    yupCheckMap[FieldArray.name] = yup.string().email().required().label(FieldArray.label)
                }
                else if ( (FieldArray.type == "textarea" || FieldArray.type == "autocomplete" || FieldArray.type == "tablefilter" || FieldArray.type == "tablefiltercolor" || FieldArray.type == "radiogroup") && FieldArray.rules && FieldArray.rules.required) {
                    yupCheckMap[FieldArray.name] = yup.string().required().label(FieldArray.label).nullable()
                }
                else if ((FieldArray.type == "date" || FieldArray.type == "date1" || FieldArray.type == "date2" || FieldArray.type == "datetime" || FieldArray.type == "month" || FieldArray.type == "year" || FieldArray.type == "monthrange" || FieldArray.type == "yearrange" || FieldArray.type == "quarter") && FieldArray.rules && FieldArray.rules.required) {
                    let yupCheck = yup.string().trim().label(FieldArray.label)
                    FieldArray.rules.required ? yupCheck = yupCheck.required() : '';                    
                    yupCheckMap[FieldArray.name] = yupCheck
                }
                else if (FieldArray.type == "avatar" && FieldArray.rules && FieldArray.rules.required)  {
                    //yupCheckMap[FieldArray.name] = yup.string().required().label(FieldArray.label)
                }
                else if (FieldArray.type == "file" && FieldArray.rules && FieldArray.rules.required)    {
                    //yupCheckMap[FieldArray.name] = yup.string().required().label(FieldArray.label)
                }
                else if (FieldArray.type == "xlsx" && FieldArray.rules && FieldArray.rules.required)    {
                    //yupCheckMap[FieldArray.name] = yup.array().of(yup.string().required('Array elements cannot be empty')).label(FieldArray.label)
                }
                else if (FieldArray.type == "password" && FieldArray.rules && FieldArray.rules.required)  {
                    yupCheckMap[FieldArray.name] = yup.string().required().label(FieldArray.label)
                }
                else if (FieldArray.type == "comfirmpassword" && FieldArray.rules && FieldArray.rules.required)  {
                    yupCheckMap[FieldArray.name] = yup.string().required().min(6).matches(/^(?:\d+|[a-zA-Z]+|[!@#$%^&*]+)$/, FieldArray.rules.invalidtext).label(FieldArray.label)
                }
            })
        })
    }

    const yupCheckMapSchema = yup.object().shape(yupCheckMap)

    // ** Hooks
    const {
        reset,
        control,
        setValue,
        handleSubmit,
        formState: { errors }
    } = useForm({
        defaultValues,
        mode: 'onChange',
        resolver: yupResolver(yupCheckMapSchema)
    })

    const onSubmit = (data: {[key:string]:any}) => {
        const toastId = toast.loading(addEditStructInfo2.submitloading)
        setIsSubmitLoading(true)
        handleIsLoadingTipChange(true, addEditStructInfo2.ImportLoading)
        const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!
        if (!storedToken) {
            toggleAddTableDrawer()
            reset()
            
            return
        }
        
        //upload file 
        const formData = new FormData();

        const dataMap = new Map(Object.entries(data));
        for (const [key, value] of dataMap.entries()) {
            formData.append(key, value);
        }
        const allFilesMap = new Map(Object.entries(allFiles));
        for (const [key, value] of allFilesMap.entries()) {
            formData.append(key, value);
        }
        const allDatesMap = new Map(Object.entries(allDates));
        for (const [key, value] of allDatesMap.entries()) {
            formData.append(key, value);
        }
        const autoCompleteMultiMap = new Map(Object.entries(autoCompleteMulti));
        for (const [key, value] of autoCompleteMultiMap.entries()) {
            formData.append(key, value);
        }
        const allEditorValuesMap = new Map(Object.entries(allEditorValues));
        for (const [key, value] of allEditorValuesMap.entries()) {
            formData.append(key, convertToHTML(value.getCurrentContent()));
        }
        for (const Item in AddtionalParams) {
            formData.append(Item, AddtionalParams[Item]);
        }
        if(selectedMenuOneNameForSubmit!=undefined && selectedMenuOneNameForSubmit!="") {
            const selectedCheckboxResult:string[] = []
            Object.keys(selectedCheckbox).map((selectedCheckboxOne:string)=>{
                selectedCheckbox[selectedCheckboxOne].map((selectedCheckboxElement:string)=>{
                    selectedCheckboxResult.push(selectedCheckboxElement)
                })
            })
            formData.append(selectedMenuOneNameForSubmit, selectedCheckboxResult.join(','));
            console.log("formData",formData)
        }
        if(uploadFileFieldName!=undefined && uploadFileFieldName!="" && uploadFiles!=undefined && uploadFiles.length>0) {
            uploadFiles.forEach((file: File | FileUrl) =>     {
                if(file && (file.type=="image" || file.type=="file") )  {
                    //Exist Files
                    formData.append(`${uploadFileFieldName}_OriginalFieldValue[]`, file.name);
                }
                else {
                    //New Files
                    formData.append(`${uploadFileFieldName}[]`, file);
                }
            });
        }
        formData.append('ChildItemCounter', String(childItemCounter));
        formData.append('deleteChildTableItemArray', deleteChildTableItemArray.join(','));

        

        const postUrl = authConfig.backEndApiHost + backEndApi + "?action=" + action + "_data&id=" + id + "&externalId=" + externalId
        fetch(
            postUrl,
            {
                headers: { Authorization: storedToken+"::::"+CSRF_TOKEN },
                method: 'POST',
                body: formData,
            }
        )
            .then((response) => response.json())
            .then((result) => {
                console.log('Success:', result);
                if (result && result.status == "OK") {
                    toast.success(result.msg)
                }
                else {
                    toast.error(result.msg)
                }
                toast.dismiss(toastId);
                addUserHandleFilter();
                setIsSubmitLoading(false)
                handleIsLoadingTipChange(false, addEditStructInfo2.ImportLoading)
            })
            .catch((error) => {
                console.error('Error:', error);
                setIsSubmitLoading(false)
                handleIsLoadingTipChange(false, addEditStructInfo2.ImportLoading)
            });
            

        // clear avatar and files
        setAvatorShowArea({})
        setAllFiles({})
        setAllDates({})

        //setDefaultValuesNew({})
        toggleAddTableDrawer()
        reset()

    }

    useEffect(() => {        
        Mousetrap.bind(['alt+s', 'command+s', 'command+enter'], () => {handleSubmit(onSubmit)();});
        Mousetrap.bind(['alt+c', 'command+c'], handleClose);        
        
        return () => {
            Mousetrap.unbind(['alt+s', 'command+s', 'command+enter']);
            Mousetrap.unbind(['alt+c', 'command+c']);
        }
    });

    const handleClose = () => {
        setAvatorShowArea({})
        setAllFiles({})
        setAllDates({})
        
        //setDefaultValuesNew({})
        toggleAddTableDrawer()
        reset()
    }

    const handleDialogWindowClose = () => {
        setJumpWindowIsShow({})
        setActiveTab('detailsTab')
    }

    const handleDialogWindowCloseWithParam = (field: string, value: string, fieldCode: string, valueCode: string) => {
        const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
        defaultValuesNewTemp[field] = value
        defaultValuesNewTemp[fieldCode] = valueCode
        setDefaultValuesNew(defaultValuesNewTemp)
        setJumpWindowIsShow({})
        setActiveTab('detailsTab')
        console.log("defaultValuesNewTemp", defaultValuesNewTemp)
    }

    const handleAvatorChange = (e: ChangeEvent) => {
        const reader = new FileReader()
        const { files } = e.target as HTMLInputElement
        if (files && files.length !== 0) {
            reader.onloadend = () => {
                const avatorShowAreaTemp:{[key:string]:any} = { ...avatorShowArea }
                avatorShowAreaTemp[(e.target as HTMLInputElement).name] = reader.result as string
                setAvatorShowArea(avatorShowAreaTemp)
                const allFilesTemp:{[key:string]:any} = { ...allFiles }
                allFilesTemp[(e.target as HTMLInputElement).name] = files[0]
                setAllFiles(allFilesTemp)
            };
            reader.readAsDataURL(files[0]);
        }
    }

    const handleAvatorReset: MouseEventHandler<HTMLButtonElement> = (e) => {
        const avatorShowAreaTemp:{[key:string]:any} = { ...avatorShowArea }
        avatorShowAreaTemp[(e.target as HTMLInputElement).name] = '/images/avatars/1.png'
        setAvatorShowArea(avatorShowAreaTemp)
        const allFilesTemp:{[key:string]:any} = { ...allFiles }
        allFilesTemp[(e.target as HTMLInputElement).name] = undefined
        setAllFiles(allFilesTemp)
        const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
        defaultValuesNewTemp[(e.target as HTMLInputElement).name] = ""
        setDefaultValuesNew(defaultValuesNewTemp)
    }

    const ImgStyled = styled('img')(({ theme }) => ({
        width: 110,
        height: 110,
        borderRadius: 4,
        marginRight: theme.spacing(5)
    }))

    const CustomLink = styled(Link)({
        textDecoration: "none",
        color: "inherit",
    })

    const ButtonStyled = styled(Button)<ButtonProps & { component?: ElementType; htmlFor?: string }>(({ theme }) => ({
        [theme.breakpoints.down('sm')]: {
            width: '100%',
            textAlign: 'center'
        }
    }))

    const ResetButtonStyled = styled(Button)<ButtonProps>(({ theme }) => ({
        marginLeft: theme.spacing(4),
        [theme.breakpoints.down('sm')]: {
            width: '100%',
            marginLeft: 0,
            textAlign: 'center',
            marginTop: theme.spacing(4)
        }
    }))

    interface State {
        password: string
        password2: string
        showPassword: boolean
        showPassword2: boolean
    }
    const [state, setState] = useState<State>({
        password: '',
        password2: '',
        showPassword: false,
        showPassword2: false
    })
    
    // Handle Password
    const handleClickShowPassword = () => {
        setState({ ...state, showPassword: !state.showPassword })
    }
    const handleMouseDownPassword = (event: MouseEvent<HTMLButtonElement>) => {
        event.preventDefault()
    }
    
    // Handle Confirm Password
    const handleClickShowConfirmPassword = () => {
        setState({ ...state, showPassword2: !state.showPassword2 })
    }
    const handleMouseDownConfirmPassword = (event: MouseEvent<HTMLButtonElement>) => {
        event.preventDefault()
    }
    
    
    const RoleMenuElementPermission = (id: string, MenuOneName: string) => {
        const arr:{[key:string]:any[]} = selectedCheckbox
        if (selectedCheckbox[MenuOneName] && selectedCheckbox[MenuOneName].includes(id)) {
          arr[MenuOneName].splice(arr[MenuOneName].indexOf(id), 1)
          setSelectedCheckbox({...arr})
        } 
        else {
            if(arr[MenuOneName]==undefined) {
                arr[MenuOneName] = []
            }
            arr[MenuOneName].push(id)
            setSelectedCheckbox({...arr})
        }
    }
    const [userRoleMenuDetail, setUserRoleMenuDetail] = useState<{[key:string]:any}>({})
    const [menuTwoCount, setMenuTwoCount] = useState<{[key:string]:any}>({})
    const [selectedCheckbox, setSelectedCheckbox] = useState<{[key:string]:any[]}>({})
    const [selectedMenuOneNameForSubmit, setSelectedMenuOneNameForSubmit] = useState<string>("")
    const [isIndeterminateCheckbox, setIsIndeterminateCheckbox] = useState<{[key:string]:boolean}>({})
    const handleSelectAllCheckbox = (e:any) => {
        const chooseMenuOneName = e.target.value
        if (isIndeterminateCheckbox[chooseMenuOneName]) {
            const TempSelectedCheckbox:{[key:string]:any} = { ...selectedCheckbox }
            TempSelectedCheckbox[chooseMenuOneName] = []
            setSelectedCheckbox(TempSelectedCheckbox)
        } 
        else {
            const SectionMenuArray = userRoleMenuDetail[chooseMenuOneName]
            Object.keys(SectionMenuArray).map((SectionMenuKey: any) => {
                SectionMenuArray[SectionMenuKey].map((SectionThreeKey: any) => {
                    RoleMenuElementPermission(SectionThreeKey['id'], chooseMenuOneName)
                })
            })
        }
    }
    useEffect(() => {
        const isIndeterminateCheckboxTemp = {...isIndeterminateCheckbox}
        Object.keys(selectedCheckbox).map((selectedCheckboxKey: any) => {
            const selectedCheckboxSubArray = selectedCheckbox[selectedCheckboxKey]
            if (selectedCheckboxSubArray && selectedCheckboxSubArray.length > 0 && selectedCheckboxSubArray.length) {
                isIndeterminateCheckboxTemp[selectedCheckboxKey] = true
            } else {
                isIndeterminateCheckboxTemp[selectedCheckboxKey] = false
            }
        })        
        setIsIndeterminateCheckbox(isIndeterminateCheckboxTemp)
    }, [selectedCheckbox])
      
    const formatDateItem = (value:number) => {
        let str = '' + value;
        while (str.length < 2) {
            str = '0' + str;
        }
        
        return str;
    }

    const { i18n } = useTranslation()
    registerLocale(i18n.language, langObj[i18n.language])

    interface FileUrl extends File {
        url: string;
    }

    const { getRootProps: getRootPropsFiles, getInputProps: getInputPropsFiles } = useDropzone({
        onDrop: (acceptedFiles: File[]) => {
            const filtered = uploadFiles
            acceptedFiles.map((file: File) => filtered.push(Object.assign(file)))
            setUploadFiles([...filtered])
        }
    })

    const { getRootProps: getRootPropsFile, getInputProps: getInputPropsFile } = useDropzone({
        maxFiles: 1,
        onDrop: (acceptedFiles: File[]) => {
            const filtered = uploadFiles
            acceptedFiles.map((file: File) => filtered.push(Object.assign(file)))
            setUploadFiles([...filtered])
        }
    })

    const { getRootProps: getRootPropsXlsx, getInputProps: getInputPropsXlsx } = useDropzone({
        maxFiles: 1,
        maxSize: 20000000,
        accept: {
        'application/vnd.ms-excel': ['.xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx']
        },
        onDrop: (acceptedFiles: File[]) => {
            const filtered = uploadFiles
            acceptedFiles.map((file: File) => filtered.push(Object.assign(file)))
            setUploadFiles([...filtered])
        },
        onDropRejected: () => {
            toast.error('You can only upload 1 Excel file & maximum size of 20 MB.', {
            duration: 2000
            })
        }
    })
    const renderFilePreview = (file: File | FileUrl) => {
        if (file && 'webkitRelativePath' in file && file['webkitRelativePath']!="" && file['type']=="image") {
            return <img width={38} height={38} alt={file.name} style={{padding: "3px 3px 0 0"}} src={authConfig.backEndApiHost+file['webkitRelativePath']} />
        }
        else if (file && 'webkitRelativePath' in file && file['webkitRelativePath']!="" && file['type']!="image") {
            return <Icon icon='mdi:file-document-outline' />
        }
        else if (file.type.startsWith('image')) {
            return <img width={38} height={38} alt={file.name} style={{padding: "3px 3px 0 0"}} src={URL.createObjectURL(file as any)} />
        } 
        else {
            return <Icon icon='mdi:file-document-outline' />
        }
    }
    const handleRemoveFile = (file: File) => {
        const filtered = uploadFiles.filter((i: File) => i.name !== file.name)
        setUploadFiles([...filtered])
    }
    const handleRemoveAllFiles = () => {
        setUploadFiles([])
    }
    const deleteChildTableItem = (e: SyntheticEvent, i: number) => {
        e.preventDefault()
        setDeleteChildTableItemArray([...deleteChildTableItemArray, i])

        // @ts-ignore
        e.target.closest('.repeater-wrapper').remove()
    }

    return (
        <Fragment>
            {titletext && titletext != "" && !isLoading ?
                <Box sx={{ mb: 8, textAlign: 'center' }}>
                    <Typography variant='h5' sx={{ mb: 3 }}>
                        {titletext}
                    </Typography>
                    <Typography variant='body2'>{addEditStructInfo2.titlememo ? addEditStructInfo2.titlememo : ''}</Typography>
                </Box>
                : ''
            }
            <Grid container spacing={6}>
                {isLoading ? (
                    <Grid item xs={12} sm={12} container justifyContent="space-around">
                        <Box sx={{ mt: 6, mb: 6, display: 'flex', alignItems: 'center', flexDirection: 'column' }}>
                            <CircularProgress />
                            <Typography>{addEditStructInfo2.loading}</Typography>
                        </Box>
                    </Grid>
                ) : (
                    <form onSubmit={handleSubmit(onSubmit)}>
                        {allFieldsMode && allFieldsMode.map((allFieldsModeItem: any, allFieldsModeIndex: number) => {
                            
                            return (
                                <Card key={"AllFieldsMode_" + allFieldsModeIndex} sx={{ mb: 2 }}>
                                    {allFieldsModeItem.label ? <CardHeader title={allFieldsModeItem.label} /> : ''}
                                    <CardContent>
                                        <Grid container spacing={5}>
                                            {allFields && allFields[allFieldsModeItem.value] && allFields[allFieldsModeItem.value].map((FieldArray: any, FieldArray_index: number) => {
                                                
                                                //开始根据表单中每个字段的类型,进行不同的渲染,此部分比较复杂,注意代码改动.
                                                //Start to render differently according to the type of each field in the form
                                                //this part is more complicated, pay attention to the code changes.
                                                //console.log("defaultValuesNew[FieldArray.name]-----", FieldArray)
                                                
                                                if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && (FieldArray.type == "hidden") ) {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && (FieldArray.type == "input" || FieldArray.type == "email" || FieldArray.type == "number")) {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <TextField
                                                                            size={componentsize}
                                                                            disabled={FieldArray.rules.disabled}
                                                                            value={value}
                                                                            label={FieldArray.label}
                                                                            type={FieldArray.type}
                                                                            InputProps={FieldArray.inputProps ? FieldArray.inputProps : {}}
                                                                            onChange={(e) => {
                                                                                onChange(e);
                                                                                const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                if(FieldArray.inputProps && FieldArray.inputProps.step && FieldArray.inputProps.step=='0.01' && String(e.target.value).split('.')[1] && String(e.target.value).split('.')[1].length>2)  {
                                                                                    defaultValuesNewTemp[FieldArray.name] = parseFloat(e.target.value).toFixed(2)
                                                                                    console.log("FieldArray.inputProps", defaultValuesNewTemp)
                                                                                }
                                                                                else {
                                                                                    defaultValuesNewTemp[FieldArray.name] = e.target.value
                                                                                }
                                                                                if(FieldArray.rules.format == 'chinaidcard' && chinaIdCardCheck(e.target.value))     {
                                                                                    if( FieldArray.rules.出生日期 )   {        
                                                                                        defaultValuesNewTemp[FieldArray.rules.出生日期] = defaultValuesNew[FieldArray.name].substr(6, 4)+"-"+defaultValuesNew[FieldArray.name].substr(10, 2)+"-"+defaultValuesNew[FieldArray.name].substr(12, 2)
                                                                                        const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                        allDatesTemp[FieldArray.rules.出生日期] = defaultValuesNewTemp[FieldArray.rules.出生日期]
                                                                                        setAllDates(allDatesTemp)
                                                                                    }
                                                                                    if( FieldArray.rules.出生年月 )   {                                                                                     
                                                                                        defaultValuesNewTemp[FieldArray.rules.出生年月] = defaultValuesNew[FieldArray.name].substr(6, 4)+"-"+defaultValuesNew[FieldArray.name].substr(10, 2)
                                                                                        const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                        allDatesTemp[FieldArray.rules.出生年月] = defaultValuesNewTemp[FieldArray.rules.出生年月]
                                                                                        setAllDates(allDatesTemp)
                                                                                    }
                                                                                    if( FieldArray.rules.性别 )   {    
                                                                                        if(parseInt(defaultValuesNew[FieldArray.name].substr(16,1))%2==1)  {
                                                                                            defaultValuesNewTemp[FieldArray.rules.性别] = "男"
                                                                                        }
                                                                                        else {
                                                                                            defaultValuesNewTemp[FieldArray.rules.性别] = "女"
                                                                                        }                                                                                 
                                                                                    }
                                                                                    if( FieldArray.rules.年龄 )   {                                                
                                                                                        const currentDate = new Date();
                                                                                        const currentYear = currentDate.getFullYear();
                                                                                        console.log(currentYear, Number(defaultValuesNew[FieldArray.name].substr(6, 4)))
                                                                                        defaultValuesNewTemp[FieldArray.rules.年龄] = currentYear - Number(defaultValuesNew[FieldArray.name].substr(6, 4))
                                                                                        const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                        allDatesTemp[FieldArray.rules.年龄] = defaultValuesNewTemp[FieldArray.rules.年龄]
                                                                                        setAllDates(allDatesTemp)
                                                                                    }
                                                                                }
                                                                                setDefaultValuesNew(defaultValuesNewTemp)
                                                                            }}
                                                                            placeholder={FieldArray.placeholder}
                                                                            error={Boolean(errors[FieldArray.name])}
                                                                        />
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "buttonrouter") {
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                            <Button variant='contained' onClick={() => router.push(defaultValuesNew[FieldArray.name])} >
                                                                {FieldArray.label}
                                                            </Button>
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "buttonurl") {
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <CustomLink href={authConfig.backEndApiHost+defaultValuesNew[FieldArray.name]} download={FieldArray.label}>
                                                                    <Button variant='contained'>
                                                                        {FieldArray.label}
                                                                    </Button>
                                                                </CustomLink>
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "readonly") {
                                                    
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <TextField
                                                                            size={componentsize}
                                                                            disabled={FieldArray.rules.disabled}
                                                                            value={value}
                                                                            label={FieldArray.label}
                                                                            type={FieldArray.type}
                                                                            onChange={(e) => {
                                                                                onChange(e);
                                                                                const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                defaultValuesNewTemp[FieldArray.name] = e.target.value
                                                                                setDefaultValuesNew(defaultValuesNewTemp)
                                                                            }}
                                                                            placeholder={FieldArray.placeholder}
                                                                            error={Boolean(errors[FieldArray.name])}
                                                                        />
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "password") {
                                                                                                      
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <InputLabel htmlFor='input-confirm-new-password' error={Boolean(errors[FieldArray.name])}>
                                                                {FieldArray.label}
                                                                </InputLabel>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <OutlinedInput
                                                                            size={componentsize}
                                                                            value={value}
                                                                            label={FieldArray.label}
                                                                            onChange={(e) => {
                                                                                onChange(e);
                                                                                const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                defaultValuesNewTemp[FieldArray.name] = e.target.value
                                                                                setDefaultValuesNew(defaultValuesNewTemp)
                                                                            }}
                                                                            id='password'
                                                                            error={Boolean(errors[FieldArray.name])}
                                                                            type={state.showPassword ? 'text' : 'password'}
                                                                            endAdornment={
                                                                                <InputAdornment position='end'>
                                                                                    <IconButton
                                                                                        edge='end'
                                                                                        onClick={handleClickShowPassword}
                                                                                        onMouseDown={handleMouseDownPassword}
                                                                                        aria-label='toggle password visibility'
                                                                                    >
                                                                                        <Icon icon={state.showPassword ? 'mdi:eye-outline' : 'mdi:eye-off-outline'} />
                                                                                    </IconButton>
                                                                                </InputAdornment>
                                                                            }
                                                                        />
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "comfirmpassword") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <InputLabel htmlFor='input-confirm-new-password' error={Boolean(errors[FieldArray.name])}>
                                                                {FieldArray.label}
                                                                </InputLabel>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <OutlinedInput
                                                                            size={componentsize}
                                                                            value={value}
                                                                            label={FieldArray.label}
                                                                            onChange={(e) => {
                                                                                onChange(e);
                                                                                const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                defaultValuesNewTemp[FieldArray.name] = e.target.value
                                                                                setDefaultValuesNew(defaultValuesNewTemp)
                                                                            }}
                                                                            id='confirm-password'
                                                                            error={Boolean(errors[FieldArray.name])}
                                                                            type={state.showPassword2 ? 'text' : 'password'}
                                                                            endAdornment={
                                                                                <InputAdornment position='end'>
                                                                                    <IconButton
                                                                                        edge='end'
                                                                                        onClick={handleClickShowConfirmPassword}
                                                                                        onMouseDown={handleMouseDownConfirmPassword}
                                                                                        aria-label='toggle password visibility'
                                                                                    >
                                                                                        <Icon icon={state.showPassword2 ? 'mdi:eye-outline' : 'mdi:eye-off-outline'} />
                                                                                    </IconButton>
                                                                                </InputAdornment>
                                                                            }
                                                                        />
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && (FieldArray.type == "select" || FieldArray.type == "tablefilter" || FieldArray.type == "tablefiltercolor")) {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    //console.log("errors select--------------------------------", errors)
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <InputLabel
                                                                    id='validation-basic-select'
                                                                    error={Boolean(errors[FieldArray.name])}
                                                                    htmlFor='validation-basic-select'
                                                                >
                                                                    {FieldArray.label}
                                                                </InputLabel>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <Select
                                                                            size={componentsize}
                                                                            value={value}
                                                                            label={FieldArray.label}
                                                                            onChange={(e) => {
                                                                                onChange(e);
                                                                                const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                defaultValuesNewTemp[FieldArray.name] = e.target.value
                                                                                setDefaultValuesNew(defaultValuesNewTemp)
                                                                            }}
                                                                            error={Boolean(errors[FieldArray.name])}
                                                                            labelId='validation-basic-select'
                                                                            aria-describedby='validation-basic-select'
                                                                        >
                                                                            {FieldArray.options.map((ItemArray: any, ItemArray_index: number) => {
                                                                                return <MenuItem value={ItemArray.value} key={ItemArray_index}>{ItemArray.label}</MenuItem>
                                                                            })}
                                                                        </Select>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }  
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && (FieldArray.type == "ProvinceAndCity")) {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (defaultValuesNew[FieldArray.行政区划] != undefined) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.行政区划, defaultValuesNew[FieldArray.行政区划])
                                                        setValue(FieldArray.所在省, defaultValuesNew[FieldArray.所在省])
                                                        setValue(FieldArray.所在市, defaultValuesNew[FieldArray.所在市])
                                                        setValue(FieldArray.所在区县, defaultValuesNew[FieldArray.所在区县])                                                        
                                                    }
                                                    
                                                    //console.log("errors select--------------------------------", errors)
                                                    
                                                    return (
                                                        <Fragment key={"AllFields_1_" + FieldArray_index}> 
                                                            <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm}>
                                                                <FormControl fullWidth sx={{ mb: 0 }}>
                                                                    <InputLabel
                                                                        id='validation-basic-select'
                                                                        error={Boolean(errors[FieldArray.所在省])}
                                                                        htmlFor='validation-basic-select'
                                                                    >
                                                                        {FieldArray.所在省}
                                                                    </InputLabel>
                                                                    <Controller
                                                                        name={FieldArray.所在省}
                                                                        control={control}
                                                                        render={({ field: { value, onChange } }) => (
                                                                            <Select
                                                                                size={componentsize}
                                                                                value={value}
                                                                                label={FieldArray.所在省}
                                                                                onChange={(e) => {
                                                                                    onChange(e);
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[FieldArray.所在省] = e.target.value
                                                                                    defaultValuesNewTemp[FieldArray.行政区划] = ""
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                }}
                                                                                error={Boolean(errors[FieldArray.所在省])}
                                                                                labelId='validation-basic-select'
                                                                                aria-describedby='validation-basic-select'
                                                                            >
                                                                                {Object.keys(chinacity).map((ProvinceName: any, ItemArray_index: number) => {
                                                                                    return <MenuItem value={ProvinceName} key={ItemArray_index}>{ProvinceName}</MenuItem>
                                                                                })}
                                                                            </Select>
                                                                        )}
                                                                    />
                                                                </FormControl>
                                                            </Grid>
                                                            <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_2_" + FieldArray_index}>
                                                                <FormControl fullWidth sx={{ mb: 0 }}>
                                                                    <InputLabel
                                                                        id='validation-basic-select'
                                                                        error={Boolean(errors[FieldArray.所在市])}
                                                                        htmlFor='validation-basic-select'
                                                                    >
                                                                        {FieldArray.所在市}
                                                                    </InputLabel>
                                                                    <Controller
                                                                        name={FieldArray.所在市}
                                                                        control={control}
                                                                        render={({ field: { value, onChange } }) => (
                                                                            <Select
                                                                                size={componentsize}
                                                                                value={value}
                                                                                label={FieldArray.label}
                                                                                onChange={(e) => {
                                                                                    onChange(e);
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[FieldArray.所在市] = e.target.value
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                }}
                                                                                error={Boolean(errors[FieldArray.所在市])}
                                                                                labelId='validation-basic-select'
                                                                                aria-describedby='validation-basic-select'
                                                                            >
                                                                                {chinacity[defaultValuesNew[FieldArray.所在省]] && Object.keys(chinacity[defaultValuesNew[FieldArray.所在省]]).map((CityName: any, ItemArray_index: number) => {
                                                                                    return <MenuItem value={CityName} key={ItemArray_index}>{CityName}</MenuItem>
                                                                                })}
                                                                            </Select>
                                                                        )}
                                                                    />
                                                                </FormControl>
                                                            </Grid>                                                            
                                                            <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_3_" + FieldArray_index}>
                                                                <FormControl fullWidth sx={{ mb: 0 }}>
                                                                    <InputLabel
                                                                        id='validation-basic-select'
                                                                        error={Boolean(errors[FieldArray.所在区县])}
                                                                        htmlFor='validation-basic-select'
                                                                    >
                                                                        {FieldArray.所在区县}
                                                                    </InputLabel>
                                                                    <Controller
                                                                        name={FieldArray.所在区县}
                                                                        control={control}
                                                                        render={({ field: { value, onChange } }) => (
                                                                            <Select
                                                                                size={componentsize}
                                                                                value={value}
                                                                                label={FieldArray.label}
                                                                                onChange={(e) => {
                                                                                    onChange(e);
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[FieldArray.所在区县] = e.target.value
                                                                                    {chinacity[defaultValuesNew[FieldArray.所在省]] && chinacity[defaultValuesNew[FieldArray.所在省]][defaultValuesNew[FieldArray.所在市]] && chinacity[defaultValuesNew[FieldArray.所在省]][defaultValuesNew[FieldArray.所在市]].map((DistrictArray: any) => {
                                                                                        if(DistrictArray.DistrictName==e.target.value) {
                                                                                            console.log("-------------------------DistrictArray.DistrictName",DistrictArray.DistrictID)
                                                                                            defaultValuesNewTemp[FieldArray.行政区划] = DistrictArray.DistrictID
                                                                                        }
                                                                                    })}
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    
                                                                                }}
                                                                                error={Boolean(errors[FieldArray.所在区县])}
                                                                                labelId='validation-basic-select'
                                                                                aria-describedby='validation-basic-select'
                                                                            >
                                                                                {chinacity[defaultValuesNew[FieldArray.所在省]] && chinacity[defaultValuesNew[FieldArray.所在省]][defaultValuesNew[FieldArray.所在市]] && chinacity[defaultValuesNew[FieldArray.所在省]][defaultValuesNew[FieldArray.所在市]].map((DistrictArray: any, ItemArray_index: number) => {
                                                                                    return <MenuItem value={DistrictArray.DistrictName} key={ItemArray_index}>{DistrictArray.DistrictName}</MenuItem>
                                                                                })}
                                                                            </Select>
                                                                        )}
                                                                    />
                                                                </FormControl>
                                                            </Grid>                                                            
                                                            <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_4_" + FieldArray_index}>
                                                                <FormControl fullWidth sx={{ mb: 0 }}>
                                                                    <Controller
                                                                        name={FieldArray.行政区划}
                                                                        control={control}
                                                                        render={({ field: { value } }) => (
                                                                            <TextField
                                                                                size={componentsize}
                                                                                disabled={FieldArray.rules.disabled}
                                                                                value={value}
                                                                                label={FieldArray.label}
                                                                                type={FieldArray.type}
                                                                                InputProps={{
                                                                                    readOnly: true,
                                                                                  }}
                                                                                placeholder={FieldArray.placeholder}
                                                                                error={Boolean(errors[FieldArray.行政区划])}
                                                                            />
                                                                        )}
                                                                    />
                                                                </FormControl>
                                                            </Grid>
                                                        </Fragment>
                                                        
                                                    )
                                                } 
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "autocomplete") {
                                                    if(FieldArray.name!=FieldArray.code) {
                                                        if(defaultValuesNew[FieldArray.code]!="" && defaultValuesNew[FieldArray.code]!=undefined && defaultValuesNew[FieldArray.name]==undefined && FieldArray && FieldArray.options && FieldArray.options.length>0 ) {
                                                            FieldArray.options.map((ItemValue: any) => {
                                                                if(ItemValue.value==defaultValuesNew[FieldArray.code]) {
                                                                    setValue(FieldArray.name, ItemValue.label)
                                                                    setValue(FieldArray.code, ItemValue.value)
                                                                }
                                                            })
                                                        }
                                                        if(defaultValuesNew[FieldArray.code]!="" && defaultValuesNew[FieldArray.code]!=undefined && defaultValuesNew[FieldArray.name]!=undefined)  {
                                                            setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                        }
                                                        if(defaultValuesNew[FieldArray.name]==undefined && defaultValuesNew[FieldArray.code]==undefined)  {
                                                            setValue(FieldArray.name, "")
                                                            setValue(FieldArray.code, "")
                                                        }
                                                    }

                                                    if(defaultValuesNew[FieldArray.code]==undefined)  {
                                                        setValue(FieldArray.code, "")
                                                    }
                                                    else {                                                        
                                                        setValue(FieldArray.code, defaultValuesNew[FieldArray.code])
                                                    }
                                                    
                                                    const options = FieldArray.options
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value } }) => (
                                                                        <Autocomplete
                                                                            size={componentsize}
                                                                            value={value}
                                                                            options={options}
                                                                            freeSolo={FieldArray.freeSolo}
                                                                            id="controllable-states-demo"
                                                                            isOptionEqualToValue={(option:any, value) => { return option.value === value; }}
                                                                            renderInput={(params) => <TextField {...params} label={FieldArray.label} />}
                                                                            onChange={(event: any, newValue: any) => {
                                                                                if (newValue != undefined) {
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    if(FieldArray.name!=FieldArray.code) {
                                                                                        defaultValuesNewTemp[FieldArray.name] = newValue.label
                                                                                        defaultValuesNewTemp[FieldArray.code] = newValue.value
                                                                                    }
                                                                                    else    {
                                                                                        defaultValuesNewTemp[FieldArray.code] = newValue.value
                                                                                    }
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    
                                                                                    //This field will control other fields show or not
                                                                                    const fieldArrayShowTemp:{[key:string]:any} = {}
                                                                                    if (FieldArray.EnableFields && FieldArray.EnableFields != undefined && FieldArray.EnableFields[newValue.value] != undefined) {
                                                                                        for (const fieldItem of FieldArray.EnableFields[newValue.value]) {
                                                                                            fieldArrayShowTemp[fieldItem] = true
                                                                                        }
                                                                                    }
                                                                                    if (FieldArray.DisableFields && FieldArray.DisableFields != undefined && FieldArray.DisableFields[newValue.value] != undefined) {
                                                                                        for (const fieldItem of FieldArray.DisableFields[newValue.value]) {
                                                                                            fieldArrayShowTemp[fieldItem] = false
                                                                                        }
                                                                                    }
                                                                                    setFieldArrayShow(fieldArrayShowTemp)

                                                                                    //根据下拉列表中项目的值来指定其它字段的类型
                                                                                    FieldArray.options.map((ItemValue: any) => {
                                                                                        if(ItemValue['ExtraControl']) {
                                                                                            const TempFieldNameAndType = ItemValue['ExtraControl'].split(":")
                                                                                            if(TempFieldNameAndType.length > 1 && TempFieldNameAndType[1] && newValue.value==ItemValue['value']) {
                                                                                                allFields && allFields[allFieldsModeItem.value] && allFields[allFieldsModeItem.value].map((FieldArrayChild: any, FieldArrayChild_index: number) => {
                                                                                                    if(FieldArrayChild.name == TempFieldNameAndType[0]) {
                                                                                                        const allFieldsTemp:{[key:string]:any} = JSON.parse(JSON.stringify(allFields))
                                                                                                        if(TempFieldNameAndType[1]=='chinaidcard') {
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['type'] = "input"
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['format'] = TempFieldNameAndType[1]
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['出生日期'] = FieldArrayChild.name.replace("身份证件号","出生日期")
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['出生年月'] = FieldArrayChild.name.replace("身份证件号","出生年月")
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['性别'] = FieldArrayChild.name.replace("身份证件号","性别")
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['年龄'] = FieldArrayChild.name.replace("身份证件号","年龄")
                                                                                                        }
                                                                                                        else {
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['type'] = "input"
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['format'] = ""
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['出生日期'] = ""
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['出生年月'] = ""
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['性别'] = ""
                                                                                                            allFieldsTemp[allFieldsModeItem.value][FieldArrayChild_index]['rules']['年龄'] = ""
                                                                                                        }
                                                                                                        setAllFields(allFieldsTemp)
                                                                                                    }
                                                                                                })
                                                                                            }
                                                                                        }
                                                                                    })
                                                                                }
                                                                                else {
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[FieldArray.name] = ""
                                                                                    defaultValuesNewTemp[FieldArray.code] = ""
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    setValue(FieldArray.name, "")
                                                                                    setValue(FieldArray.code, "")
                                                                                }
                                                                            }}
                                                                        />
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }    
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "autocompletemdi") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        //setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value } }) => (
                                                                        <Autocomplete
                                                                            size={componentsize}
                                                                            value={value}
                                                                            freeSolo={FieldArray.freeSolo}
                                                                            onChange={(event: any, newValue: any) => {
                                                                                if (newValue != undefined) {
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[FieldArray.name] = newValue.value
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                }
                                                                            }}
                                                                            id="controllable-states-demo"
                                                                            options={mdi}
                                                                            isOptionEqualToValue={(option:any, value) => { return option.label === value; }}
                                                                            renderInput={(params) => <TextField {...params} label={FieldArray.label} />}
                                                                            renderOption={(props, option) => (
                                                                                <Box component='li' sx={{ '& > img': { mr: 4, flexShrink: 0 } }} {...props}>
                                                                                    <Icon icon={`mdi-${option.label}`} />
                                                                                    {option.label}
                                                                                </Box>
                                                                            )}
                                                                        />
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }    
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "autocompletemulti") {
                                                    const DefaultValueForAutoComplete:any[] = []
                                                    const DefaultValueForAutoCompleteMap:{[key:string]:any} = {}
                                                    FieldArray.options.map((ItemValue: any) => {
                                                        DefaultValueForAutoCompleteMap[ItemValue['value']] = ItemValue['label'];
                                                    })
                                                    if(defaultValuesNew[FieldArray.code] != null) {
                                                        const TempArray = defaultValuesNew[FieldArray.code].split(',')
                                                        TempArray.map((ItemValue: any) => {
                                                            if (ItemValue != '' && ItemValue != undefined) {
                                                                DefaultValueForAutoComplete.push({ "value": ItemValue, "label": DefaultValueForAutoCompleteMap[ItemValue] })
                                                            }
                                                        })
                                                    }                                                    
                                                    setValue(FieldArray.name, DefaultValueForAutoComplete)
                                                    if(FieldArray.code!=FieldArray.name)  {
                                                        setValue(FieldArray.code, defaultValuesNew[FieldArray.code])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value } }) => {

                                                                        return (
                                                                            <Fragment>
                                                                                { Array.isArray(value) ? 
                                                                                <Autocomplete
                                                                                    multiple
                                                                                    size={componentsize}                                                                            
                                                                                    value={value}
                                                                                    id="tags-outlined"
                                                                                    options={FieldArray.options}
                                                                                    getOptionLabel={(option:any) => option.label}
                                                                                    filterSelectedOptions
                                                                                    isOptionEqualToValue={(option:any, value) => { return option.value === value.value; }}
                                                                                    onChange={(event: any, newValue: any) => {
                                                                                        if (newValue && newValue.length > 0) {
                                                                                            const newValueArray = []
                                                                                            for (const fieldItem of newValue) {
                                                                                                newValueArray.push(fieldItem.value);
                                                                                            }
                                                                                            const autoCompleteMultiTemp:{[key:string]:any} = { ...autoCompleteMulti }
                                                                                            autoCompleteMultiTemp[FieldArray.name] = newValueArray.join(',');
                                                                                            setAutoCompleteMulti(autoCompleteMultiTemp)
        
                                                                                            const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                            defaultValuesNewTemp[FieldArray.code] = newValueArray.join(',');
                                                                                            setDefaultValuesNew(defaultValuesNewTemp)
        
                                                                                        }
                                                                                        else {
                                                                                            const autoCompleteMultiTemp:{[key:string]:any} = { ...autoCompleteMulti }
                                                                                            autoCompleteMultiTemp[FieldArray.name] = "";
                                                                                            setAutoCompleteMulti(autoCompleteMultiTemp)
        
                                                                                            const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                            defaultValuesNewTemp[FieldArray.code] = "";
                                                                                            setDefaultValuesNew(defaultValuesNewTemp)
                                                                                        }
        
                                                                                    }}
                                                                                    renderInput={(params) => (
                                                                                        <TextField
                                                                                            {...params}
                                                                                            label={FieldArray.label}
                                                                                            placeholder={FieldArray.placeholder}
                                                                                        />
                                                                                    )}
                                                                                />
                                                                                : '' }
                                                                            </Fragment>    
                                                                        )
                                                                    }}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "jumpwindow") {
                                                    const NewFieldName = FieldArray.name
                                                    const NewFieldCode = FieldArray.code
                                                    if(NewFieldName!=NewFieldCode) {
                                                        if(defaultValuesNew[NewFieldCode]!="" && defaultValuesNew[NewFieldCode]!=undefined && defaultValuesNew[NewFieldName]==undefined && FieldArray && FieldArray.options && FieldArray.options.length>0 ) {
                                                            FieldArray.options.map((ItemValue: any) => {
                                                                if(ItemValue.value==defaultValuesNew[NewFieldCode]) {
                                                                    setValue(NewFieldName, ItemValue.label)
                                                                    setValue(NewFieldCode, ItemValue.value)
                                                                }
                                                            })
                                                        }
                                                        if(defaultValuesNew[NewFieldCode]!="" && defaultValuesNew[NewFieldCode]!=undefined && defaultValuesNew[NewFieldName]!=undefined)  {
                                                            setValue(NewFieldName, defaultValuesNew[NewFieldName])
                                                        }
                                                        if(defaultValuesNew[NewFieldName]==undefined && defaultValuesNew[NewFieldCode]==undefined)  {
                                                            setValue(NewFieldName, "")
                                                            setValue(NewFieldCode, "")
                                                        }
                                                    }

                                                    if(defaultValuesNew[NewFieldCode]==undefined)  {
                                                        setValue(NewFieldCode, "")
                                                    }
                                                    else {                                                        
                                                        setValue(NewFieldCode, defaultValuesNew[NewFieldCode])
                                                    }

                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={NewFieldName}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <Fragment>
                                                                            <TextField
                                                                                size={componentsize}  
                                                                                disabled={FieldArray.rules.disabled}
                                                                                value={value}
                                                                                label={FieldArray.label}
                                                                                type={FieldArray.type}
                                                                                InputProps={FieldArray.inputProps ? FieldArray.inputProps : {}}
                                                                                onChange={(e) => {
                                                                                    onChange(e);
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[NewFieldName] = e.target.value
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                }}
                                                                                onSelect={(event: FocusEvent<HTMLInputElement>) => {
                                                                                    event.target.blur();
                                                                                    const jumpWindowIsShowTemp:{[key:string]:any} = { ...jumpWindowIsShow }
                                                                                    jumpWindowIsShowTemp[NewFieldName] = true
                                                                                    setJumpWindowIsShow(jumpWindowIsShowTemp)
                                                                                }}
                                                                                placeholder={FieldArray.placeholder}
                                                                                error={Boolean(errors[NewFieldName])}
                                                                            />
                                                                            <Dialog
                                                                                fullWidth
                                                                                open={jumpWindowIsShow[NewFieldName]==true?true:false}
                                                                                scroll='body'
                                                                                maxWidth='md'
                                                                                onClose={()=>handleDialogWindowClose()}
                                                                                onBackdropClick={()=>handleDialogWindowClose()}
                                                                                TransitionComponent={Transition}
                                                                            >
                                                                                <DialogContent
                                                                                sx={{
                                                                                    pt: { xs: 8, sm: 12.5 },
                                                                                    pr: { xs: 5, sm: 12 },
                                                                                    pb: { xs: 5, sm: 9.5 },
                                                                                    pl: { xs: 4, sm: 11 },
                                                                                    position: 'relative'
                                                                                }}
                                                                                >
                                                                                <IconButton size='small' onClick={()=>handleDialogWindowClose()} sx={{ position: 'absolute', right: '1rem', top: '1rem' }}>
                                                                                    <Icon icon='mdi:close' />
                                                                                </IconButton>
                                                                                <Box sx={{ mb: 8, textAlign: 'center' }}>
                                                                                    <Typography variant='h5' sx={{ mb: 3 }}>{FieldArray.jumpWindowTitle}</Typography>
                                                                                    <Typography variant='body2'>{FieldArray.jumpWindowSubTitle}.</Typography>
                                                                                </Box>
                                                                                <Box sx={{ display: 'flex', flexWrap: { xs: 'wrap', md: 'nowrap' } }}>
                                                                                    <TabContext value={activeTab}>
                                                                                    <TabPanel value='detailsTab' sx={{ flexGrow: 1 }}>
                                                                                        <IndexJumpDialogWindow handleDialogWindowCloseWithParam={handleDialogWindowCloseWithParam} NewFieldName={NewFieldName} NewFieldCode={NewFieldCode} FieldArray={FieldArray} />
                                                                                    </TabPanel>
                                                                                    </TabContext>
                                                                                </Box>
                                                                                </DialogContent>
                                                                            </Dialog>
                                                                        </Fragment>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[NewFieldName] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[NewFieldName]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }     
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && (FieldArray.type == "radiogroup" || FieldArray.type == "radiogroupcolor")) {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                    }
                                                    setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <FormLabel>{FieldArray.label}</FormLabel>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field }) => (
                                                                        <RadioGroup
                                                                            row={FieldArray.rules.row}
                                                                            {...field}
                                                                            aria-label={FieldArray.label}
                                                                            name={FieldArray.name}
                                                                            onChange={(e) => {
                                                                                const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                defaultValuesNewTemp[FieldArray.name] = e.target.value
                                                                                setDefaultValuesNew(defaultValuesNewTemp)
                                                                            }}
                                                                        >
                                                                            {FieldArray.options.map((ItemArray: any, ItemArray_index: number) => {
                                                                                
                                                                            return (
                                                                                    <FormControlLabel
                                                                                        value={ItemArray.value}
                                                                                        label={ItemArray.label}
                                                                                        key={ItemArray_index}
                                                                                        sx={errors[FieldArray.name] ? { color: 'error.main' } : null}
                                                                                        control={<Radio size={componentsize} sx={errors[FieldArray.name] ? { color: 'error.main' } : null} />}
                                                                                    />
                                                                                )
                                                                            })}
                                                                        </RadioGroup>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "checkbox") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if ((action.indexOf("edit_default") != -1 || action.indexOf("import_default") != -1) && defaultValuesNew[FieldArray.name] != undefined) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <FormLabel>{FieldArray.label}</FormLabel>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field }) => (
                                                                        <FormGroup 
                                                                            row={FieldArray.rules.row}
                                                                            {...field} 
                                                                            aria-label={FieldArray.label}>
                                                                            {FieldArray.options.map((ItemArray: any, ItemArray_index: number) => {
                                                                                const TempValueArray = defaultValuesNew[FieldArray.name].split(",")
                                                                                
                                                                                return (
                                                                                    <FormControlLabel
                                                                                        value={ItemArray.value}
                                                                                        label={ItemArray.label}
                                                                                        key={ItemArray_index}
                                                                                        sx={errors[FieldArray.name] ? { color: 'error.main' } : null}
                                                                                        control={
                                                                                            <Checkbox
                                                                                                size={componentsize}
                                                                                                sx={errors[FieldArray.name] ? { color: 'error.main' } : null}
                                                                                                checked={TempValueArray.indexOf(ItemArray.value) == -1 ? false : true}
                                                                                                onChange={(e) => {
                                                                                                    const clickOrNot = e.target.checked
                                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                                    if (clickOrNot) {
                                                                                                        //click
                                                                                                        if (defaultValuesNewTemp[FieldArray.name].indexOf(ItemArray.value) == -1) {
                                                                                                            //Not Exist, will add into
                                                                                                            if (defaultValuesNewTemp[FieldArray.name] == undefined || defaultValuesNewTemp[FieldArray.name] == "undefined" || defaultValuesNewTemp[FieldArray.name] == "") {
                                                                                                                defaultValuesNewTemp[FieldArray.name] = ItemArray.value
                                                                                                            }
                                                                                                            else {
                                                                                                                defaultValuesNewTemp[FieldArray.name] += "," + ItemArray.value
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    else {
                                                                                                        //cancel
                                                                                                        const TempValue = defaultValuesNewTemp[FieldArray.name].split(",")
                                                                                                        if (TempValue && TempValue.indexOf(ItemArray.value) != -1) {
                                                                                                            //Exist, will remove
                                                                                                            TempValue.splice(TempValue.indexOf(ItemArray.value), 1)
                                                                                                            defaultValuesNewTemp[FieldArray.name] = TempValue.join(',')
                                                                                                        }
                                                                                                    }
                                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                                }}
                                                                                            />
                                                                                        }
                                                                                    />
                                                                                )
                                                                            })}
                                                                        </FormGroup>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }} id='validation-basic-select'>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "textarea") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined && defaultValuesNew[FieldArray.name] != "") {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <TextField
                                                                            size={componentsize}
                                                                            value={value}
                                                                            label={FieldArray.label}
                                                                            rows={4}
                                                                            multiline
                                                                            onChange={(e) => {
                                                                                onChange(e);
                                                                                const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                defaultValuesNewTemp[FieldArray.name] = e.target.value
                                                                                setDefaultValuesNew(defaultValuesNewTemp)
                                                                            }}
                                                                            placeholder={FieldArray.placeholder}
                                                                            error={Boolean(errors[FieldArray.name])}
                                                                            aria-describedby='validation-basic-textarea'
                                                                        />
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && (FieldArray.type == "date" || FieldArray.type == "date1" || FieldArray.type == "date2")) {
                                                    
                                                    // Add ' 00:00:00' to avoid the date minus one day in the DatePicker
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined && defaultValuesNew[FieldArray.name] != "" && defaultValuesNew[FieldArray.name] != "0000-00-00" && defaultValuesNew[FieldArray.name] != "1971-01-01" && defaultValuesNew[FieldArray.name].length == 10) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]***************************", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange, onBlur } }) => (
                                                                        <DatePickerWrapper sx={{ zIndex: 'tooltip' }}>
                                                                            <DatePicker 
                                                                                selected={defaultValuesNew[FieldArray.name]!="" && defaultValuesNew[FieldArray.name] != "0000-00-00" && defaultValuesNew[FieldArray.name] != "1971-01-01" && defaultValuesNew[FieldArray.name].length == 10 ? (new Date(defaultValuesNew[FieldArray.name] + ' 00:00:00')) : (value ? new Date(value) : null)  }
                                                                                id={FieldArray.name}
                                                                                showYearDropdown
                                                                                showMonthDropdown
                                                                                locale={i18n.language}
                                                                                dateFormat={FieldArray.dateFormat}
                                                                                popperPlacement='bottom-start'
                                                                                onChange={(date: Date) => {
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    if (date != undefined) {
                                                                                        defaultValuesNewTemp[FieldArray.name] = date.getFullYear() + "-" + formatDateItem(date.getMonth() + 1) + "-" + formatDateItem(date.getDate())
                                                                                    }
                                                                                    else {
                                                                                        defaultValuesNewTemp[FieldArray.name] = "1971-01-01";
                                                                                    }
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                    allDatesTemp[FieldArray.name] = defaultValuesNewTemp[FieldArray.name]
                                                                                    setAllDates(allDatesTemp)
                                                                                    onChange(date);
                                                                                    onBlur();
                                                                                }}
                                                                                todayButton='Today'
                                                                                minDate={FieldArray.StartDate != "" && FieldArray.StartDate != undefined && FieldArray.StartDate != "1971-01-01" ? new Date(FieldArray.StartDate + ' 00:00:00') : new Date("1971-01-01 00:00:00")}
                                                                                maxDate={FieldArray.EndDate != "" && FieldArray.EndDate != undefined && FieldArray.EndDate != "1971-01-01" ? new Date(FieldArray.EndDate + ' 00:00:00') : new Date("2099-12-31 00:00:00")}
                                                                                placeholderText={FieldArray.placeholder}
                                                                                customInput={<TextField fullWidth size={componentsize} label={FieldArray.label || ''} autoComplete='off'/>}
                                                                            />
                                                                        </DatePickerWrapper>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "year") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined && defaultValuesNew[FieldArray.name] != "" && defaultValuesNew[FieldArray.name] != "0000" && defaultValuesNew[FieldArray.name] != "1971" && defaultValuesNew[FieldArray.name].length == 4) {
                                                        
                                                        //console.log("FieldArray***************************", FieldArray)
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    // Add ' 00:00:00' to avoid the date minus one day in the DatePicker
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange, onBlur } }) => (
                                                                        <DatePickerWrapper>
                                                                            <DatePicker
                                                                                selected={defaultValuesNew[FieldArray.name] && defaultValuesNew[FieldArray.name] != "0000" && defaultValuesNew[FieldArray.name] != "1971" && defaultValuesNew[FieldArray.name].length == 4 ? (new Date(defaultValuesNew[FieldArray.name] + '-01-01 00:00:00')) : (value ? new Date(value) : null)   }
                                                                                id={FieldArray.name}
                                                                                showYearPicker
                                                                                locale={i18n.language}
                                                                                dateFormat={FieldArray.dateFormat}
                                                                                popperPlacement='bottom-start'
                                                                                onChange={(date: Date) => {
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    if (date != undefined) {
                                                                                        defaultValuesNewTemp[FieldArray.name] = date.getFullYear()
                                                                                    }
                                                                                    else {
                                                                                        defaultValuesNewTemp[FieldArray.name] = "1971"
                                                                                    }
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                    allDatesTemp[FieldArray.name] = defaultValuesNewTemp[FieldArray.name]
                                                                                    setAllDates(allDatesTemp)
                                                                                    onChange(date);
                                                                                    onBlur();
                                                                                }}
                                                                                minDate={FieldArray.StartYear != "" && FieldArray.StartYear != undefined && FieldArray.StartYear != "1971" ? new Date(FieldArray.StartYear + '-01-01 00:00:00') : new Date("1971-01-01 00:00:00")}
                                                                                maxDate={FieldArray.EndYear != "" && FieldArray.EndYear != undefined && FieldArray.EndYear != "1971" ? new Date(FieldArray.EndYear + '-01-01 00:00:00') : new Date("2099-12-31 00:00:00")}
                                                                                placeholderText={FieldArray.placeholder}
                                                                                customInput={<TextField fullWidth size={componentsize} label={FieldArray.label || ''} />}
                                                                            />
                                                                        </DatePickerWrapper>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "month") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined && defaultValuesNew[FieldArray.name] != "" && defaultValuesNew[FieldArray.name] != "0000-00" && defaultValuesNew[FieldArray.name] != "1971-01" && defaultValuesNew[FieldArray.name].length == 7) {
                                                        
                                                        //console.log("FieldArray***************************", FieldArray)
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    // Add ' 00:00:00' to avoid the date minus one day in the DatePicker
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange, onBlur } }) => (
                                                                        <DatePickerWrapper>
                                                                            <DatePicker
                                                                                selected={defaultValuesNew[FieldArray.name] && defaultValuesNew[FieldArray.name] != "0000-00" && defaultValuesNew[FieldArray.name] != "1971-01" && defaultValuesNew[FieldArray.name].length == 7 ? (new Date(defaultValuesNew[FieldArray.name] + '-01 00:00:00')) : (value ? new Date(value) : null)   }
                                                                                id={FieldArray.name}
                                                                                showMonthYearPicker
                                                                                locale={i18n.language}
                                                                                dateFormat={FieldArray.dateFormat}
                                                                                popperPlacement='bottom-start'
                                                                                onChange={(date: Date) => {
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    if (date != undefined) {
                                                                                        defaultValuesNewTemp[FieldArray.name] = date.getFullYear() + "-" + formatDateItem(date.getMonth() + 1);
                                                                                    }
                                                                                    else {
                                                                                        defaultValuesNewTemp[FieldArray.name] = "1971-01";
                                                                                    }
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                    allDatesTemp[FieldArray.name] = defaultValuesNewTemp[FieldArray.name]
                                                                                    setAllDates(allDatesTemp)
                                                                                    onChange(date);
                                                                                    onBlur();
                                                                                }}
                                                                                minDate={FieldArray.StartMonth != "" && FieldArray.StartMonth != undefined && FieldArray.StartMonth != "1971-01" ? new Date(FieldArray.StartMonth + '-01 00:00:00') : new Date("1971-01-01 00:00:00")}
                                                                                maxDate={FieldArray.EndMonth != "" && FieldArray.EndMonth != undefined && FieldArray.EndMonth != "1971-01" ? new Date(FieldArray.EndMonth + '-01 00:00:00') : new Date("2099-12-31 00:00:00")}
                                                                                placeholderText={FieldArray.placeholder}
                                                                                customInput={<TextField fullWidth size={componentsize} label={FieldArray.label || ''} />}
                                                                            />
                                                                        </DatePickerWrapper>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "quarter") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    const quarterMap:{[key:string]:any} = {}
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined && defaultValuesNew[FieldArray.name] != "" && defaultValuesNew[FieldArray.name] != "0000-00" && defaultValuesNew[FieldArray.name] != "1971-01" && defaultValuesNew[FieldArray.name].length == 7) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]---------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                        if (defaultValuesNew[FieldArray.name] && defaultValuesNew[FieldArray.name] != "0000-00" && defaultValuesNew[FieldArray.name] != "1971-Q1" && defaultValuesNew[FieldArray.name].length == 7) {
                                                            const quarterValue = defaultValuesNew[FieldArray.name][6]
                                                            switch (quarterValue) {
                                                                case '1':
                                                                    quarterMap[FieldArray.name] = defaultValuesNew[FieldArray.name].substr(0, 4) + '-01-01 00:00:00'
                                                                    break;
                                                                case '2':
                                                                    quarterMap[FieldArray.name] = defaultValuesNew[FieldArray.name].substr(0, 4) + '-04-01 00:00:00'
                                                                    break;
                                                                case '3':
                                                                    quarterMap[FieldArray.name] = defaultValuesNew[FieldArray.name].substr(0, 4) + '-07-01 00:00:00'
                                                                    break;
                                                                case '4':
                                                                    quarterMap[FieldArray.name] = defaultValuesNew[FieldArray.name].substr(0, 4) + '-10-01 00:00:00'
                                                                    break;
                                                            }
                                                        }
                                                    }

                                                    // Add ' 00:00:00' to avoid the date minus one day in the DatePicker
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange, onBlur } }) => {
                                                                        
                                                                        //console.log("value---------------------------------", value)
                                                                        //console.log("quarterMap---------------------------------", quarterMap)
                                                                        
                                                                        return (
                                                                            <DatePickerWrapper sx={{ '& .react-datepicker-wrapper': { width: '290px' } }}>
                                                                                <DatePicker
                                                                                    selected={quarterMap[FieldArray.name] ? (new Date(quarterMap[FieldArray.name])) : (value ? new Date(value) : null)   }
                                                                                    id={FieldArray.name}
                                                                                    showQuarterYearPicker
                                                                                    locale={i18n.language}
                                                                                    dateFormat={FieldArray.dateFormat}
                                                                                    popperPlacement='bottom-start'
                                                                                    onChange={(date: Date) => {
                                                                                        const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                        if (date != undefined) {
                                                                                            defaultValuesNewTemp[FieldArray.name] = date.getFullYear() + "-Q" + Math.floor((date.getMonth() + 3) / 3)
                                                                                        }
                                                                                        else {
                                                                                            defaultValuesNewTemp[FieldArray.name] = "1971-Q1"
                                                                                        }
                                                                                        setDefaultValuesNew(defaultValuesNewTemp)
                                                                                        const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                        allDatesTemp[FieldArray.name] = defaultValuesNewTemp[FieldArray.name]
                                                                                        setAllDates(allDatesTemp)
                                                                                        onChange(date);
                                                                                        onBlur();
                                                                                    }}
                                                                                    placeholderText={FieldArray.placeholder}
                                                                                    customInput={<TextField fullWidth size={componentsize} label={FieldArray.label || ''} />}
                                                                                />
                                                                            </DatePickerWrapper>
                                                                        )
                                                                    }
                                                                    }
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "datetime") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined && defaultValuesNew[FieldArray.name] != "" && defaultValuesNew[FieldArray.name] != "0000-00-00 00:00:00" && defaultValuesNew[FieldArray.name] != "1971-01-01 00:00:00" && defaultValuesNew[FieldArray.name].length == 19) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]***************************", new Date(defaultValuesNew[FieldArray.name]+' 00:00:00'))
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                        
                                                        //const allDatesTemp = { ...allDates }
                                                        //allDatesTemp[FieldArray.name] = defaultValuesNew[FieldArray.name]
                                                        //setAllDates(allDatesTemp)
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange, onBlur } }) => (
                                                                        <DatePickerWrapper sx={{ 'z-index': 9999}}>
                                                                            <DatePicker
                                                                                showTimeSelect
                                                                                timeFormat='HH:mm'
                                                                                timeIntervals={15}
                                                                                selected={defaultValuesNew[FieldArray.name] && defaultValuesNew[FieldArray.name] != "0000-00-00 00:00:00" && defaultValuesNew[FieldArray.name] != "1971-01-01 00:00:00" && defaultValuesNew[FieldArray.name].length == 19 ? (new Date(defaultValuesNew[FieldArray.name])) : (value ? new Date(value) : null)   }
                                                                                id={FieldArray.name}
                                                                                showYearDropdown
                                                                                showMonthDropdown
                                                                                locale={i18n.language}
                                                                                dateFormat={FieldArray.dateFormat}
                                                                                popperPlacement='bottom-start'
                                                                                onChange={(date: Date) => {
                                                                                    console.log(new Date(date))
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    if (date != undefined) {
                                                                                        defaultValuesNewTemp[FieldArray.name] = date.getFullYear() + "-" + formatDateItem(date.getMonth() + 1) + "-" + formatDateItem(date.getDate()) + " " + formatDateItem(date.getHours()) + ":" + formatDateItem(date.getMinutes()) + ":" + formatDateItem(date.getSeconds())
                                                                                    }
                                                                                    else {
                                                                                        defaultValuesNewTemp[FieldArray.name] = "1971-01-01 00:00:00"
                                                                                    }
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                                    allDatesTemp[FieldArray.name] = defaultValuesNewTemp[FieldArray.name]
                                                                                    setAllDates(allDatesTemp)
                                                                                    onChange(date);
                                                                                    onBlur();
                                                                                }}
                                                                                minDate={FieldArray.StartDateTime != "" && FieldArray.StartDateTime != undefined && FieldArray.StartDateTime != "1971-01-01" ? new Date(FieldArray.StartDateTime) : new Date("1971-01-01 00:00:00")}
                                                                                maxDate={FieldArray.EndDateTime != "" && FieldArray.EndDateTime != undefined && FieldArray.EndDateTime != "1971-01-01" ? new Date(FieldArray.EndDateTime) : new Date("2099-12-31 00:00:00")}
                                                                                placeholderText={FieldArray.placeholder}
                                                                                customInput={<TextField fullWidth size={componentsize} label={FieldArray.label || ''} />}
                                                                            />
                                                                        </DatePickerWrapper>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "time") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined && defaultValuesNew[FieldArray.name] != "" && defaultValuesNew[FieldArray.name].length == 8) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]***************************", new Date(defaultValuesNew[FieldArray.name]+' 00:00:00'))
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                        const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                        allDatesTemp[FieldArray.name] = defaultValuesNew[FieldArray.name]
                                                        setAllDates(allDatesTemp)
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <DatePickerWrapper>
                                                                    <DatePicker
                                                                        showTimeSelect
                                                                        timeFormat='HH:mm'
                                                                        timeIntervals={15}
                                                                        showTimeSelectOnly
                                                                        selected={defaultValuesNew[FieldArray.name] && defaultValuesNew[FieldArray.name].length == 8 ? (new Date("2023-02-02 " + defaultValuesNew[FieldArray.name])) : (new Date()) }
                                                                        id={FieldArray.name}
                                                                        dateFormat={FieldArray.dateFormat}
                                                                        popperPlacement='bottom-start'
                                                                        onChange={(date: Date) => {
                                                                            console.log(new Date(date))
                                                                            const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                            if (date != undefined) {
                                                                                defaultValuesNewTemp[FieldArray.name] = formatDateItem(date.getHours()) + ":" + formatDateItem(date.getMinutes()) + ":" + formatDateItem(date.getSeconds())
                                                                            }
                                                                            else {
                                                                                defaultValuesNewTemp[FieldArray.name] = "00:00:00"
                                                                            }
                                                                            setDefaultValuesNew(defaultValuesNewTemp)
                                                                            const allDatesTemp:{[key:string]:any} = { ...allDates }
                                                                            allDatesTemp[FieldArray.name] = defaultValuesNewTemp[FieldArray.name]
                                                                            setAllDates(allDatesTemp)
                                                                        }
                                                                        }
                                                                        placeholderText={FieldArray.placeholder}
                                                                        customInput={<TextField fullWidth style={{ width: '100%' }} size={componentsize} label={FieldArray.label || ''} />}
                                                                    />
                                                                </DatePickerWrapper>
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "xlsx") {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        //setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <DropzoneWrapper>
                                                                    <div {...getRootPropsXlsx({ className: 'dropzone' })}>
                                                                        <input {...getInputPropsXlsx()} />
                                                                        <Box sx={{ display: 'flex', flexDirection: ['column', 'column', 'row'], alignItems: 'center' }}>
                                                                        <Box sx={{ display: 'flex', flexDirection: 'column', textAlign: ['center', 'center', 'inherit'] }}>
                                                                        <Typography color='textSecondary'>{FieldArray.label}:</Typography>
                                                                        </Box>
                                                                        </Box>
                                                                    </div>
                                                                    {uploadFiles && uploadFiles.length ? (
                                                                        <Fragment>
                                                                        <List>
                                                                        {uploadFiles.map((fileInfor: File | FileUrl) => {

                                                                            return (
                                                                                    <ListItem key={fileInfor.name}>
                                                                                        <div className='file-details' style={{overflow: 'hidden'}}>
                                                                                            <div className='file-preview'>{renderFilePreview(fileInfor)}</div>
                                                                                            <div>
                                                                                            {fileInfor['type']=="file" ? 
                                                                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']}>{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {fileInfor['type']=="image" ? 
                                                                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']} target="_blank">{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {(fileInfor['type']!="file" && fileInfor['type']!="image") ? 
                                                                                            <Typography className='file-name'>{fileInfor['name']}</Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            <Typography className='file-size' variant='body2'>
                                                                                                {Math.round(fileInfor.size / 100) / 10 > 1000
                                                                                                ? `${(Math.round(fileInfor.size / 100) / 10000).toFixed(1)} mb`
                                                                                                : `${(Math.round(fileInfor.size / 100) / 10).toFixed(1)} kb`}
                                                                                            </Typography>
                                                                                            </div>
                                                                                        </div>
                                                                                        <IconButton onClick={() => handleRemoveFile(fileInfor)}>
                                                                                            <Icon icon='mdi:close' fontSize={20} />
                                                                                        </IconButton>
                                                                                    </ListItem>
                                                                                    )
                                                                        })}
                                                                        </List>
                                                                        </Fragment>
                                                                    ) : null}
                                                                </DropzoneWrapper>
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "file") {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        //setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <DropzoneWrapper>
                                                                    <div {...getRootPropsFile({ className: 'dropzone' })}>
                                                                        <input {...getInputPropsFile()} />
                                                                        <Box sx={{ display: 'flex', flexDirection: ['column', 'column', 'row'], alignItems: 'center' }}>
                                                                        <Box sx={{ display: 'flex', flexDirection: 'column', textAlign: ['center', 'center', 'inherit'] }}>
                                                                        <Typography color='textSecondary'>{FieldArray.label}:</Typography>
                                                                        </Box>
                                                                        </Box>
                                                                    </div>
                                                                    {uploadFiles && uploadFiles.length ? (
                                                                        <Fragment>
                                                                        <List>
                                                                        {uploadFiles.map((fileInfor: File | FileUrl) => {

                                                                            return (
                                                                                    <ListItem key={fileInfor.name}>
                                                                                        <div className='file-details' style={{overflow: 'hidden'}}>
                                                                                            <div className='file-preview'>{renderFilePreview(fileInfor)}</div>
                                                                                            <div>
                                                                                            {fileInfor['type']=="file" ? 
                                                                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']}>{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {fileInfor['type']=="image" ? 
                                                                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']} target="_blank">{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {(fileInfor['type']!="file" && fileInfor['type']!="image") ? 
                                                                                            <Typography className='file-name'>{fileInfor['name']}</Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            <Typography className='file-size' variant='body2'>
                                                                                                {Math.round(fileInfor.size / 100) / 10 > 1000
                                                                                                ? `${(Math.round(fileInfor.size / 100) / 10000).toFixed(1)} mb`
                                                                                                : `${(Math.round(fileInfor.size / 100) / 10).toFixed(1)} kb`}
                                                                                            </Typography>
                                                                                            </div>
                                                                                        </div>
                                                                                        <IconButton onClick={() => handleRemoveFile(fileInfor)}>
                                                                                            <Icon icon='mdi:close' fontSize={20} />
                                                                                        </IconButton>
                                                                                    </ListItem>
                                                                                    )
                                                                        })}
                                                                        </List>
                                                                        </Fragment>
                                                                    ) : null}
                                                                </DropzoneWrapper>
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "files") {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        //setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <DropzoneWrapper>
                                                                    <div {...getRootPropsFiles({ className: 'dropzone' })}>
                                                                        <input {...getInputPropsFiles()} />
                                                                        <Box sx={{ display: 'flex', flexDirection: ['column', 'column', 'row'], alignItems: 'center' }}>
                                                                        <Box sx={{ display: 'flex', flexDirection: 'column', textAlign: ['center', 'center', 'inherit'] }}>
                                                                        <Typography color='textSecondary'>{FieldArray.label}:</Typography>
                                                                        </Box>
                                                                        </Box>
                                                                    </div>
                                                                    {uploadFiles && uploadFiles.length ? (
                                                                        <Fragment>
                                                                        <List>
                                                                        {uploadFiles.map((fileInfor: File | FileUrl) => {

                                                                            return (
                                                                                    <ListItem key={fileInfor.name}>
                                                                                        <div className='file-details' style={{overflow: 'hidden'}}>
                                                                                            <div className='file-preview'>{renderFilePreview(fileInfor)}</div>
                                                                                            <div>
                                                                                            {fileInfor['type']=="file" ? 
                                                                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']}>{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {fileInfor['type']=="image" ? 
                                                                                            <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']} target="_blank">{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {(fileInfor['type']!="file" && fileInfor['type']!="image") ? 
                                                                                            <Typography className='file-name'>{fileInfor['name']}</Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            <Typography className='file-size' variant='body2'>
                                                                                                {Math.round(fileInfor.size / 100) / 10 > 1000
                                                                                                ? `${(Math.round(fileInfor.size / 100) / 10000).toFixed(1)} mb`
                                                                                                : `${(Math.round(fileInfor.size / 100) / 10).toFixed(1)} kb`}
                                                                                            </Typography>
                                                                                            </div>
                                                                                        </div>
                                                                                        <IconButton onClick={() => handleRemoveFile(fileInfor)}>
                                                                                            <Icon icon='mdi:close' fontSize={20} />
                                                                                        </IconButton>
                                                                                    </ListItem>
                                                                                    )
                                                                        })}
                                                                        </List>
                                                                        <div className='buttons'>
                                                                            <Button color='error' variant='outlined' onClick={handleRemoveAllFiles}>
                                                                            {FieldArray.RemoveAll}
                                                                            </Button>
                                                                        </div>
                                                                        </Fragment>
                                                                    ) : null}
                                                                </DropzoneWrapper>
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "readonlyfiles") {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        //setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <DropzoneWrapper>
                                                                    <Box sx={{ display: 'flex', flexDirection: ['column', 'column', 'row'], alignItems: 'center' }}>
                                                                        <Box sx={{ display: 'flex', flexDirection: 'column', textAlign: ['center', 'center', 'inherit'] }}>
                                                                            <Typography color='textSecondary'>{FieldArray.label}:</Typography>
                                                                        </Box>
                                                                    </Box>
                                                                    {uploadFiles && uploadFiles.length ? (
                                                                        <Fragment>
                                                                        <List>
                                                                        {uploadFiles.map((fileInfor: File | FileUrl) => {

                                                                            return (
                                                                                    <ListItem key={fileInfor.name} style={{padding: "3px"}}>
                                                                                        <div className='file-details' style={{ display: 'flex', overflow: 'hidden'}}>
                                                                                            <div className='file-preview'>{renderFilePreview(fileInfor)}</div>
                                                                                            <div>
                                                                                            {fileInfor['type']=="file" ? 
                                                                                                <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']}>{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {fileInfor['type']=="image" ? 
                                                                                                <Typography className='file-name'><CustomLink href={authConfig.backEndApiHost+fileInfor['webkitRelativePath']} download={fileInfor['name']} target="_blank">{fileInfor['name']}</CustomLink></Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            {(fileInfor['type']!="file" && fileInfor['type']!="image") ? 
                                                                                                <Typography className='file-name'>{fileInfor['name']}</Typography>
                                                                                            :
                                                                                            ''
                                                                                            }
                                                                                            <Typography className='file-size' variant='body2'>
                                                                                                {Math.round(fileInfor.size / 100) / 10 > 1000
                                                                                                ? `${(Math.round(fileInfor.size / 100) / 10000).toFixed(1)} mb`
                                                                                                : `${(Math.round(fileInfor.size / 100) / 10).toFixed(1)} kb`}
                                                                                            </Typography>
                                                                                            </div>
                                                                                        </div>
                                                                                    </ListItem>
                                                                                    )
                                                                        })}
                                                                        </List>
                                                                        </Fragment>
                                                                    ) : null}
                                                                    {FieldArray.helptext && (
                                                                        <FormHelperText>
                                                                            {FieldArray.helptext}
                                                                        </FormHelperText>
                                                                    )}
                                                                    {errors[FieldArray.name] && (
                                                                        <FormHelperText sx={{ color: 'error.main' }}>
                                                                            {(errors[FieldArray.name]?.message as string)??''}
                                                                        </FormHelperText>
                                                                    )}
                                                                </DropzoneWrapper>
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "avatar") {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }

                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                                                    {avatorShowArea && avatorShowArea[FieldArray.name] ?
                                                                        (<ImgStyled src={avatorShowArea[FieldArray.name]} alt={FieldArray.helptext} />)
                                                                        : ( defaultValuesNew[FieldArray.name] ? <Box sx={{ display: 'flex', alignItems: 'center',cursor: 'pointer',':hover': {cursor: 'pointer',}, }} onClick={() => toggleImagesPreviewListDrawer([authConfig.backEndApiHost+defaultValuesNew[FieldArray.name]])}><ImgStyled src={authConfig.backEndApiHost+defaultValuesNew[FieldArray.name]} alt={FieldArray.helptext} /></Box> : <Box sx={{ display: 'flex', alignItems: 'center',}} ><ImgStyled src={'/images/avatars/1.png'} alt={FieldArray.helptext} /></Box> )
                                                                    }
                                                                    <div>
                                                                        <ButtonStyled component='label' variant='contained' htmlFor={FieldArray.name}>
                                                                            {FieldArray.label}
                                                                            <input
                                                                                hidden
                                                                                type='file'
                                                                                name={FieldArray.name}
                                                                                accept='image/png, image/jpeg'
                                                                                onChange={handleAvatorChange}
                                                                                id={FieldArray.name}
                                                                            />
                                                                        </ButtonStyled>
                                                                        <ResetButtonStyled color='secondary' variant='outlined' name={FieldArray.name} onClick={handleAvatorReset}>
                                                                        {FieldArray.Reset}
                                                                        </ResetButtonStyled>
                                                                        <Typography variant='caption' sx={{ mt: 4, display: 'block', color: 'text.disabled' }}>
                                                                        {FieldArray.AvatarFormatTip}
                                                                        </Typography>
                                                                    </div>
                                                                </Box>
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "readonlyavatar") {
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                                                    {avatorShowArea && avatorShowArea[FieldArray.name] ?
                                                                        (<ImgStyled src={avatorShowArea[FieldArray.name]} alt={FieldArray.helptext} />)
                                                                        : (<ImgStyled src={authConfig.backEndApiHost+defaultValuesNew[FieldArray.name]} alt={FieldArray.helptext} />)
                                                                    }
                                                                </Box>
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "slider") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <Box sx={{ width: 380 }}>
                                                                            <Typography sx={{ fontWeight: 500 }}>{FieldArray.label}</Typography>
                                                                            <Slider
                                                                                size={componentsize}
                                                                                min={FieldArray.min}
                                                                                max={FieldArray.max}
                                                                                step={FieldArray.step}
                                                                                marks={FieldArray.marks}
                                                                                onChange={(e) => {
                                                                                    onChange(e);
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[FieldArray.name] = (e.target as HTMLInputElement).value
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                }}
                                                                                value={Number(value)}
                                                                                valueLabelDisplay='auto'
                                                                                aria-labelledby='custom-marks-slider'
                                                                            />
                                                                        </Box>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "Switch") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, (defaultValuesNew[FieldArray.name] == "1" || defaultValuesNew[FieldArray.name] == "Yes" || defaultValuesNew[FieldArray.name] == "是" || defaultValuesNew[FieldArray.name] == "true") ? "1" : "0")
                                                    }
                                                    if (defaultValuesNew[FieldArray.name] != undefined) {
                                                        setValue(FieldArray.name, (defaultValuesNew[FieldArray.name] == "1" || defaultValuesNew[FieldArray.name] == "on" || defaultValuesNew[FieldArray.name] == "Yes" || defaultValuesNew[FieldArray.name] == "是" || defaultValuesNew[FieldArray.name] == "true" || defaultValuesNew[FieldArray.name] == true) ? "1" : "0")
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 0 }}>
                                                                <Controller
                                                                    name={FieldArray.name}
                                                                    control={control}
                                                                    render={({ field: { value, onChange } }) => (
                                                                        <Box sx={{ width: 380 }}>
                                                                            <Typography sx={{ fontWeight: 500 }}>{FieldArray.label}</Typography>
                                                                            <Switch
                                                                                size={componentsize}
                                                                                name='appBarBlur'
                                                                                checked={value=="1"?true:false}
                                                                                onChange={(e) => {
                                                                                    onChange(e);
                                                                                    const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                    defaultValuesNewTemp[FieldArray.name] = (defaultValuesNew[FieldArray.name] == "1" || defaultValuesNew[FieldArray.name] == "on" || defaultValuesNew[FieldArray.name] == "Yes" || defaultValuesNew[FieldArray.name] == "是" || defaultValuesNew[FieldArray.name] == "true" || defaultValuesNew[FieldArray.name] == true) ? false : true
                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                }}
                                                                            />
                                                                        </Box>
                                                                    )}
                                                                />
                                                                {FieldArray.helptext && (
                                                                    <FormHelperText>
                                                                        {FieldArray.helptext}
                                                                    </FormHelperText>
                                                                )}
                                                                {errors[FieldArray.name] && (
                                                                    <FormHelperText sx={{ color: 'error.main' }}>
                                                                        {(errors[FieldArray.name]?.message as string)??''}
                                                                    </FormHelperText>
                                                                )}
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "editor") {
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <FormControl fullWidth sx={{ mb: 3 }}>
                                                                <EditorWrapper>
                                                                    <Box >
                                                                        <Typography sx={{ fontWeight: 500, mb: 3 }}>{FieldArray.label}</Typography>
                                                                        <ReactDraftWysiwyg
                                                                            editorState={allEditorValues[FieldArray.name] ? allEditorValues[FieldArray.name] : EditorState.createWithContent(ContentState.createFromBlockArray(convertFromHTML(defaultValuesNew[FieldArray.name]).contentBlocks, convertFromHTML(defaultValuesNew[FieldArray.name]).entityMap,))}
                                                                            onEditorStateChange={(data) => {
                                                                                const allEditorValuesTemp = { ...allEditorValues }
                                                                                allEditorValuesTemp[FieldArray.name] = data
                                                                                setAllEditorValues(allEditorValuesTemp)
                                                                            }
                                                                            }
                                                                            placeholder={FieldArray.placeholder}
                                                                            toolbar={{
                                                                                options: ['inline', 'textAlign'],
                                                                                inline: {
                                                                                    inDropdown: false,
                                                                                    options: ['bold', 'italic', 'underline', 'strikethrough']
                                                                                }
                                                                            }}
                                                                        />
                                                                    </Box>
                                                                </EditorWrapper>
                                                            </FormControl>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show || fieldArrayShow[FieldArray.name]) && FieldArray.type == "UserRoleMenuDetail") {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                    if (action.indexOf("edit_default") != -1 && defaultValuesNew[FieldArray.name] != undefined) {
                                                        
                                                        //console.log("defaultValuesNew[FieldArray.name]--------------------------------", defaultValuesNew[FieldArray.name])
                                                        setValue(FieldArray.name, defaultValuesNew[FieldArray.name])
                                                    }
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <TableContainer>
                                                            <Table size='small'>
                                                            <TableHead>
                                                                <TableRow>
                                                                <TableCell sx={{ pl: '0 !important' }}>
                                                                    <Box
                                                                    sx={{
                                                                        display: 'flex',
                                                                        fontSize: '0.875rem',
                                                                        whiteSpace: 'nowrap',
                                                                        alignItems: 'center',
                                                                        textTransform: 'capitalize',
                                                                        '& svg': { ml: 1, cursor: 'pointer' }
                                                                    }}
                                                                    >
                                                                    </Box>
                                                                </TableCell>
                                                                </TableRow>
                                                            </TableHead>
                                                            <TableBody>
                                                            <TableRow key={"TableRow_" + FieldArray_index} sx={{ '& .MuiTableCell-root:first-of-type': { pl: '0 !important' } }}>
                                                            
                                                            {Object.keys(FieldArray.MenuTwoArray).map((MenuOneName: string, MenuOneName_index: number) => {
                                                                const MenuTwoArray = FieldArray.MenuTwoArray[MenuOneName]
                                                                
                                                                return (                                                                    
                                                                    <TableCell
                                                                        key={MenuOneName_index}
                                                                        sx={{
                                                                        verticalAlign: "top",
                                                                        fontWeight: 600,
                                                                        whiteSpace: 'nowrap',
                                                                        color: theme => `${theme.palette.text.primary} !important`
                                                                        }}
                                                                    >
                                                                        <Table size='small'>
                                                                            <TableHead>
                                                                                <TableRow key={"MenuOneName_" + MenuOneName_index} sx={{ '& .MuiTableCell-root:first-of-type': { pl: '0 !important' } }}>                                                                                
                                                                                    <TableCell colSpan={3}>
                                                                                        {MenuOneName} 
                                                                                    </TableCell>   
                                                                                </TableRow>
                                                                                <TableRow key={"TableCell_" + MenuOneName_index} sx={{ '& .MuiTableCell-root:first-of-type': { pl: '0 !important' } }}>                                                                                
                                                                                <TableCell colSpan={3}>
                                                                                    <FormControlLabel
                                                                                    label={FieldArray.SelectAll}
                                                                                    sx={{ '& .MuiTypography-root': { textTransform: 'capitalize' } }}
                                                                                    control={
                                                                                        <Checkbox
                                                                                        size='small'
                                                                                        value={MenuOneName} 
                                                                                        onChange={handleSelectAllCheckbox}
                                                                                        indeterminate={isIndeterminateCheckbox[MenuOneName]}
                                                                                        checked={selectedCheckbox[MenuOneName] && selectedCheckbox[MenuOneName].length === menuTwoCount[MenuOneName] }
                                                                                        />
                                                                                    }
                                                                                    />
                                                                                </TableCell>   
                                                                                </TableRow>
                                                                            </TableHead>
                                                                            <TableBody>                                                                                
                                                                                {Object.keys(MenuTwoArray).map((MenuTwoName: string, MenuTwoName_index: number) => {
                                                                                    const MenuThreeArray = MenuTwoArray[MenuTwoName]
                                                                                    
                                                                                    return ( 
                                                                                        <Fragment key={MenuTwoName_index}>
                                                                                            {MenuThreeArray.length==1 && MenuThreeArray.map((MenuThreeRecord: any, MenuThreeRecord_index: number) => {
                                                                                                const checkboxid = MenuThreeRecord['id']
                                                                                                
                                                                                                return (
                                                                                                <TableRow key={`${MenuThreeRecord['id']}_${MenuThreeRecord_index}`} sx={{ '& .MuiTableCell-root:first-of-type': { pl: '0 !important' } }}>
                                                                                                    <TableCell>
                                                                                                        <FormControlLabel
                                                                                                        label={MenuThreeRecord['MenuTwoName']}
                                                                                                        control={
                                                                                                            <Checkbox
                                                                                                            size='small'
                                                                                                            id={checkboxid}
                                                                                                            onChange={() => RoleMenuElementPermission(checkboxid, MenuOneName)}
                                                                                                            checked={selectedCheckbox[MenuOneName] && selectedCheckbox[MenuOneName].includes(checkboxid) ? true : false}
                                                                                                            />
                                                                                                        }
                                                                                                        />
                                                                                                    </TableCell>
                                                                                                </TableRow>
                                                                                                )
                                                                                            })}

                                                                                            {MenuThreeArray.length>1 ?
                                                                                                <Fragment>
                                                                                                    <TableRow key={`${MenuTwoName}_${MenuTwoName_index}_TableRow`} sx={{ '& .MuiTableCell-root:first-of-type': { pl: '0 !important' } }}>
                                                                                                        <TableCell>
                                                                                                            <FormControlLabel
                                                                                                            style={{paddingLeft:"30px"}}
                                                                                                            label={MenuTwoName}
                                                                                                            control={
                                                                                                                <Fragment></Fragment>
                                                                                                            }
                                                                                                            />
                                                                                                        </TableCell>
                                                                                                    </TableRow>
                                                                                                </Fragment>
                                                                                                :
                                                                                                ''
                                                                                            }
                                                                                            {MenuThreeArray.length>1 && MenuThreeArray.map((MenuThreeRecord: any, MenuThreeRecord_index: number) => {
                                                                                                const checkboxid = MenuThreeRecord['id']
                                                                                                
                                                                                                return (
                                                                                                <TableRow key={`${MenuThreeRecord['id']}_${MenuThreeRecord_index}`} sx={{ '& .MuiTableCell-root:first-of-type': { pl: '0 !important' } }}>
                                                                                                    <TableCell>
                                                                                                        <FormControlLabel 
                                                                                                        style={{paddingLeft:"20px"}}
                                                                                                        label={MenuThreeRecord['MenuThreeName']}
                                                                                                        control={
                                                                                                            <Checkbox
                                                                                                            size='small'
                                                                                                            id={checkboxid}
                                                                                                            onChange={() => RoleMenuElementPermission(checkboxid, MenuOneName)}
                                                                                                            checked={selectedCheckbox[MenuOneName] && selectedCheckbox[MenuOneName].includes(checkboxid) ? true : false}
                                                                                                            />
                                                                                                        }
                                                                                                        />
                                                                                                    </TableCell>
                                                                                                </TableRow>
                                                                                                )
                                                                                            })}
                                                                                        </Fragment>
                                                                                    )
                                                                                })}        
                                                                            </TableBody>
                                                                        </Table>
                                                                    </TableCell>
                                                                )
                                                                    
                                                                })}
                                                                </TableRow>
                                                            </TableBody>
                                                            </Table>
                                                        </TableContainer>
                                                        </Grid>
                                                    )
                                                }
                                                else if ((FieldArray.show) && FieldArray.type == "divider") {
                                                    
                                                    return (
                                                        <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                            <Divider />
                                                        </Grid>
                                                    )
                                                }
                                                else if (!FieldArray.show) {
                                                    
                                                    //console.log("****************************** Hidden Not Show", FieldArray)
                                                }
                                                else {
                                                    
                                                    //console.log("defaultValuesNew[FieldArray.name]***************Begin", FieldArray)
                                                }
                                            })}
                                        </Grid>
                                    </CardContent>
                                </Card>
                            )
                        })}

                        {addEditStructInfo2.childtable && addEditStructInfo2.childtable.allFields && addEditStructInfo2.childtable.submittext ?
                            <Card key={"ChildtableSection"} sx={{ mb: 2 }}>
                                <RepeaterWrapper>
                                    <Repeater count={childItemCounter}>
                                    {(i: number) => {
                                        const Tag = i === 0 ? Box : Collapse

                                        return (
                                        <Tag key={i} className='repeater-wrapper' {...(i !== 0 ? { in: true } : {})}>
                                            <Grid container>
                                            <RepeatingContent item xs={12}>
                                                <Grid container sx={{ pl: 1, py: 2, width: '100%', pr: 1 }}>
                                                    {addEditStructInfo2.childtable.allFields.Default.map((FieldArray: any, FieldArray_index: number) => {
                                                        const NewFieldName = "ChildTable____" + i + "____" + FieldArray.name
                                                        if (FieldArray.show && (FieldArray.type == "input" || FieldArray.type == "email" || FieldArray.type == "number")) {
                                                            if (defaultValuesNew[NewFieldName] != undefined) {
                                                                setValue(NewFieldName, defaultValuesNew[NewFieldName])
                                                            }
                                                            else if (defaultValuesNew[NewFieldName] == undefined) {
                                                                setValue(NewFieldName, "")
                                                            }
                                                            
                                                            return (
                                                                <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"ChildAllFields_" + FieldArray_index} sx={{ml:1, mr:1}} >
                                                                    <FormControl fullWidth sx={{ mb: 0 }}>
                                                                        <Controller
                                                                            name={NewFieldName}
                                                                            control={control}
                                                                            render={({ field: { value, onChange } }) => (
                                                                                <TextField
                                                                                    size='small'
                                                                                    disabled={FieldArray.rules.disabled}
                                                                                    value={value}
                                                                                    label={FieldArray.label}
                                                                                    type={FieldArray.type}
                                                                                    InputProps={FieldArray.inputProps ? FieldArray.inputProps : {}}
                                                                                    onChange={(e) => {
                                                                                        onChange(e);
                                                                                        const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                        if(FieldArray.inputProps && FieldArray.inputProps.step && FieldArray.inputProps.step=='0.01' && String(e.target.value).split('.')[1] && String(e.target.value).split('.')[1].length>2)  {
                                                                                            defaultValuesNewTemp[NewFieldName] = parseFloat(e.target.value).toFixed(2)
                                                                                        }
                                                                                        else if(FieldArray.inputProps && FieldArray.inputProps.step && FieldArray.inputProps.step=='0.1' && String(e.target.value).split('.')[1] && String(e.target.value).split('.')[1].length>1)  {
                                                                                            defaultValuesNewTemp[NewFieldName] = parseFloat(e.target.value).toFixed(1)
                                                                                        }
                                                                                        else if(FieldArray.inputProps && FieldArray.inputProps.step && FieldArray.inputProps.step=='1' && String(e.target.value).includes('.'))  {
                                                                                            defaultValuesNewTemp[NewFieldName] = parseFloat(e.target.value).toFixed(0)
                                                                                        }
                                                                                        else {
                                                                                            defaultValuesNewTemp[NewFieldName] = e.target.value
                                                                                        }
                                                                                        setDefaultValuesNew(defaultValuesNewTemp)
                                                                                        
                                                                                        //Formula Method
                                                                                        if(FieldArray.Formula && FieldArray.Formula.FormulaMethod && FieldArray.Formula.FormulaMethod!="" && FieldArray.Formula.FormulaMethod!="None" && FieldArray.Formula.FormulaMethodField && FieldArray.Formula.FormulaMethodField!="" && FieldArray.Formula.FormulaMethodTarget && FieldArray.Formula.FormulaMethodTarget!="") {
                                                                                            const NewFormulaMethodField = "ChildTable____" + i + "____" + FieldArray.Formula.FormulaMethodField
                                                                                            const NewFormulaMethodTarget = "ChildTable____" + i + "____" + FieldArray.Formula.FormulaMethodTarget
                                                                                            console.log(defaultValuesNewTemp[NewFormulaMethodField])
                                                                                            console.log(e.target.value)
                                                                                            if( defaultValuesNewTemp[NewFormulaMethodField] && e.target.value) {
                                                                                                console.log("NewFormulaMethodField",NewFormulaMethodField)
                                                                                                console.log("NewFormulaMethodTarget",NewFormulaMethodTarget)
                                                                                                console.log("defaultValuesNewTemp",defaultValuesNewTemp)
                                                                                                if(FieldArray.Formula.FormulaMethod=='*') {
                                                                                                    const ThisInputValue: any = e.target.value
                                                                                                    const NewValue = defaultValuesNewTemp[NewFormulaMethodField] * ThisInputValue
                                                                                                    if(String(NewValue).split('.')[1] && String(NewValue).split('.')[1].length>2)  {
                                                                                                        defaultValuesNewTemp[NewFormulaMethodTarget] = parseFloat(String(NewValue)).toFixed(2)
                                                                                                    }
                                                                                                    else {
                                                                                                        defaultValuesNewTemp[NewFormulaMethodTarget] = NewValue
                                                                                                    }
                                                                                                    setDefaultValuesNew(defaultValuesNewTemp)
                                                                                                }

                                                                                            }
                                                                                        }
                                                                                    }}
                                                                                    placeholder={FieldArray.placeholder}
                                                                                    error={Boolean(errors[NewFieldName])}
                                                                                />
                                                                            )}
                                                                        />
                                                                        {FieldArray.helptext && (
                                                                            <FormHelperText>
                                                                                {FieldArray.helptext}
                                                                            </FormHelperText>
                                                                        )}
                                                                        {errors[NewFieldName] && (
                                                                            <FormHelperText sx={{ color: 'error.main' }}>
                                                                                {(errors[NewFieldName]?.message as string)??''}
                                                                            </FormHelperText>
                                                                        )}
                                                                    </FormControl>
                                                                </Grid>
                                                            )
                                                        }
                                                        else if (FieldArray.show && FieldArray.type == "readonly") {
                                                            if (defaultValuesNew[NewFieldName] != undefined) {
                                                                setValue(NewFieldName, defaultValuesNew[NewFieldName])
                                                            }
                                                            else if (defaultValuesNew[NewFieldName] == undefined) {
                                                                setValue(NewFieldName, "")
                                                            }
                                                            
                                                            return (
                                                                <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"ChildAllFields_" + FieldArray_index} sx={{ml:1, mr:1}} >
                                                                    <FormControl fullWidth sx={{ mb: 0 }}>
                                                                        <Controller
                                                                            name={NewFieldName}
                                                                            control={control}
                                                                            render={({ field: { value, onChange } }) => (
                                                                                <TextField
                                                                                    size='small'
                                                                                    disabled={FieldArray.rules.disabled}
                                                                                    value={value}
                                                                                    label={FieldArray.label}
                                                                                    type={FieldArray.type}
                                                                                    InputProps={FieldArray.inputProps ? FieldArray.inputProps : {}}
                                                                                    onChange={(e) => {
                                                                                        onChange(e);
                                                                                        const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                        if(FieldArray.inputProps && FieldArray.inputProps.step && FieldArray.inputProps.step=='0.01' && String(e.target.value).split('.')[1] && String(e.target.value).split('.')[1].length>2)  {
                                                                                            defaultValuesNewTemp[NewFieldName] = parseFloat(e.target.value).toFixed(2)
                                                                                        }
                                                                                        else {
                                                                                            defaultValuesNewTemp[NewFieldName] = e.target.value
                                                                                        }
                                                                                        setDefaultValuesNew(defaultValuesNewTemp)
                                                                                    }}
                                                                                    placeholder={FieldArray.placeholder}
                                                                                    error={Boolean(errors[NewFieldName])}
                                                                                />
                                                                            )}
                                                                        />
                                                                        {FieldArray.helptext && (
                                                                            <FormHelperText>
                                                                                {FieldArray.helptext}
                                                                            </FormHelperText>
                                                                        )}
                                                                        {errors[NewFieldName] && (
                                                                            <FormHelperText sx={{ color: 'error.main' }}>
                                                                                {(errors[NewFieldName]?.message as string)??''}
                                                                            </FormHelperText>
                                                                        )}
                                                                    </FormControl>
                                                                </Grid>
                                                            )
                                                        }
                                                        else if ((FieldArray.show || fieldArrayShow[NewFieldName]) && FieldArray.type == "autocomplete") {
                                                            const NewFieldCode = "ChildTable____" + i + "____" + FieldArray.code
                                                            if(NewFieldName!=NewFieldCode) {
                                                                if(defaultValuesNew[NewFieldCode]!="" && defaultValuesNew[NewFieldCode]!=undefined && defaultValuesNew[NewFieldName]==undefined && FieldArray && FieldArray.options && FieldArray.options.length>0 ) {
                                                                    FieldArray.options.map((ItemValue: any) => {
                                                                        if(ItemValue.value==defaultValuesNew[NewFieldCode]) {
                                                                            setValue(NewFieldName, ItemValue.label)
                                                                            setValue(NewFieldCode, ItemValue.value)
                                                                        }
                                                                    })
                                                                }
                                                                if(defaultValuesNew[NewFieldCode]!="" && defaultValuesNew[NewFieldCode]!=undefined && defaultValuesNew[NewFieldName]!=undefined)  {
                                                                    setValue(NewFieldName, defaultValuesNew[NewFieldName])
                                                                }
                                                                if(defaultValuesNew[NewFieldName]==undefined && defaultValuesNew[NewFieldCode]==undefined)  {
                                                                    setValue(NewFieldName, "")
                                                                    setValue(NewFieldCode, "")
                                                                }
                                                            }

                                                            if(defaultValuesNew[NewFieldCode]==undefined)  {
                                                                setValue(NewFieldCode, "")
                                                            }
                                                            else {                                                        
                                                                setValue(NewFieldCode, defaultValuesNew[NewFieldCode])
                                                            }
                                                            
                                                            const options = FieldArray.options
                                                            
                                                            return (
                                                                <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                                    <FormControl fullWidth sx={{ mb: 0 }}>
                                                                        <Controller
                                                                            name={NewFieldName}
                                                                            control={control}
                                                                            render={({ field: { value } }) => (
                                                                                <Autocomplete
                                                                                    size="small"
                                                                                    value={value}
                                                                                    options={options}
                                                                                    disabled={FieldArray.rules.disabled}
                                                                                    freeSolo={FieldArray.freeSolo}
                                                                                    id="controllable-states-demo"
                                                                                    isOptionEqualToValue={(option:any, value) => { return option.value === value; }}
                                                                                    renderInput={(params) => <TextField {...params} label={FieldArray.label} />}
                                                                                    onChange={(event: any, newValue: any) => {
                                                                                        if (newValue != undefined) {
                                                                                            const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                            if(NewFieldName!=NewFieldCode) {
                                                                                                defaultValuesNewTemp[NewFieldName] = newValue.label
                                                                                                defaultValuesNewTemp[NewFieldCode] = newValue.value
                                                                                            }
                                                                                            else    {
                                                                                                defaultValuesNewTemp[NewFieldCode] = newValue.value
                                                                                            }
                                                                                            setDefaultValuesNew(defaultValuesNewTemp)
                                                                                            
                                                                                            //This field will control other fields show or not
                                                                                            const fieldArrayShowTemp:{[key:string]:any} = {}
                                                                                            if (FieldArray.EnableFields && FieldArray.EnableFields != undefined && FieldArray.EnableFields[newValue.value] != undefined) {
                                                                                                for (const fieldItem of FieldArray.EnableFields[newValue.value]) {
                                                                                                    fieldArrayShowTemp[fieldItem] = true
                                                                                                }
                                                                                            }
                                                                                            if (FieldArray.DisableFields && FieldArray.DisableFields != undefined && FieldArray.DisableFields[newValue.value] != undefined) {
                                                                                                for (const fieldItem of FieldArray.DisableFields[newValue.value]) {
                                                                                                    fieldArrayShowTemp[fieldItem] = false
                                                                                                }
                                                                                            }
                                                                                            setFieldArrayShow(fieldArrayShowTemp)
                                                                                        }
                                                                                        else {
                                                                                            const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                            defaultValuesNewTemp[NewFieldName] = ""
                                                                                            defaultValuesNewTemp[NewFieldCode] = ""
                                                                                            setDefaultValuesNew(defaultValuesNewTemp)
                                                                                            setValue(NewFieldName, "")
                                                                                            setValue(NewFieldCode, "")
                                                                                        }
                                                                                    }}
                                                                                />
                                                                            )}
                                                                        />
                                                                        {FieldArray.helptext && (
                                                                            <FormHelperText>
                                                                                {FieldArray.helptext}
                                                                            </FormHelperText>
                                                                        )}
                                                                        {errors[NewFieldName] && (
                                                                            <FormHelperText sx={{ color: 'error.main' }}>
                                                                                {(errors[NewFieldName]?.message as string)??''}
                                                                            </FormHelperText>
                                                                        )}
                                                                    </FormControl>
                                                                </Grid>
                                                            )
                                                        }
                                                        else if ((FieldArray.show || fieldArrayShow[NewFieldName]) && FieldArray.type == "jumpwindow") {
                                                            const NewFieldCode = "ChildTable____" + i + "____" + FieldArray.code
                                                            if(NewFieldName!=NewFieldCode) {
                                                                if(defaultValuesNew[NewFieldCode]!="" && defaultValuesNew[NewFieldCode]!=undefined && defaultValuesNew[NewFieldName]==undefined && FieldArray && FieldArray.options && FieldArray.options.length>0 ) {
                                                                    FieldArray.options.map((ItemValue: any) => {
                                                                        if(ItemValue.value==defaultValuesNew[NewFieldCode]) {
                                                                            setValue(NewFieldName, ItemValue.label)
                                                                            setValue(NewFieldCode, ItemValue.value)
                                                                        }
                                                                    })
                                                                }
                                                                if(defaultValuesNew[NewFieldCode]!="" && defaultValuesNew[NewFieldCode]!=undefined && defaultValuesNew[NewFieldName]!=undefined)  {
                                                                    setValue(NewFieldName, defaultValuesNew[NewFieldName])
                                                                }
                                                                if(defaultValuesNew[NewFieldName]==undefined && defaultValuesNew[NewFieldCode]==undefined)  {
                                                                    setValue(NewFieldName, "")
                                                                    setValue(NewFieldCode, "")
                                                                }
                                                            }

                                                            if(defaultValuesNew[NewFieldCode]==undefined)  {
                                                                setValue(NewFieldCode, "")
                                                            }
                                                            else {                                                        
                                                                setValue(NewFieldCode, defaultValuesNew[NewFieldCode])
                                                            }

                                                            return (
                                                                <Grid item xs={FieldArray.rules.xs} sm={FieldArray.rules.sm} key={"AllFields_" + FieldArray_index}>
                                                                    <FormControl fullWidth sx={{ mb: 0 }}>
                                                                        <Controller
                                                                            name={NewFieldName}
                                                                            control={control}
                                                                            render={({ field: { value, onChange } }) => (
                                                                                <Fragment>
                                                                                    <TextField
                                                                                        size='small'
                                                                                        disabled={FieldArray.rules.disabled}
                                                                                        value={value}
                                                                                        label={FieldArray.label}
                                                                                        type={FieldArray.type}
                                                                                        InputProps={FieldArray.inputProps ? FieldArray.inputProps : {}}
                                                                                        onChange={(e) => {
                                                                                            onChange(e);
                                                                                            const defaultValuesNewTemp:{[key:string]:any} = { ...defaultValuesNew }
                                                                                            defaultValuesNewTemp[NewFieldName] = e.target.value
                                                                                            setDefaultValuesNew(defaultValuesNewTemp)
                                                                                        }}
                                                                                        onSelect={(event: FocusEvent<HTMLInputElement>) => {
                                                                                            event.target.blur();
                                                                                            const jumpWindowIsShowTemp:{[key:string]:any} = { ...jumpWindowIsShow }
                                                                                            jumpWindowIsShowTemp[NewFieldName] = true
                                                                                            setJumpWindowIsShow(jumpWindowIsShowTemp)
                                                                                        }}
                                                                                        placeholder={FieldArray.placeholder}
                                                                                        error={Boolean(errors[NewFieldName])}
                                                                                    />
                                                                                    <Dialog
                                                                                        fullWidth
                                                                                        open={jumpWindowIsShow[NewFieldName]==true?true:false}
                                                                                        scroll='body'
                                                                                        maxWidth='md'
                                                                                        onClose={()=>handleDialogWindowClose()}
                                                                                        onBackdropClick={()=>handleDialogWindowClose()}
                                                                                        TransitionComponent={Transition}
                                                                                    >
                                                                                        <DialogContent
                                                                                        sx={{
                                                                                            pt: { xs: 8, sm: 12.5 },
                                                                                            pr: { xs: 5, sm: 12 },
                                                                                            pb: { xs: 5, sm: 9.5 },
                                                                                            pl: { xs: 4, sm: 11 },
                                                                                            position: 'relative'
                                                                                        }}
                                                                                        >
                                                                                        <IconButton size='small' onClick={()=>handleDialogWindowClose()} sx={{ position: 'absolute', right: '1rem', top: '1rem' }}>
                                                                                            <Icon icon='mdi:close' />
                                                                                        </IconButton>
                                                                                        <Box sx={{ mb: 8, textAlign: 'center' }}>
                                                                                            <Typography variant='h5' sx={{ mb: 3 }}>{FieldArray.jumpWindowTitle}</Typography>
                                                                                            <Typography variant='body2'>{FieldArray.jumpWindowSubTitle}.</Typography>
                                                                                        </Box>
                                                                                        <Box sx={{ display: 'flex', flexWrap: { xs: 'wrap', md: 'nowrap' } }}>
                                                                                            <TabContext value={activeTab}>
                                                                                            <TabPanel value='detailsTab' sx={{ flexGrow: 1 }}>
                                                                                                <IndexJumpDialogWindow handleDialogWindowCloseWithParam={handleDialogWindowCloseWithParam} NewFieldName={NewFieldName} NewFieldCode={NewFieldCode} FieldArray={FieldArray} />
                                                                                            </TabPanel>
                                                                                            </TabContext>
                                                                                        </Box>
                                                                                        </DialogContent>
                                                                                    </Dialog>
                                                                                </Fragment>
                                                                            )}
                                                                        />
                                                                        {FieldArray.helptext && (
                                                                            <FormHelperText>
                                                                                {FieldArray.helptext}
                                                                            </FormHelperText>
                                                                        )}
                                                                        {errors[NewFieldName] && (
                                                                            <FormHelperText sx={{ color: 'error.main' }}>
                                                                                {(errors[NewFieldName]?.message as string)??''}
                                                                            </FormHelperText>
                                                                        )}
                                                                    </FormControl>
                                                                </Grid>
                                                            )
                                                        }

                                                    })}

                                                </Grid>
                                                {addEditStructInfo2.childtable && addEditStructInfo2.childtable.Delete ?
                                                <ChildTableRowAction>
                                                    <IconButton size='small' onClick={(event: SyntheticEvent)=>deleteChildTableItem(event, i)}>
                                                        <Icon icon='mdi:close' fontSize={20} />
                                                    </IconButton>
                                                </ChildTableRowAction>
                                                :''}
                                            </RepeatingContent>
                                            </Grid>
                                        </Tag>
                                        )
                                    }}
                                    </Repeater>
                                    
                                    {addEditStructInfo2.childtable && addEditStructInfo2.childtable.Add ?
                                    <Grid container sx={{ mt: 4 }}>
                                        <Grid item xs={12} sx={{ px: 0 }}>
                                            <Button
                                            size='small'
                                            variant='contained'
                                            startIcon={<Icon icon='mdi:plus' fontSize={20} />}
                                            onClick={() => setChildItemCounter(childItemCounter + 1)}
                                            >
                                            {addEditStructInfo2.childtable.submittext}
                                            </Button>
                                        </Grid>
                                    </Grid>
                                    : ''}
                                </RepeaterWrapper>
                            </Card>
                            : ''
                        }

                        {((addEditStructInfo2.submittext && addEditStructInfo2.submittext) || (addEditStructInfo2.canceltext && addEditStructInfo2.canceltext)) != "" ?
                            <Grid item xs={12} sm={12} container justifyContent="space-around" sx={{ pt: 4 }}>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                    {addEditStructInfo2.submittext && addEditStructInfo2.submittext != "" ?
                                        <Tooltip title="Alt+s">
                                            <Button size={componentsize} type='submit' variant='contained' sx={{ mr: 3 }}>
                                                {isSubmitLoading ? (
                                                    <CircularProgress
                                                        sx={{
                                                            color: 'common.white',
                                                            width: '20px !important',
                                                            height: '20px !important',
                                                            mr: theme => theme.spacing(2)
                                                        }}
                                                    />
                                                ) : null}
                                                {addEditStructInfo2.submittext}
                                            </Button>
                                        </Tooltip>
                                        : ''
                                    }
                                    {addEditStructInfo2.canceltext && addEditStructInfo2.canceltext != "" ?
                                        <Tooltip title="Alt+c">
                                            <Button size='small' variant='outlined' color='secondary' onClick={handleClose}>
                                                {addEditStructInfo2.canceltext}
                                            </Button>
                                        </Tooltip>
                                        : ''
                                    }
                                </Box>
                            </Grid>
                            : ''
                        }
                    </form>
                )}
            </Grid>
            
        </Fragment>
    )
}

export default AddOrEditTableCore
