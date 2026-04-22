declare namespace NodeJS {
    interface ProcessEnv {
        NEXT_PUBLIC_API_URL?: string;
    }
}

declare var process: {
    env: NodeJS.ProcessEnv;
};

// Modül bulunamadı hatalarını gidermek için ambient tanımlar
declare module "react" {
    export function useState<T>(initialState: T | (() => T)): [T, (newState: T | ((prevState: T) => T)) => void];
    export function useEffect(effect: () => void | (() => void), deps?: any[]): void;
    export function useCallback<T extends (...args: any[]) => any>(callback: T, deps: any[]): T;
    export default React;
}
declare module "react-dom";
declare module "@dnd-kit/core";
declare module "@dnd-kit/sortable";
declare module "@dnd-kit/utilities";
declare module "lucide-react";
declare module "clsx";
declare module "tailwind-merge";
