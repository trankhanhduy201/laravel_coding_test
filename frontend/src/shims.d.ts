declare module 'vue' {
  export type ComputedRef<T = any> = { readonly value: T }
  export type Ref<T = any> = { value: T }
  export const computed: <T>(getter: () => T) => ComputedRef<T>
  export const reactive: <T extends object>(target: T) => T
  export const ref: <T = any>(value: T) => Ref<T>
  export const onMounted: (callback: () => void | Promise<void>) => void
  export const watch: (...args: any[]) => void
  export const createApp: (...args: any[]) => any
}

declare module 'vue-router' {
  export type RouteRecordRaw = Record<string, any>
  export const createRouter: (...args: any[]) => any
  export const createWebHistory: (...args: any[]) => any
  export const useRoute: () => any
  export const useRouter: () => any
}

declare module '*.vue' {
  const component: any
  export default component
}

declare interface ImportMetaEnv {
  readonly VITE_API_BASE_URL: string
}

declare interface ImportMeta {
  readonly env: ImportMetaEnv
}
