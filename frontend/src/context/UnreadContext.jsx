import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { notificationsApi } from "../api";

const UnreadContext = createContext({ count: 0, refresh: () => {}, setCount: () => {} });

export const POLL_INTERVAL_MS = 30_000;

/** Keeps the unread badge up to date by polling the API. */
export function UnreadProvider({ children }) {
  const [count, setCount] = useState(0);

  const refresh = useCallback(() => {
    notificationsApi.unreadCount().then(setCount).catch(() => {});
  }, []);

  useEffect(() => {
    refresh();
    const id = setInterval(refresh, POLL_INTERVAL_MS);
    return () => clearInterval(id);
  }, [refresh]);

  const value = useMemo(() => ({ count, refresh, setCount }), [count, refresh]);
  return <UnreadContext.Provider value={value}>{children}</UnreadContext.Provider>;
}

export const useUnread = () => useContext(UnreadContext);
