// ** Toolkit imports
import { configureStore } from '@reduxjs/toolkit'

// ** Reducers
import chat from 'src/store0/apps/chat'
import email from 'src/store0/apps/email'
import invoice from 'src/store0/apps/invoice'
import calendar from 'src/store0/apps/calendar'
import permissions from 'src/store0/apps/permissions'

export const store = configureStore({
  reducer: {
    chat,
    email,
    invoice,
    calendar,
    permissions
  },
  middleware: getDefaultMiddleware =>
    getDefaultMiddleware({
      serializableCheck: false
    })
})

export type AppDispatch = typeof store.dispatch
export type RootState = ReturnType<typeof store.getState>