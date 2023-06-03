// ** Toolkit imports
import { configureStore } from '@reduxjs/toolkit'

// ** Reducers
import user from 'src/pages/Enginee/store'
import permissions from 'src/store0/apps/permissions'
import invoice from 'src/store0/apps/invoice'

export const store = configureStore({
  reducer: {
    user,
    permissions,
    invoice
  },
  middleware: getDefaultMiddleware =>
    getDefaultMiddleware({
      serializableCheck: false
    })
})

export type AppDispatch = typeof store.dispatch
export type RootState = ReturnType<typeof store.getState>